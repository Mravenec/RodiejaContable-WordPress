/**
 * Land feature/{n}-{slug} onto main — shared helpers.
 * Spec: .ai/specs/epic-land-main.md
 */
import fs from "fs";
import path from "path";
import {
  listEpicKeys,
  epicBranchName,
  resolveEpicKeyFromRef,
} from "./worktree-paths.mjs";
import {
  run,
  git,
  gitOk,
  currentBranch,
  remoteHeadsHas,
  isPersistDocRel,
} from "./worktree-git.mjs";

// re-export classify if missing — define local
/**
 * Split `git status` on the primary into persist docs and everything else.
 * `allUntracked` lists untracked files one by one instead of collapsing a
 * wholly untracked folder into `dir/` (needed before deleting anything).
 */
export function classifyPrimaryDirt(primary, { allUntracked = false } = {}) {
  const args = ["status", "--porcelain"];
  if (allUntracked) args.push("--untracked-files=all");
  const r = run("git", args, primary);
  const persist = [];
  const other = [];
  for (const line of String(r.stdout || "").split(/\r?\n/)) {
    if (!line.trim()) continue;
    const rel = line.slice(3).trim().split(" -> ").pop().replace(/\\/g, "/");
    if (isPersistDocRel(rel)) persist.push({ line, rel });
    else other.push({ line, rel });
  }
  return { persist, other };
}

export const LAND_STATUSES = [
  "on-origin",
  "landing",
  "smoke-main",
  "landed",
];

export function landFilePath(epicPath) {
  return path.join(epicPath, "summaries", "land.json");
}

export function emptyLand() {
  return {
    status: "on-origin",
    started_at: null,
    finished_at: null,
    merge_commit: null,
    conflicts: [],
    smoke_ok: null,
    smoke_at: null,
    git: "git push origin main",
    note: null,
    recorded_at: null,
  };
}

export function readLand(epicPath) {
  const p = landFilePath(epicPath);
  if (!fs.existsSync(p)) return emptyLand();
  try {
    const doc = JSON.parse(fs.readFileSync(p, "utf8"));
    if (!doc || typeof doc !== "object") return emptyLand();
    return { ...emptyLand(), ...doc };
  } catch {
    return emptyLand();
  }
}

export function writeLand(epicPath, doc) {
  const p = landFilePath(epicPath);
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, JSON.stringify(doc, null, 2) + "\n", "utf8");
}

export function epicPathForKey(linearRoot, epicKey) {
  return path.join(linearRoot, "epics", epicKey);
}

export function isLandedOnMain(primary, epicKey) {
  const land = readLand(epicPathForKey(path.join(primary, ".linear"), epicKey));
  if (land.status === "landed" && land.merge_commit) return true;
  // Fallback: feature tip is ancestor of origin/main
  const br = epicBranchName(epicKey);
  if (!remoteHeadsHas(primary, br)) return false;
  if (!remoteHeadsHas(primary, "main")) return false;
  return gitOk(
    ["merge-base", "--is-ancestor", `origin/${br}`, "origin/main"],
    primary
  );
}

export function landStatusForEpic(primary, linearRoot, epicKey) {
  const epicPath = epicPathForKey(linearRoot, epicKey);
  const land = readLand(epicPath);
  const onOrigin = remoteHeadsHas(primary, epicBranchName(epicKey));
  if (land.status === "landed") return { ...land, onOrigin, epicKey };
  if (land.status === "landing" || land.status === "smoke-main") {
    return { ...land, onOrigin, epicKey };
  }
  // Detect already merged without land.json
  if (onOrigin && isLandedOnMain(primary, epicKey) && land.status === "on-origin") {
    return { ...land, status: "landed", onOrigin, epicKey, inferred: true };
  }
  return {
    ...land,
    status: onOrigin ? "on-origin" : "building",
    onOrigin,
    epicKey,
  };
}

/**
 * Persist land.json when git already shows landed but the file still says
 * on-origin / is missing fields — keeps board UI and land-ready in lockstep.
 */
export function backfillInferredLands(primary, linearRoot) {
  const written = [];
  for (const row of listLandQueue(primary, linearRoot)) {
    if (!row.inferred || row.status !== "landed") continue;
    const epicPath = epicPathForKey(linearRoot, row.epicKey);
    const land = readLand(epicPath);
    if (land.status === "landed" && land.merge_commit) continue;
    const br = epicBranchName(row.epicKey);
    const tip = gitOk(["rev-parse", "--verify", `origin/${br}`], primary)
      ? run("git", ["rev-parse", `origin/${br}`], primary)
      : run("git", ["rev-parse", "origin/main"], primary);
    const sha = String(tip.stdout || "").trim() || null;
    const next = {
      ...land,
      status: "landed",
      merge_commit: land.merge_commit || sha,
      smoke_ok: land.smoke_ok ?? true,
      smoke_at: land.smoke_at || new Date().toISOString(),
      finished_at: land.finished_at || new Date().toISOString(),
      recorded_at: new Date().toISOString(),
      git: land.git || "git push origin main",
      note:
        land.note ||
        `(backfilled) ${row.epicKey} was already on origin/main; land.json written so the board shows Landed.`,
    };
    writeLand(epicPath, next);
    written.push(row.epicKey);
  }
  return written;
}

