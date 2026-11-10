#!/usr/bin/env node
/**
 * Next epic to land onto main (after origin-ready).
 *   node .ai/scripts/worktree-land-next.mjs
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { git, gitOk } from "./lib/worktree-git.mjs";
import { nextEpicToLand, listLandQueue } from "./lib/land-main.mjs";
import { remoteHeadsHas } from "./lib/worktree-git.mjs";
import { listEpicKeys, epicBranchName } from "./lib/worktree-paths.mjs";

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

const startCwd = fs.existsSync(path.join(SCRIPT_ROOT, ".git"))
  ? SCRIPT_ROOT
  : process.cwd();

if (!gitOk(["rev-parse", "--is-inside-work-tree"], startCwd)) {
  console.error("Run this from a git checkout of the host harness.");
  process.exit(1);
}

const primary = primaryCheckout(startCwd);
const linear = path.join(primary, ".linear");
const keys = listEpicKeys(linear);
if (!keys.length) {
  console.error("No epics on the live board.");
  process.exit(1);
}
const missing = keys.filter((k) => !remoteHeadsHas(primary, epicBranchName(k)));
if (missing.length) {
  console.error(
    JSON.stringify(
      {
        ok: false,
        error: "originReady is false — push missing epics first",
        missingOnOrigin: missing.map((k) => epicBranchName(k)),
      },
      null,
      2
    )
  );
  process.exit(1);
}

const next = nextEpicToLand(primary, linear);
console.log(
  JSON.stringify(
    {
      ...next,
      hint: next.done
        ? "All epics landed. Run worktree-land-ready.mjs"
        : `Next: node .ai/scripts/worktree-land-main.mjs ${next.epicKey}`,
      queue: listLandQueue(primary, linear).map((r) => ({
        epicKey: r.epicKey,
        status: r.status,
        onOrigin: r.onOrigin,
      })),
    },
    null,
    2
  )
);
if (!next.ok) process.exit(1);
