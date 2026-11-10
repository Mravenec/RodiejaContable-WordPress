#!/usr/bin/env node
/**
 * Local-commit one ticket worktree (never push).
 *
 *   node .ai/scripts/worktree-commit.mjs APP-1-2
 *   node .ai/scripts/worktree-commit.mjs APP-1-2 --message "APP-1-2: short description"
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import {
  ISSUE_ID_RE,
  findTicketWorktreeDir,
} from "./lib/worktree-paths.mjs";
import { gitOk, git, commitTicketDir } from "./lib/worktree-git.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
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
if (!id) {
  console.error(
    "Usage: node .ai/scripts/worktree-commit.mjs <ISSUE_ID> [--message \"…\"]"
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
