#!/usr/bin/env node
/**
 * Local-commit one ticket worktree on the host repo (never push).
 *
 *   node scripts/worktree-commit.mjs APP-1-2 --repo C:\path\to\host
 *   node scripts/worktree-commit.mjs APP-1-2 --repo C:\path\to\host --message "APP-1-2: …"
 */
import fs from "fs";
import path from "path";
import { ISSUE_ID_RE, findTicketWorktreeDir } from "./lib/worktree-paths.mjs";
import { gitOk, git, commitTicketDir } from "./lib/worktree-git.mjs";

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

function looksLikeEngine(dir) {
  return (
    fs.existsSync(path.join(dir, "config.yaml")) &&
    fs.existsSync(path.join(dir, "scripts", "board.mjs")) &&
    !fs.existsSync(path.join(dir, ".linear", "config.yaml"))
  );
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

const argv = process.argv.slice(2);
const id = argv.find((a) => ISSUE_ID_RE.test(a));
const repoFlag = flag(argv, "--repo");
if (!id) {
  console.error(
    "Usage: node scripts/worktree-commit.mjs <ISSUE_ID> --repo <host-git-root> [--message \"…\"]"
  );
  process.exit(1);
}

const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();
if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass --repo <host-git-root>."
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
if (!found?.dir || !fs.existsSync(found.dir)) {
  console.error(`No worktree folder for ${id} under ${primary}\\.worktrees`);
  process.exit(1);
}

const message = flag(argv, "--message") || `${id}: ticket work`;
const result = commitTicketDir(found.dir, message, { primary });
console.log(
  JSON.stringify(
    {
      ...result,
      id,
      folder: found.dir,
      note: "Local commit only. Never push ticket branches.",
    },
    null,
    2
  )
);