export function listLandQueue(primary, linearRoot) {
  return listEpicKeys(linearRoot).map((epicKey) =>
    landStatusForEpic(primary, linearRoot, epicKey)
  );
}

export function nextEpicToLand(primary, linearRoot) {
  const queue = listLandQueue(primary, linearRoot);
  for (const row of queue) {
    if (row.status === "landed") continue;
    if (!row.onOrigin) {
      return {
        ok: false,
        error: `${row.epicKey} is not on origin yet. Finish worktree-push first.`,
        queue,
      };
    }
    return { ok: true, epicKey: row.epicKey, status: row.status, queue };
  }
  return { ok: true, epicKey: null, done: true, queue };
}

export function assertPriorEpicsLanded(primary, linearRoot, epicKey) {
  const keys = listEpicKeys(linearRoot);
  const idx = keys.indexOf(epicKey);
  if (idx < 0) throw new Error(`Unknown epic ${epicKey}`);
  for (let i = 0; i < idx; i++) {
    const prior = landStatusForEpic(primary, linearRoot, keys[i]);
    if (prior.status !== "landed") {
      throw new Error(
        `Cannot land ${epicKey}: prior epic ${keys[i]} is ${prior.status} (need landed).`
      );
    }
  }
}

/** Porcelain / diff path with forward slashes and no trailing slash. */
function normRel(rel) {
  return String(rel).trim().replace(/\\/g, "/").replace(/\/+$/, "");
}

/** True when `rel` (file or directory) and merge path `m` overlap. */
function pathsOverlap(rel, m) {
  const a = normRel(rel);
  const b = normRel(m);
  return a === b || b.startsWith(a + "/") || a.startsWith(b + "/");
}

/**
 * Remove untracked persist docs (`.docs/board`, `.docs/journey`) that the
 * merge would overwrite. Feature branches carry their own snapshots, so the
 * copy on `main` is regenerated. Every other untracked path is left on disk;
 * `assertMainCleanEnough` refuses the land when one collides.
 */
export function clearUntrackedPersistForMerge(primary, epicKey) {
  const epicBranch = epicBranchName(epicKey);
  run("git", ["fetch", "origin", epicBranch], primary);
  const mergePaths = String(
    run("git", ["diff", "--name-only", `HEAD...origin/${epicBranch}`], primary)
      .stdout || ""
  )
    .split(/\r?\n/)
    .map(normRel)
    .filter(Boolean);

  const { persist } = classifyPrimaryDirt(primary, { allUntracked: true });
  const removed = [];
  for (const item of persist) {
    if (!item.line.startsWith("??")) continue;
    const rel = normRel(item.rel);
    if (!mergePaths.some((m) => pathsOverlap(rel, m))) continue;
    const abs = path.join(primary, rel);
    if (!fs.existsSync(abs) || fs.lstatSync(abs).isDirectory()) continue;
    fs.unlinkSync(abs);
    removed.push(rel);
  }
  return removed;
}

/** @deprecated use clearUntrackedPersistForMerge */
export function clearUntrackedPersistForEpic(primary, epicKey) {
  return clearUntrackedPersistForMerge(primary, epicKey);
}

/**
 * Gitlinks (mode 160000) the feature branch adds that its `.gitmodules`
 * does not declare — an untracked nested clone committed by accident.
 */
export function featureAddedGitlinks(primary, epicKey) {
  const ref = `origin/${epicBranchName(epicKey)}`;
  const raw = run("git", ["diff", "--raw", "--no-renames", `HEAD...${ref}`], primary);
  const modules = run("git", ["show", `${ref}:.gitmodules`], primary);
  const declared = new Set();
  if (modules.status === 0) {
    for (const line of String(modules.stdout || "").split(/\r?\n/)) {
      const m = /^\s*path\s*=\s*(.+)$/.exec(line);
      if (m) declared.add(normRel(m[1]));
    }
  }
  const found = [];
  for (const line of String(raw.stdout || "").split(/\r?\n/)) {
    const m = /^:(\d{6}) (\d{6}) \S+ \S+ \S+\t(.+)$/.exec(line);
    if (!m || m[2] !== "160000" || m[1] === "160000") continue;
    const rel = normRel(m[3]);
    if (!declared.has(rel)) found.push(rel);
  }
  return found;
}

