#!/usr/bin/env node
/**
 * After the human pushes feature/{n}-{slug} from _epic:
 *   - remove the _epic worktree
 *   - delete the local epic branch (commits stay on origin)
 *
 * Run from the primary checkout (not from inside _epic).
 *
 *   node .ai/scripts/worktree-after-push.mjs 1
 *   node .ai/scripts/worktree-after-push.mjs 1-my-feature
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { resolveEpicKeyFromRef } from "./lib/worktree-paths.mjs";
import { gitOk, git, pruneEpicWorktreeAfterPush } from "./lib/worktree-git.mjs";

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
  console.error(
    "Usage: node .ai/scripts/worktree-after-push.mjs <n|slug|{n}-{slug}>"
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
  epicKey = resolveEpicKeyFromRef(linear, ref);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const result = pruneEpicWorktreeAfterPush(primary, epicKey);
if (!result.ok && result.error) {
  console.error(result.error);
  process.exit(1);
}
console.log(JSON.stringify({ prunedEpic: true, ...result }, null, 2));
if (!result.ok) process.exit(1);
