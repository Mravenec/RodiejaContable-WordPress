#!/usr/bin/env node
/**
 * After summarize + handoff: publish feature/{n}-{slug} from _epic, then
 * drop the local _epic worktree. Commits stay on origin.
 *
 *   node .ai/scripts/worktree-push.mjs 1
 *   node .ai/scripts/worktree-push.mjs 1-my-feature
 *
 * Never --force. Never HEAD. Never ticket branches.
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { resolveEpicKeyFromRef } from "./lib/worktree-paths.mjs";
import {
  gitOk,
  git,
  pushEpicBranch,
  pruneEpicWorktreeAfterPush,
  sweepMergedTicketBranches,
} from "./lib/worktree-git.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

function primaryCheckout(cwd) {
  const out = git(["worktree", "list", "--porcelain"], cwd);
  const line = out.split(/\r?\n/).find((l) => l.startsWith("worktree "));
  if (!line) {
    console.error("Could not read git worktree list.");
    process.exit(1);
  }
  return line.slice("worktree ".length).trim();
}

const ref = process.argv.slice(2).find((a) => a && !String(a).startsWith("--"));
if (!ref) {
  console.error("Usage: node .ai/scripts/worktree-push.mjs <n|slug|{n}-{slug}>");
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
  epicKey = resolveEpicKeyFromRef(linear, ref);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const pushed = pushEpicBranch(primary, epicKey);
if (!pushed.ok) {
  console.error(pushed.error);
  process.exit(1);
}

const pruned = pruneEpicWorktreeAfterPush(primary, epicKey);
if (!pruned.ok && pruned.error) {
  console.error(pruned.error);
  process.exit(1);
}
const ticketSweep = sweepMergedTicketBranches(primary, epicKey);
console.log(
  JSON.stringify(
    {
      pushed: true,
      ...pushed,
      prunedEpic: true,
      ...pruned,
      ticketBranches: ticketSweep,
    },
    null,
    2
  )
);
if (!pruned.ok) process.exit(1);
if (!ticketSweep.ok && ticketSweep.remaining.length) process.exit(1);
