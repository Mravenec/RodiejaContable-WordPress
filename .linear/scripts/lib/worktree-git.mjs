/**
 * Shared git helpers for ticket commit, start-point (incl. cross-epic
 * blocked_by), dirty-tree gates, and epic merge-apply.
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
import {
  parseIssueId,
  resolveEpicKey,
  resolveExistingTicketBranch,
  findTicketWorktreeDir,
  ticketBranchAliases,
  epicWorktreeDir,
  epicBranchName,
  PARKED_COLUMNS,
} from "./worktree-paths.mjs";
import {
  unlinkSharedBoardLinks,
  unlinkLinkOnly,
  BOARD_SHARE_NAMES,
} from "./board-share.mjs";

export function run(cmd, args, cwd) {
  return spawnSync(cmd, args, { cwd, encoding: "utf8" });
}

export function git(args, cwd) {
  const r = run("git", args, cwd);
  if (r.status !== 0) {
    process.stderr.write(r.stderr || r.stdout || "git failed\n");
    process.exit(r.status ?? 1);
  }
  return (r.stdout || "").trim();
}

export function gitOk(args, cwd) {
  return run("git", args, cwd).status === 0;
}

export function isDirty(dir) {
  const r = run("git", ["status", "--porcelain"], dir);
  return Boolean((r.stdout || "").trim());
}

export function isMerging(dir) {
  return gitOk(["rev-parse", "-q", "--verify", "MERGE_HEAD"], dir);
}

export function currentBranch(dir) {
  const r = run("git", ["rev-parse", "--abbrev-ref", "HEAD"], dir);
  if (r.status !== 0) return null;
  return (r.stdout || "").trim();
}

export function loadEpicShow(linearRoot, ref) {
  const r = run(
    "node",
    [path.join(linearRoot, "scripts", "board.mjs"), "epic", "show", String(ref)],
    linearRoot
  );
  if (r.status !== 0) return null;
  try {
    return JSON.parse(r.stdout || "{}");
  } catch {
    return null;
  }
}

export function gitCommitTime(primary, ref) {
  const r = run("git", ["log", "-1", "--format=%ct", ref], primary);
  if (r.status !== 0) return 0;
  return Number((r.stdout || "").trim()) || 0;
}

/**
 * Newest committed start point: parked tickets in this epic, plus this
 * ticket's done blocked_by (other epics included).
 */
export function pickStartPoint(primary, linearRoot, epicKey, ticketId) {
  const parsed = parseIssueId(ticketId);
  if (!parsed) return "HEAD";
  const candidates = [];

  const same = loadEpicShow(linearRoot, parsed.epic);
  const self = (same?.issues || []).find((i) => i.id === ticketId);
  const parkedSame = (same?.issues || []).filter(
    (i) => i.id !== ticketId && PARKED_COLUMNS.has(i.column)
  );
  for (const i of parkedSame) {
    const br = resolveExistingTicketBranch(gitOk, primary, epicKey, i.id);
    if (br) candidates.push({ id: i.id, branch: br });
  }

  const blockedBy = self?.blocked_by || [];
  for (const depId of blockedBy) {
    let depKey;
    try {
      depKey = resolveEpicKey(linearRoot, depId);
    } catch {
      continue;
    }
    const depParsed = parseIssueId(depId);
    const depShow = depParsed
      ? loadEpicShow(linearRoot, depParsed.epic)
      : null;
    const dep = (depShow?.issues || []).find((i) => i.id === depId);
    if (!dep || !PARKED_COLUMNS.has(dep.column)) continue;
    const br = resolveExistingTicketBranch(gitOk, primary, depKey, depId);
    if (br) candidates.push({ id: depId, branch: br });
  }

  if (!candidates.length) return "HEAD";
  let best = candidates[0];
  let bestT = gitCommitTime(primary, best.branch);
  for (const c of candidates.slice(1)) {
    const t = gitCommitTime(primary, c.branch);
    if (t >= bestT) {
      best = c;
      bestT = t;
    }
  }
  return best.branch;
}

/**
 * If a parked dependency still has uncommitted files, the next worktree would
 * start without that work. Fail with the finish command (absolute folder).
 */
