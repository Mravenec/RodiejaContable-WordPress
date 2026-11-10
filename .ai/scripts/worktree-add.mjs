#!/usr/bin/env node
/**
 * Isolated git worktree for one board issue (Windows-first).
 *
 *   Branch : ticket/{n}-{slug}/{ISSUE_ID}
 *   Folder : <primary>/.worktrees/{n}-{slug}/{ISSUE_ID}
 *   Epic   : feature/{n}-{slug}  (created at integrate — not here; git cannot nest
 *            ticket refs under the epic branch name)
 *
 * Shares .linear/epics and .linear/agents with the primary checkout
 * (directory junction on Windows, symlink elsewhere).
 *
 *   node .ai/scripts/worktree-add.mjs APP-1-2
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { spawnSync } from "child_process";
import {
  ISSUE_ID_RE,
  resolveEpicKey,
  epicBranchName,
  ticketBranchName,
  ticketWorktreeDir,
} from "./lib/worktree-paths.mjs";
import {
  pickStartPoint,
  assertParkedDepsCommitted,
} from "./lib/worktree-git.mjs";
import { shareDir } from "./lib/board-share.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

function run(cmd, args, cwd) {
  return spawnSync(cmd, args, { cwd, encoding: "utf8" });
}

function git(args, cwd) {
  const r = run("git", args, cwd);
  if (r.status !== 0) {
    process.stderr.write(r.stderr || r.stdout || "git failed\n");
    process.exit(r.status ?? 1);
  }
  return (r.stdout || "").trim();
}

function gitOk(args, cwd) {
  return run("git", args, cwd).status === 0;
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

function isWorktreeCheckout(dir) {
  const gitfile = path.join(dir, ".git");
  if (!fs.existsSync(gitfile)) return false;
  try {
    const st = fs.lstatSync(gitfile);
    if (st.isFile()) {
      return fs.readFileSync(gitfile, "utf8").includes("gitdir:");
    }
  } catch {
    return false;
  }
  return false;
}

const id = process.argv[2];
if (!id || !ISSUE_ID_RE.test(id)) {
  console.error(
    "Usage: node .ai/scripts/worktree-add.mjs <ISSUE_ID>\n" +
      "  ISSUE_ID looks like APP-1-2 (team_key-epic-seq)."
  );
  process.exit(1);
}

const startCwd = fs.existsSync(path.join(SCRIPT_ROOT, ".git"))
  ? SCRIPT_ROOT
  : process.cwd();

if (!gitOk(["rev-parse", "--is-inside-work-tree"], startCwd)) {
  console.error("Run this from a git checkout of the host harness.");
  process.exit(1);
}

const primary = primaryCheckout(startCwd);
const linear = path.join(primary, ".linear");
if (!fs.existsSync(path.join(linear, "config.yaml"))) {
  console.error(`No .linear/config.yaml under primary checkout:\n  ${primary}`);
  process.exit(1);
}

let epicKey;
try {
  epicKey = resolveEpicKey(linear, id);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const epicBranch = epicBranchName(epicKey);
const ticketBranch = ticketBranchName(epicKey, id);
const dir = ticketWorktreeDir(primary, epicKey, id);
const parent = path.dirname(dir);
const legacyDir = path.join(primary, ".worktrees", id);

if (fs.existsSync(legacyDir)) {
  console.error(
    `Legacy worktree still exists:\n  ${legacyDir}\n` +
      `Remove it first: node .ai/scripts/worktree-rm.mjs ${id}`
  );
  process.exit(1);
}

if (isWorktreeCheckout(parent)) {
  console.error(
    `Cannot nest a ticket worktree inside another worktree:\n  ${parent}\n` +
      `Epic checkout must be .worktrees/${epicKey}/_epic (plain parent folder).`
  );
  process.exit(1);
}

if (fs.existsSync(dir)) {
  console.error(`Worktree folder already exists:\n  ${dir}`);
  process.exit(1);
}

assertParkedDepsCommitted(primary, linear, epicKey, id);
const startPoint = pickStartPoint(primary, linear, epicKey, id);

fs.mkdirSync(parent, { recursive: true });

if (gitOk(["show-ref", "--verify", "--quiet", `refs/heads/${ticketBranch}`], primary)) {
  git(["worktree", "add", dir, ticketBranch], primary);
} else {
  git(["worktree", "add", "-b", ticketBranch, dir, startPoint], primary);
}

try {
  shareDir(path.join(dir, ".linear", "epics"), path.join(linear, "epics"));
  shareDir(path.join(dir, ".linear", "agents"), path.join(linear, "agents"));
} catch (err) {
  process.stderr.write(String(err.message || err) + "\n");
  process.exit(1);
}

console.log(`Worktree ready
  epic key : ${epicKey}
  folder   : ${dir}
  branch   : ${ticketBranch}
  based on : ${startPoint}  (newest committed parked same-epic ticket, or done blocked_by including other epics)
  epic     : ${epicBranch}  (created at integrate — do not create this ref while tickets are open)
  board    : this folder MUST match board ticket ${id} (one claimed ticket = one worktree)
  ui       : http://127.0.0.1:47382  (one board — do not fork .linear)

Implement inside that folder. Mark the same board:
  node .linear/scripts/board.mjs checklist ${id} <n> --agent <name>
`);
