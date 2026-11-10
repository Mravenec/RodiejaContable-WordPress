#!/usr/bin/env node
/**
 * After every ticket in the epic is in done (or later), apply commits + merges
 * on the host repo. Human never merges. After summarize + handoff, agents
 * run worktree-push (named epic branch, then drop _epic).
 *
 *   node scripts/worktree-integrate.mjs 1 --repo C:\path\to\host
 *   node scripts/worktree-integrate.mjs 1-my-feature --repo C:\path\to\host --print-only
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
import {
  resolveEpicKeyFromRef,
  epicBranchName,
  epicWorktreeDir,
  findTicketWorktreeDir,
  PARKED_COLUMNS,
  resolveExistingTicketBranch,
} from "./lib/worktree-paths.mjs";
import {
  gitOk,
  git,
  isDirty,
  isMerging,
  currentBranch,
  commitTicketDir,
  mergeTicketIntoEpic,
  conflictedFiles,
  pruneTicketWorktreesAfterIntegrate,
} from "./lib/worktree-git.mjs";
import { shareDir } from "./lib/board-share.mjs";

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

function positional(argv) {
  const out = [];
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === "--repo") {
      i += 1;
      continue;
    }
    if (String(argv[i]).startsWith("--")) continue;
    out.push(argv[i]);
  }
  return out;
}

function run(cmd, args, cwd) {
  return spawnSync(cmd, args, { cwd, encoding: "utf8" });
}

function primaryCheckout(cwd) {
  const out = git(["worktree", "list", "--porcelain"], cwd);
  const line = out.split(/\r?\n/).find((l) => l.startsWith("worktree "));
  if (!line) {
    console.error("Could not read git worktree list.");
    process.exit(1);
  }
  return line.slice("worktree ".length).trim();
}

function looksLikeEngine(dir) {
  return (
    fs.existsSync(path.join(dir, "config.yaml")) &&
    fs.existsSync(path.join(dir, "scripts", "board.mjs")) &&
    !fs.existsSync(path.join(dir, ".linear", "config.yaml"))
  );
}

function loadEpicShow(linearRoot, ref) {
  const r = run(
    "node",
    [path.join(linearRoot, "scripts", "board.mjs"), "epic", "show", String(ref)],
    linearRoot
  );
  if (r.status !== 0) {
    process.stderr.write(r.stderr || r.stdout || "board epic show failed\n");
    process.exit(r.status ?? 1);
  }
  return JSON.parse(r.stdout || "{}");
}

function rel(primary, abs) {
  return path.relative(primary, abs) || abs;
}

const raw = process.argv.slice(2);
const repoFlag = flag(raw, "--repo");
const printOnly = raw.includes("--print-only");
const [ref] = positional(raw);

if (!ref) {
  console.error(
    "Usage: node scripts/worktree-integrate.mjs <n|slug|{n}-{slug}> --repo <host-git-root> [--print-only]"
  );
  process.exit(1);
}

const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();

if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass the host git repo:\n" +
      "  node scripts/worktree-integrate.mjs " +
      ref +
      " --repo <host-git-root>"
  );
  process.exit(1);
}

if (!gitOk(["rev-parse", "--is-inside-work-tree"], cwd)) {
  console.error("Not a git checkout. Pass --repo <host-git-root>.");
  process.exit(1);
}

const primary = primaryCheckout(cwd);
const linear = path.join(primary, ".linear");
if (!fs.existsSync(path.join(linear, "config.yaml"))) {
  console.error(`No .linear/config.yaml under primary checkout:\n  ${primary}`);
  process.exit(1);
}

let epicKey;
try {
  epicKey = resolveEpicKeyFromRef(linear, ref);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const show = loadEpicShow(linear, epicKey.split("-")[0]);
const issues = (show.issues || []).sort((a, b) =>
  String(a.id).localeCompare(String(b.id), undefined, { numeric: true })
);

if (!issues.length) {
  console.error(`Epic ${epicKey} has no tickets.`);
  process.exit(1);
}

const open = issues.filter((i) => !PARKED_COLUMNS.has(i.column));
if (open.length) {
  console.error(
    `Cannot integrate ${epicKey} until every ticket is in done (or summarize).\n` +
      open.map((i) => `  ${i.id}  ${i.column}  ${i.title}`).join("\n")
  );
  process.exit(1);
}

function collectTickets(autoCommit) {
  const dirtyTickets = [];
  const missingFolders = [];
  const missingCommits = [];
  const ticketInfos = [];
  const commits = [];

  for (const issue of issues) {
    const found = findTicketWorktreeDir(primary, linear, issue.id);
    const folderExists = Boolean(found?.dir && fs.existsSync(found.dir));
    if (!folderExists) missingFolders.push(issue.id);

    let branch = null;
    if (folderExists) {
      branch = currentBranch(found.dir) || found.branch;
        if (autoCommit) {
          const result = commitTicketDir(found.dir, `${issue.id}: ticket work`, {
            primary,
          });
          if (result.committed) commits.push({ id: issue.id, ...result });
          branch = result.branch || branch;
        } else if (isDirty(found.dir)) {
          dirtyTickets.push({
            id: issue.id,
            dir: found.dir,
            rel: rel(primary, found.dir),
            branch,
          });
        }
    }
    if (!branch) {
      branch = resolveExistingTicketBranch(gitOk, primary, epicKey, issue.id);
    }
    if (!branch) {
      missingCommits.push(issue.id);
      continue;
    }
    ticketInfos.push({ id: issue.id, branch, folderExists });
  }
  return { dirtyTickets, missingFolders, missingCommits, ticketInfos, commits };
}

const apply = !printOnly;
const collected = collectTickets(apply);
const epicBranch = epicBranchName(epicKey);
const epicBranchExists = gitOk(
  ["show-ref", "--verify", "--quiet", `refs/heads/${epicBranch}`],
  primary
);

if (collected.missingCommits.length && !epicBranchExists) {
  console.error(
    `Missing committed ticket branches (run worktree-finish first):\n` +
      collected.missingCommits.map((id) => `  ${id}`).join("\n")
  );
  process.exit(1);
}
let epicCreatedFrom = null;
if (!gitOk(["show-ref", "--verify", "--quiet", `refs/heads/${epicBranch}`], primary)) {
  const starter = collected.ticketInfos[0];
  if (!starter) {
    console.error(
      `Cannot create ${epicBranch}: no ticket branches found. Every board ticket needs a worktree at .worktrees/${epicKey}/<ISSUE_ID>.`
    );
    process.exit(1);
  }
  git(["branch", epicBranch, starter.branch], primary);
  epicCreatedFrom = starter.branch;
}

const epicDir = epicWorktreeDir(primary, epicKey);
if (!fs.existsSync(epicDir)) {
  fs.mkdirSync(path.dirname(epicDir), { recursive: true });
  git(["worktree", "add", epicDir, epicBranch], primary);
  try {
    shareDir(path.join(epicDir, ".linear", "epics"), path.join(linear, "epics"));
    shareDir(path.join(epicDir, ".linear", "agents"), path.join(linear, "agents"));
  } catch (err) {
    process.stderr.write(String(err.message || err) + "\n");
    process.exit(1);
  }
}

const epicRel = rel(primary, epicDir);
const toMerge = collected.ticketInfos.filter((t) => {
  if (t.branch === epicBranch) return false;
  if (epicCreatedFrom && t.branch === epicCreatedFrom) return false;
  return true;
});

if (printOnly) {
  console.log(`Epic ${epicKey} (print-only — agents should omit --print-only).

  epic key    : ${epicKey}
  epic branch : ${epicBranch}
  epic folder : ${epicRel}
`);
  for (const t of collected.dirtyTickets) {
    console.log(
      `   node scripts/worktree-commit.mjs ${t.id} --repo ${primary}`
    );
  }
  for (const t of toMerge) {
    console.log(`   cd ${epicRel} && git merge --no-ff ${t.branch}`);
  }
  process.exit(0);
}

if (isMerging(epicDir)) {
  const files = conflictedFiles(epicDir);
  if (files.length) {
    console.error(
      `Merge still conflicted in ${epicRel}. Keep both tickets' intent.\n` +
        files.map((f) => `  ${f}`).join("\n") +
        `\nThen from ${epicRel}: git add -A && git commit\n` +
        `Then re-run: node scripts/worktree-integrate.mjs ${ref} --repo ${primary}`
    );
    process.exit(1);
  }
  const finish = run("git", ["commit", "--no-edit"], epicDir);
  if (finish.status !== 0) {
    console.error(
      `Finish the in-progress merge in ${epicRel}, then re-run integrate.\n` +
        (finish.stderr || finish.stdout || "")
    );
    process.exit(1);
  }
}

const merged = [];
const skipped = [];
for (const t of toMerge) {
  const result = mergeTicketIntoEpic(epicDir, epicBranch, t.branch);
  if (!result.ok) {
    const files = conflictedFiles(epicDir);
    console.error(
      `CONFLICT merging ${t.branch} into ${epicBranch}.\n` +
        `Resolve in ${epicRel} so no ticket's work is lost (keep both intents).\n` +
        (files.length
          ? `Conflicted files:\n${files.map((f) => `  ${f}`).join("\n")}\n`
          : "") +
        `\nFrom ${epicRel}: edit files, then git add -A && git commit\n` +
        `Then re-run: node scripts/worktree-integrate.mjs ${ref} --repo ${primary}`
    );
    process.exit(1);
  }
  if (result.skipped) skipped.push(t.branch);
  else merged.push(t.branch);
}

const persist = commitTicketDir(
  epicDir,
  `${epicKey}: persist board snapshots and journeys`,
  { primary }
);

const pruned = pruneTicketWorktreesAfterIntegrate(
  primary,
  linear,
  epicKey,
  issues.map((i) => i.id)
);

const pruneOk = pruned.ok !== false && !(pruned.leftovers || []).length;
console.log(
  JSON.stringify(
    {
      integrated: pruneOk,
      epicKey,
      epicBranch,
      epicFolder: epicDir,
      autoCommits: collected.commits,
      persistDocs: persist,
      merged,
      alreadyInEpic: skipped,
      missingFolders: collected.missingFolders,
      prunedTickets: pruned,
      next: pruneOk
        ? [
            "Smoke the combined tree in _epic (schema, API, UI, CORS / Vite proxy).",
            "Ticket folders are gone. Only _epic remains until worktree-push.",
            "Move every ticket to summarize.",
            "Write living HTML as a study guide; board.mjs handoff with the push line + Supervisor / Manager smoke note.",
            "Then run: node scripts/worktree-push.mjs " +
              ref +
              " --repo " +
              primary +
              " (publishes feature/{n}-{slug}, then drops _epic).",
          ]
        : [
            "Ticket branches were merged, but leftover APP-* folders remain (Windows file lock from java/vite).",
            "Scripts already tried to stop those ticket servers. Re-run: node scripts/worktree-integrate.mjs " +
              ref +
              " --repo " +
              primary,
            "Do not treat this as a clean integrate until leftovers is empty.",
          ],
      recordedPush: `cd ${epicRel} && git push -u origin ${epicBranch}`,
    },
    null,
    2
  )
);
if (!pruneOk) {
  console.error(
    "Integrate merge finished, but ticket folders are still on disk. Re-run worktree-integrate after those java/vite processes exit."
  );
  process.exit(1);
}
