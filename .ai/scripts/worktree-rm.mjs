#!/usr/bin/env node
/**
 * Manual escape hatch: remove the git worktree for one leftover ticket folder.
 * Integrate already drops ticket folders; this is for leftovers only.
 *
 *   node .ai/scripts/worktree-rm.mjs APP-1-2
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { spawnSync } from "child_process";
import { ISSUE_ID_RE, findTicketWorktreeDir } from "./lib/worktree-paths.mjs";
import { unlinkSharedBoardLinks } from "./lib/board-share.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

function run(cmd, args, cwd) {
  return spawnSync(cmd, args, { cwd, encoding: "utf8" });
}

function git(args, cwd, { allowFail = false } = {}) {
  const r = run("git", args, cwd);
  if (r.status !== 0 && !allowFail) {
    process.stderr.write(r.stderr || r.stdout || "git failed\n");
    process.exit(r.status ?? 1);
  }
  return r;
}

function gitOk(args, cwd) {
  return run("git", args, cwd).status === 0;
}

function primaryCheckout(cwd) {
  const r = git(["worktree", "list", "--porcelain"], cwd);
  const line = (r.stdout || "")
    .split(/\r?\n/)
    .find((l) => l.startsWith("worktree "));
  if (!line) {
    console.error("Could not read git worktree list.");
    process.exit(1);
  }
  return line.slice("worktree ".length).trim();
}

const id = process.argv[2];
if (!id || !ISSUE_ID_RE.test(id)) {
  console.error("Usage: node .ai/scripts/worktree-rm.mjs <ISSUE_ID>");
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
const found = findTicketWorktreeDir(primary, linear, id);

if (!found) {
  console.error(
    `No worktree folder for ${id} (looked under .worktrees/{n}-{slug}/${id} and legacy .worktrees/${id}).`
  );
  process.exit(1);
}

const { dir, branch, legacy } = found;

unlinkSharedBoardLinks(dir);

let r = git(["worktree", "remove", dir], primary, { allowFail: true });
if (r.status !== 0) {
  r = git(["worktree", "remove", "--force", dir], primary, { allowFail: true });
}
if (r.status !== 0) {
  process.stderr.write(r.stderr || r.stdout || "git worktree remove failed\n");
  process.exit(r.status ?? 1);
}

console.log(`Removed worktree
  folder : ${dir}${legacy ? "  (legacy path)" : ""}
  branch : ${branch}  (still exists — merge into the epic branch, then delete if you want)
`);