export function assertParkedDepsCommitted(primary, linearRoot, epicKey, ticketId) {
  const parsed = parseIssueId(ticketId);
  const ids = new Set();
  const same = loadEpicShow(linearRoot, parsed?.epic);
  for (const i of same?.issues || []) {
    if (i.id !== ticketId && PARKED_COLUMNS.has(i.column)) ids.add(i.id);
  }
  const self = (same?.issues || []).find((i) => i.id === ticketId);
  for (const depId of self?.blocked_by || []) ids.add(depId);

  const errors = [];
  for (const id of ids) {
    const found = findTicketWorktreeDir(primary, linearRoot, id);
    if (!found?.dir || !fs.existsSync(found.dir)) continue;
    if (!isDirty(found.dir)) continue;
    errors.push(
      `${id} is parked on the board but still has uncommitted files in:\n` +
        `  ${found.dir}\n` +
        `  Run: node .ai/scripts/worktree-commit.mjs ${id}`
    );
  }
  if (errors.length) {
    console.error(
      "Cannot create a worktree until parked dependencies are committed.\n" +
        errors.join("\n")
    );
    process.exit(1);
  }
}

function copyPersistTree(src, dest) {
  const st = fs.lstatSync(src);
  if (st.isSymbolicLink()) return;
  if (st.isDirectory()) {
    fs.mkdirSync(dest, { recursive: true });
    for (const name of fs.readdirSync(src)) {
      if (name === "node_modules" || name === ".git") continue;
      copyPersistTree(path.join(src, name), path.join(dest, name));
    }
    return;
  }
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
}

/**
 * Tracked recovery copies that must ride the ticket or epic branch.
 * Live .linear/epics folders stay gitignored and are never copied here.
 */
export function listPersistDocRelPaths(primary) {
  const rels = [];
  const boardRoot = path.join(primary, ".docs", "board");
  if (fs.existsSync(boardRoot)) {
    for (const name of fs.readdirSync(boardRoot)) {
      rels.push(path.join(".docs", "board", name));
    }
  }
  const journeyRoot = path.join(primary, ".docs", "journey");
  if (fs.existsSync(journeyRoot)) {
    for (const name of fs.readdirSync(journeyRoot)) {
      if (name.endsWith(".md")) {
        rels.push(path.join(".docs", "journey", name));
      }
    }
  }
  return rels;
}

export function isPersistDocRel(rel) {
  const n = String(rel).replace(/\\/g, "/");
  return n.startsWith(".docs/board/") || n === ".docs/board" ||
    n.startsWith(".docs/journey/");
}

/**
 * Board pulses write snapshots on the primary checkout. Ticket and _epic
 * worktrees have a separate working tree. Copy those files in before commit.
 */
export function syncPersistDocsIntoWorktree(primary, worktreeDir) {
  if (!primary || !worktreeDir) return { copied: 0 };
  const same =
    path.resolve(primary).replace(/\\/g, "/").toLowerCase() ===
    path.resolve(worktreeDir).replace(/\\/g, "/").toLowerCase();
  if (same) return { copied: 0, sameTree: true };
  let copied = 0;
  for (const rel of listPersistDocRelPaths(primary)) {
    const from = path.join(primary, rel);
    const to = path.join(worktreeDir, rel);
    if (!fs.existsSync(from)) continue;
    const st = fs.lstatSync(from);
    if (st.isDirectory()) {
      fs.rmSync(to, { recursive: true, force: true });
    }
    copyPersistTree(from, to);
    copied += 1;
  }
  return { copied };
}

function canonicalDir(p) {
  let abs = path.resolve(p);
  try {
    abs = fs.realpathSync.native(abs);
  } catch {
    /* keep resolved path */
  }
  return abs.replace(/\\/g, "/").replace(/\/+$/, "").toLowerCase();
}

/**
 * Why `dir` must not receive `git add -A` + commit, or null when it may.
 * A leftover plain folder under `.worktrees/` resolves to the parent
 * checkout, so git would stage and commit the primary tree on `main`.
 * @param {string} dir folder the caller wants to commit
 * @param {{ primary?: string }} opts primary checkout (never a commit target)
 * @returns {string|null} refusal reason, or null
 */
export function commitTargetProblem(dir, { primary } = {}) {
  if (!dir || !fs.existsSync(dir)) return `${dir} does not exist`;
  const top = run("git", ["rev-parse", "--show-toplevel"], dir);
  if (top.status !== 0) return `${dir} is not inside a git checkout`;
  const toplevel = (top.stdout || "").trim();
  if (canonicalDir(toplevel) !== canonicalDir(dir)) {
    return `${dir} is not its own git worktree (git resolves it to ${toplevel}). A leftover plain folder would commit that checkout instead.`;
  }
  if (primary && canonicalDir(primary) === canonicalDir(dir)) {
    return `${dir} is the primary checkout. Ticket and epic commits only happen in .worktrees/{n}-{slug}/…`;
  }
  const branch = currentBranch(dir);
  if (!branch || branch === "HEAD" || !/^(ticket|feature)\//.test(branch)) {
    return `${dir} is on ${!branch || branch === "HEAD" ? "a detached HEAD" : branch}. Commits need a ticket/ or feature/ branch.`;
  }
  return null;
}