export function assertMainCleanEnough(primary, epicKey) {
  const dirt = classifyPrimaryDirt(primary, { allUntracked: true });
  const epicBranch = epicBranchName(epicKey);
  run("git", ["fetch", "origin", epicBranch], primary);
  const mergePaths = String(
    run("git", ["diff", "--name-only", `HEAD...origin/${epicBranch}`], primary)
      .stdout || ""
  )
    .split(/\r?\n/)
    .map(normRel)
    .filter(Boolean);

  const gitlinks = featureAddedGitlinks(primary, epicKey);
  if (gitlinks.length) {
    return {
      ok: false,
      error:
        `origin/${epicBranch} adds nested git repositories (gitlinks) that no .gitmodules declares:\n` +
        gitlinks.map((g) => `  ${g}`).join("\n") +
        `\nRemove them on the feature branch (git rm --cached <path>, add the folder to .gitignore), push it again, then re-run land-main.`,
      dirt,
      gitlinks,
      mergePaths,
    };
  }

  const overlaps = [];
  for (const item of [...dirt.other, ...dirt.persist]) {
    // Untracked persist docs are cleared by clearUntrackedPersistForMerge.
    if (item.line.startsWith("??") && isPersistDocRel(item.rel)) continue;
    if (mergePaths.some((m) => pathsOverlap(item.rel, m))) {
      overlaps.push(item.line);
    }
  }
  if (overlaps.length) {
    return {
      ok: false,
      error:
        "Dirty or untracked paths overlap files coming from the feature merge. Nothing was deleted; park or move them first.\n" +
        overlaps.map((x) => `  ${x}`).join("\n"),
      dirt,
      mergePaths,
    };
  }
  return { ok: true, dirt, mergePaths };
}

export function mergeFeatureIntoMain(primary, epicKey) {
  const epicBranch = epicBranchName(epicKey);
  const branch = currentBranch(primary);
  if (branch !== "main") {
    return { ok: false, error: `Primary must be on main (now: ${branch || "detached"})` };
  }
  if (!remoteHeadsHas(primary, epicBranch)) {
    return { ok: false, error: `origin/${epicBranch} missing. Push the epic first.` };
  }
  run("git", ["fetch", "origin", epicBranch, "main"], primary);
  // Already ancestor?
  if (
    gitOk(
      ["merge-base", "--is-ancestor", `origin/${epicBranch}`, "HEAD"],
      primary
    )
  ) {
    const sha = git(["rev-parse", "HEAD"], primary);
    return {
      ok: true,
      already: true,
      merge_commit: sha,
      conflicts: [],
      epicBranch,
    };
  }
  const m = run(
    "git",
    [
      "merge",
      "--no-ff",
      "-m",
      `Land ${epicKey}: merge ${epicBranch} into main`,
      `origin/${epicBranch}`,
    ],
    primary
  );
  if (m.status !== 0) {
    const conflicts = conflictedPaths(primary);
    return {
      ok: false,
      merging: true,
      conflicts,
      error:
        (m.stderr || m.stdout || "merge failed").trim() +
        (conflicts.length
          ? `\nConflicts:\n${conflicts.map((c) => `  ${c}`).join("\n")}`
          : ""),
      epicBranch,
    };
  }
  const sha = git(["rev-parse", "HEAD"], primary);
  return { ok: true, merge_commit: sha, conflicts: [], epicBranch };
}

function conflictedPaths(dir) {
  const r = run("git", ["diff", "--name-only", "--diff-filter=U"], dir);
  return String(r.stdout || "")
    .split(/\r?\n/)
    .map((s) => s.trim())
    .filter(Boolean);
}

export function pushMain(primary) {
  const branch = currentBranch(primary);
  if (branch !== "main") {
    return { ok: false, error: `Not on main (${branch})` };
  }
  const r = run("git", ["push", "origin", "main"], primary);
  if (r.status !== 0) {
    return {
      ok: false,
      error: (r.stderr || r.stdout || "git push origin main failed").trim(),
    };
  }
  return { ok: true, stdout: (r.stdout || "").trim() };
}

export function updateJourneyLanded(primary, epicKey, { mergeCommit, note } = {}) {
  const p = path.join(primary, ".docs", "journey", `${epicKey}.md`);
  if (!fs.existsSync(p)) {
    return { ok: false, reason: `No journey at ${p}` };
  }
  let text = fs.readFileSync(p, "utf8");
  if (/^\*\*Status:\*\*/m.test(text)) {
    text = text.replace(
      /^\*\*Status:\*\*[^\n]*/m,
      `**Status:** Landed on main · Board: \`.linear/epics/${epicKey}/\``
    );
  } else {
    text = text.replace(
      /^(# [^\n]+\n\n)/,
      `$1**Status:** Landed on main · Board: \`.linear/epics/${epicKey}/\``
    );
  }
  const landBlock = `

## Land on main

- Merge commit: \`${mergeCommit || "(unknown)"}\`
- Branch: \`feature/${epicKey}\` → \`main\`
${note ? `\n### Supervisor / Manager (land)\n\n${note.trim()}\n` : ""}
`;
  if (/## Land on main/.test(text)) {
    text = text.replace(/## Land on main[\s\S]*?(?=\n## |\n# |$)/, landBlock.trim() + "\n\n");
  } else {
    text = text.trimEnd() + "\n" + landBlock;
  }
  fs.writeFileSync(p, text, "utf8");
  return { ok: true, path: p };
}

export function resolveEpicKey(linearRoot, ref) {
  return resolveEpicKeyFromRef(linearRoot, ref);
}
