#!/usr/bin/env node
/**
 * Host-repo worktree helper (Windows-first). Run against a harness git root,
 * not this engine folder.
 *
 *   Branch : ticket/{n}-{slug}/{ISSUE_ID}
 *   Folder : <host>/.worktrees/{n}-{slug}/{ISSUE_ID}
 *   Epic   : feature/{n}-{slug} created at integrate (not here)
 *
 *   node scripts/worktree-add.mjs APP-1-2 --repo C:\path\to\host
 *   node scripts/worktree-add.mjs APP-1-2          # if cwd is the host
 */
import fs from "fs";
import path from "path";
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

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

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

function looksLikeEngine(dir) {
  return (
    fs.existsSync(path.join(dir, "config.yaml")) &&
    fs.existsSync(path.join(dir, "scripts", "board.mjs")) &&
    !fs.existsSync(path.join(dir, ".linear", "config.yaml"))
  );
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

const argv = process.argv.slice(2).filter((a) => a !== "--repo");
const repoFlag = flag(process.argv.slice(2), "--repo");
const id = argv.find((a) => ISSUE_ID_RE.test(a));

if (!id) {
  console.error(
    "Usage: node scripts/worktree-add.mjs <ISSUE_ID> --repo <host-git-root>\n" +
      "Worktrees live on the host harness, not inside this board engine."
  );
  process.exit(1);
}

const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();

if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass the host git repo:\n" +
      "  node scripts/worktree-add.mjs " +
      id +
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
      `Remove it first: node scripts/worktree-rm.mjs ${id} --repo ${primary}`
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
`);