/**
 * Staged gitlinks (mode 160000) that are new in this commit and not
 * declared in `.gitmodules` — an untracked nested clone, not a submodule.
 */
export function stagedNewGitlinks(dir) {
  const r = run("git", ["diff", "--cached", "--raw", "--no-renames"], dir);
  const declared = new Set(
    String(
      run("git", ["config", "-f", ".gitmodules", "--get-regexp", "path"], dir)
        .stdout || ""
    )
      .split(/\r?\n/)
      .map((l) => l.split(/\s+/).slice(1).join(" ").trim())
      .filter(Boolean)
  );
  const found = [];
  for (const line of String(r.stdout || "").split(/\r?\n/)) {
    const m = /^:(\d{6}) (\d{6}) \S+ \S+ \S+\t(.+)$/.exec(line);
    if (!m || m[2] !== "160000" || m[1] === "160000") continue;
    const rel = m[3].replace(/\\/g, "/");
    if (!declared.has(rel)) found.push(rel);
  }
  return found;
}

export function commitTicketDir(dir, message, { primary } = {}) {
  const problem = commitTargetProblem(dir, { primary });
  if (problem) {
    process.stderr.write(
      `Refusing to commit: ${problem}\n` +
        `Nothing was staged. Move any real work into the ticket worktree, remove the leftover folder, then re-run.\n`
    );
    process.exit(1);
  }
  if (primary) syncPersistDocsIntoWorktree(primary, dir);
  if (!isDirty(dir)) {
    return { committed: false, reason: "clean", branch: currentBranch(dir) };
  }
  const add = run("git", ["add", "-A"], dir);
  if (add.status !== 0) {
    process.stderr.write(add.stderr || add.stdout || "git add failed\n");
    process.exit(add.status ?? 1);
  }
  const gitlinks = stagedNewGitlinks(dir);
  if (gitlinks.length) {
    run("git", ["reset", "-q"], dir);
    process.stderr.write(
      `Refusing to commit nested git repositories (gitlinks) from ${dir}:\n` +
        gitlinks.map((g) => `  ${g}`).join("\n") +
        `\nThe index was reset; files stay on disk. Add the folder to .gitignore (or declare a real submodule in .gitmodules), then re-run.\n`
    );
    process.exit(1);
  }
  const commit = run("git", ["commit", "-m", message], dir);
  if (commit.status !== 0) {
    process.stderr.write(commit.stderr || commit.stdout || "git commit failed\n");
    process.exit(commit.status ?? 1);
  }
  const sha = git(["rev-parse", "--short", "HEAD"], dir);
  return {
    committed: true,
    sha,
    branch: currentBranch(dir),
    message,
  };
}

export function mergeTicketIntoEpic(epicDir, epicBranch, ticketBranch) {
  if (gitOk(["merge-base", "--is-ancestor", ticketBranch, "HEAD"], epicDir)) {
    return { ok: true, skipped: true, branch: ticketBranch };
  }
  const r = run(
    "git",
    [
      "merge",
      "--no-ff",
      ticketBranch,
      "-m",
      `Merge ${ticketBranch} into ${epicBranch}`,
    ],
    epicDir
  );
  if (r.status !== 0) {
    return {
      ok: false,
      branch: ticketBranch,
      stdout: r.stdout || "",
      stderr: r.stderr || "",
    };
  }
  return { ok: true, branch: ticketBranch };
}

export function conflictedFiles(epicDir) {
  const r = run("git", ["diff", "--name-only", "--diff-filter=U"], epicDir);
  if (r.status !== 0) return [];
  return (r.stdout || "")
    .split(/\r?\n/)
    .map((s) => s.trim())
    .filter(Boolean);
}

function sleepMs(ms) {
  Atomics.wait(new Int32Array(new SharedArrayBuffer(4)), 0, 0, ms);
}

/**
 * Windows: PIDs whose command line contains this folder (mvn / java / vite
 * with cwd in the ticket tree). Never includes this Node process.
 */
