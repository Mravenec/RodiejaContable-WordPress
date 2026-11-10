#!/usr/bin/env node
/**
 * Commit the ticket worktree (if dirty), then move the board issue to done.
 *
 *   node scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1 --repo C:\path\to\host
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
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
const agent = flag(argv, "--agent");
const repoFlag = flag(argv, "--repo");
if (!id || !agent) {
  console.error(
    'Usage: node scripts/worktree-finish.mjs <ISSUE_ID> --agent NAME --repo <host-git-root> [--message "…"]'
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
  console.error(`No worktree folder for ${id}`);
  process.exit(1);
}

const message = flag(argv, "--message") || `${id}: ticket work`;
const commit = commitTicketDir(found.dir, message, { primary });

const move = spawnSync(
  process.execPath,
  [
    path.join(linear, "scripts", "board.mjs"),
    "move",
    id,
    "done",
    "--agent",
    agent,
  ],
  { cwd: linear, encoding: "utf8" }
);
if (move.stdout) process.stdout.write(move.stdout);
if (move.stderr) process.stderr.write(move.stderr);
if (move.status !== 0) {
  console.error(
    JSON.stringify(
      {
        committed: commit.committed,
        sha: commit.sha || null,
        folder: found.dir,
        board: "move done failed — ticket is committed locally; fix flags and retry worktree-finish",
      },
      null,
      2
    )
  );
  process.exit(move.status ?? 1);
}

console.log(
  JSON.stringify(
    {
      finished: true,
      id,
      commit,
      folder: found.dir,
      next: "Dependents may claim. worktree-add will start from this committed branch (including cross-epic blocked_by).",
    },
    null,
    2
  )
);
