#!/usr/bin/env node
/**
 * Manual escape hatch: remove one leftover ticket worktree on the host repo.
 * Integrate already drops ticket folders; this is for leftovers only.
 *
 *   node scripts/worktree-rm.mjs APP-1-2 --repo C:\path\to\host
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
import { ISSUE_ID_RE, findTicketWorktreeDir } from "./lib/worktree-paths.mjs";
import { unlinkSharedBoardLinks } from "./lib/board-share.mjs";

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

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

function looksLikeEngine(dir) {
  return (
    fs.existsSync(path.join(dir, "config.yaml")) &&
    fs.existsSync(path.join(dir, "scripts", "board.mjs")) &&
    !fs.existsSync(path.join(dir, ".linear", "config.yaml"))
  );
}

const argv = process.argv.slice(2).filter((a) => a !== "--repo");
const repoFlag = flag(process.argv.slice(2), "--repo");
const id = argv.find((a) => ISSUE_ID_RE.test(a));

if (!id) {
  console.error(
    "Usage: node scripts/worktree-rm.mjs <ISSUE_ID> --repo <host-git-root>"
  );
  process.exit(1);
}

const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();

if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass the host git repo:\n" +
      "  node scripts/worktree-rm.mjs " +
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