export function pidsHoldingPath(dir) {
  if (!dir || process.platform !== "win32") return [];
  const abs = path.resolve(dir);
  const escaped = abs.replace(/'/g, "''");
  const r = spawnSync(
    "powershell.exe",
    [
      "-NoProfile",
      "-Command",
      `$skipUi = { param($cl) if (-not $cl) { return $false }; return $cl.Contains('.linear\\ui') -or $cl.Contains('.linear/ui') -or $cl.Contains('scripts\\api.mjs') -or $cl.Contains('scripts/api.mjs') }; Get-CimInstance Win32_Process | Where-Object { -not (& $skipUi $_.CommandLine) -and (( $_.CommandLine -and $_.CommandLine.Contains('${escaped}') ) -or ( $_.ExecutablePath -and $_.ExecutablePath.Contains('${escaped}') )) } | ForEach-Object { $_.ProcessId }`,
    ],
    { encoding: "utf8" }
  );
  const skip = new Set([process.pid, process.ppid].filter(Boolean));
  return String(r.stdout || "")
    .split(/\r?\n/)
    .map((s) => Number(String(s).trim()))
    .filter((n) => Number.isInteger(n) && n > 0 && !skip.has(n));
}

/**
 * Stop ticket servers (Spring / Vite) that still have this folder open so
 * Windows can delete it. Does not touch Docker / MariaDB / the board UI.
 */
export function stopProcessesHoldingPath(dir) {
  const pids = pidsHoldingPath(dir);
  const killed = [];
  for (const pid of pids) {
    const r = spawnSync("taskkill", ["/PID", String(pid), "/T", "/F"], {
      encoding: "utf8",
    });
    if (r.status === 0) killed.push(pid);
  }
  if (killed.length) sleepMs(800);
  return { killed };
}

function boardShareStillPresent(dir) {
  return BOARD_SHARE_NAMES.some((n) =>
    fs.existsSync(path.join(dir, ".linear", n))
  );
}

function unlinkBoardSharesStrict(dir) {
  unlinkSharedBoardLinks(dir);
  for (const name of BOARD_SHARE_NAMES) {
    const p = path.join(dir, ".linear", name);
    if (!fs.existsSync(p)) continue;
    unlinkLinkOnly(p);
  }
}

function sameFsPath(a, b) {
  return path.resolve(a).replace(/\\/g, "/").toLowerCase() ===
    path.resolve(b).replace(/\\/g, "/").toLowerCase();
}

function isRegisteredGitWorktree(primary, dir) {
  const r = run("git", ["worktree", "list", "--porcelain"], primary);
  if (r.status !== 0) return false;
  for (const line of String(r.stdout || "").split(/\r?\n/)) {
    if (!line.startsWith("worktree ")) continue;
    if (sameFsPath(line.slice("worktree ".length).trim(), dir)) return true;
  }
  return false;
}

/**
 * Git may unregister the worktree while Windows still holds the folder
 * (`Permission denied` / `Invalid argument` while java/vite run).
 */
function removeOrphanFolder(dir) {
  unlinkBoardSharesStrict(dir);
  if (boardShareStillPresent(dir)) {
    return {
      ok: false,
      dir,
      stderr: `Refusing to recurse-delete ${dir} — .linear/epics or agents still present. Unlink the junction first.`,
    };
  }
  if (fs.existsSync(path.join(dir, ".git"))) {
    return {
      ok: false,
      dir,
      stderr: `${dir} still has .git — git worktree remove did not finish.`,
    };
  }
  let lastErr = "";
  for (let i = 0; i < 3; i++) {
    try {
      fs.rmSync(dir, { recursive: true, force: true });
    } catch (err) {
      lastErr = err.message || String(err);
    }
    if (!fs.existsSync(dir)) return { ok: true, dir };
    sleepMs(400);
  }
  return {
    ok: false,
    dir,
    stderr: lastErr || `Folder still exists after rm: ${dir}`,
  };
}

export function leftoverTicketFolders(primary, epicKey) {
  const parent = path.dirname(epicWorktreeDir(primary, epicKey));
  if (!fs.existsSync(parent)) return [];
  return fs
    .readdirSync(parent, { withFileTypes: true })
    .filter((d) => d.isDirectory() && d.name !== "_epic")
    .map((d) => path.join(parent, d.name));
}

/**
 * After merge/push: delete leftover APP-* folders (junctions unlinked first).
 * Stops java/vite that still have that path in their command line.
 */
export function sweepLeftoverTicketFolders(primary, epicKey) {
  const removed = [];
  const failed = [];
  for (const dir of leftoverTicketFolders(primary, epicKey)) {
    const rm = removeGitWorktree(primary, dir);
    if (rm.ok) removed.push(dir);
    else failed.push({ dir, stderr: rm.stderr });
  }
  return { removed, failed };
}

export function removeGitWorktree(primary, dir) {
  if (!dir || !fs.existsSync(dir)) {
    return { ok: true, skipped: true, dir };
  }
  unlinkBoardSharesStrict(dir);
  const registered = isRegisteredGitWorktree(primary, dir);
  if (registered) {
    let r = run("git", ["worktree", "remove", dir], primary);
    if (r.status !== 0) {
      r = run("git", ["worktree", "remove", "--force", dir], primary);
    }
    if (!fs.existsSync(dir)) {
      return { ok: true, dir };
    }
    stopProcessesHoldingPath(dir);
    unlinkBoardSharesStrict(dir);
    run("git", ["worktree", "prune"], primary);
    r = run("git", ["worktree", "remove", "--force", dir], primary);
    if (!fs.existsSync(dir)) {
      return { ok: true, dir, stoppedServers: true };
    }
  } else {
    stopProcessesHoldingPath(dir);
    unlinkBoardSharesStrict(dir);
  }
  const orphan = removeOrphanFolder(dir);
  if (orphan.ok) {
    return { ok: true, dir, orphaned: !registered };
  }
  const pids = pidsHoldingPath(dir);
  return {
    ok: false,
    dir,
    stderr:
      (orphan.stderr || "").trim() +
      (pids.length
        ? ` Stop processes still using this folder (PIDs ${pids.join(", ")}: mvn/java/vite), then re-run integrate or worktree-push.`
        : ` Stop any mvn/java/vite started from ${dir}, then re-run integrate or worktree-push.`),
  };
}

function localBranchExists(primary, branch) {
  return gitOk(["show-ref", "--verify", "--quiet", `refs/heads/${branch}`], primary);
}

/**
 * Delete a local branch when it is already contained in `ancestor`
 * (typically the epic branch). Uses -D only after that ancestor check
 * because `git branch -d` tests merge into HEAD (often main), not the epic.
 */
export function deleteLocalBranchIfAncestor(primary, branch, ancestor) {
  if (!localBranchExists(primary, branch)) {
    return { ok: true, skipped: true, branch };
  }
  if (!gitOk(["merge-base", "--is-ancestor", branch, ancestor], primary)) {
    return {
      ok: false,
      skipped: true,
      branch,
      reason: `not an ancestor of ${ancestor}`,
    };
  }
  const r = run("git", ["branch", "-D", branch], primary);
  if (r.status !== 0) {
    return {
      ok: false,
      branch,
      stderr: (r.stderr || r.stdout || "").trim(),
    };
  }
  return { ok: true, branch };
}

/**
 * After integrate: drop ticket folders and merged ticket branches.
 * Keep `_epic` + `feature/{n}-{slug}` until the human pushes.
 */
export function pruneTicketWorktreesAfterIntegrate(
  primary,
  linearRoot,
  epicKey,
  issueIds
) {
  const epicBranch = epicBranchName(epicKey);
  const removedFolders = [];
  const deletedBranches = [];
  const warnings = [];

  for (const id of issueIds) {
    const found = findTicketWorktreeDir(primary, linearRoot, id);
    if (found?.dir && fs.existsSync(found.dir)) {
      const rm = removeGitWorktree(primary, found.dir);
      if (rm.ok && !rm.skipped) removedFolders.push({ id, dir: found.dir });
      else if (!rm.ok) warnings.push(`${id}: ${rm.stderr}`);
    }
    for (const name of ticketBranchAliases(epicKey, id)) {
      const del = deleteLocalBranchIfAncestor(primary, name, epicBranch);
      if (del.ok && !del.skipped) deletedBranches.push(name);
      else if (!del.ok && del.reason) warnings.push(`${name}: ${del.reason}`);
      else if (!del.ok) warnings.push(`${name}: ${del.stderr || "delete failed"}`);
    }
  }
  run("git", ["worktree", "prune"], primary);
  const sweep = sweepLeftoverTicketFolders(primary, epicKey);
  for (const f of sweep.failed) {
    warnings.push(`${path.basename(f.dir)}: ${f.stderr}`);
  }
  const leftovers = leftoverTicketFolders(primary, epicKey);
  return {
    removedFolders: removedFolders.concat(
      sweep.removed.map((dir) => ({ id: path.basename(dir), dir }))
    ),
    deletedBranches,
    warnings,
    leftovers,
    ok: leftovers.length === 0,
  };
}

export function remoteHeadsHas(primary, branch) {
  const r = run("git", ["ls-remote", "--heads", "origin", branch], primary);
  if (r.status !== 0) return false;
  return Boolean((r.stdout || "").trim());
}

function pathIsInside(inner, outer) {
  const rel = path.relative(path.resolve(outer), path.resolve(inner));
  return rel === "" || (!rel.startsWith("..") && !path.isAbsolute(rel));
}

function rmdirIfEmpty(dir) {
  try {
    if (fs.existsSync(dir) && fs.readdirSync(dir).length === 0) {
      fs.rmdirSync(dir);
      return true;
    }
  } catch {
    /* ignore locked or non-empty */
  }
  return false;
}

/**
 * Publish feature/{n}-{slug} from `_epic`. Never --force. Never HEAD.
 * Ticket branches must not be passed here.
 */
export function pushEpicBranch(primary, epicKey) {
  const epicBranch = epicBranchName(epicKey);
  const dir = epicWorktreeDir(primary, epicKey);
  if (!fs.existsSync(dir)) {
    return { ok: false, error: `_epic is missing: ${dir}` };
  }
  const branch = currentBranch(dir);
  if (branch !== epicBranch) {
    return {
      ok: false,
      error: `_epic is on ${branch || "(detached)"}, expected ${epicBranch}`,
    };
  }
  const persist = commitTicketDir(
    dir,
    `${epicKey}: persist board snapshots and journeys`,
    { primary }
  );
  if (isDirty(dir) || isMerging(dir)) {
    return {
      ok: false,
      error: `_epic is dirty or merging. Finish integrate first.`,
      persist,
    };
  }
  const r = run("git", ["push", "-u", "origin", epicBranch], dir);
  if (r.status !== 0) {
    return {
      ok: false,
      error: (r.stderr || r.stdout || "git push failed").trim(),
    };
  }
  return { ok: true, epicBranch, persist, stdout: (r.stdout || "").trim() };
}

/**
 * After `git push -u origin feature/{n}-{slug}`: drop `_epic` and the local
 * epic branch. Commits stay on origin.
 */
export function pruneEpicWorktreeAfterPush(primary, epicKey) {
  const epicBranch = epicBranchName(epicKey);
  const dir = epicWorktreeDir(primary, epicKey);
  if (!remoteHeadsHas(primary, epicBranch)) {
    return {
      ok: false,
      error: `${epicBranch} is not on origin yet. Push from _epic first, then re-run from the primary checkout.`,
    };
  }
  if (pathIsInside(process.cwd(), dir)) {
    return {
      ok: false,
      error: `Leave ${dir} first (cd to the primary checkout), then re-run worktree-after-push.`,
    };
  }
  const warnings = [];
  let removedWorktree = false;
  if (fs.existsSync(dir)) {
    const rm = removeGitWorktree(primary, dir);
    if (rm.ok && !rm.skipped) removedWorktree = true;
    else if (!rm.ok) warnings.push(rm.stderr || `could not remove ${dir}`);
  }
  let deletedLocalBranch = false;
  if (localBranchExists(primary, epicBranch)) {
    const r = run("git", ["branch", "-D", epicBranch], primary);
    if (r.status === 0) deletedLocalBranch = true;
    else warnings.push((r.stderr || r.stdout || "").trim());
  }
  run("git", ["worktree", "prune"], primary);
  const sweep = sweepLeftoverTicketFolders(primary, epicKey);
  for (const f of sweep.failed) {
    warnings.push(f.stderr || `could not remove ${f.dir}`);
  }
  const leftovers = leftoverTicketFolders(primary, epicKey);
  if (leftovers.length) {
    warnings.push(
      `Leftover ticket folders after push (stop java/vite using them, then re-run worktree-after-push): ${leftovers.join(", ")}`
    );
  }
  const removedEpicFolder = rmdirIfEmpty(path.dirname(dir));
  return {
    ok: warnings.length === 0 && leftovers.length === 0,
    epicKey,
    epicBranch,
    removedWorktree,
    deletedLocalBranch,
    removedEpicFolder,
    sweptTickets: sweep.removed,
    leftovers,
    warnings,
  };
}
