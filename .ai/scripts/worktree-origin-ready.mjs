#!/usr/bin/env node
/**
 * Gate before merging anything into main.
 *
 * Reports whether every board epic has `origin/feature/{n}-{slug}`.
 * Does **not** merge into main.
 *
 *   node .ai/scripts/worktree-origin-ready.mjs
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import {
  listEpicKeys,
  epicBranchName,
} from "./lib/worktree-paths.mjs";
import {
  gitOk,
  git,
  remoteHeadsHas,
  isPersistDocRel,
  run,
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

function classifyPrimaryDirt(primary) {
  const r = run("git", ["status", "--porcelain"], primary);
  const persist = [];
  const other = [];
  for (const line of String(r.stdout || "").split(/\r?\n/)) {
    if (!line.trim()) continue;
    const rel = line.slice(3).trim().split(" -> ").pop().replace(/\\/g, "/");
    if (isPersistDocRel(rel)) persist.push(line);
    else other.push(line);
  }
  return { persist, other };
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

const epicKeys = listEpicKeys(linear);
if (!epicKeys.length) {
  console.error("No epics on the live board (.linear/epics).");
  process.exit(1);
}

const epics = epicKeys.map((epicKey) => {
  const epicBranch = epicBranchName(epicKey);
  return {
    epicKey,
    epicBranch,
    onOrigin: remoteHeadsHas(primary, epicBranch),
  };
});
const missing = epics.filter((e) => !e.onOrigin);
const dirt = classifyPrimaryDirt(primary);
const originReady = missing.length === 0;

console.log(
  JSON.stringify(
    {
      originReady,
      liveBoard: path.join(linear, "epics"),
      epics,
      missingOnOrigin: missing.map((e) => e.epicBranch),
      primaryCheckout: primary,
      primaryPersistUntrackedOrDirty: dirt.persist,
      primaryOtherDirty: dirt.other,
      next: originReady
        ? [
            "Every epic branch is on origin. Live board stays in .linear/epics (gitignored).",
            "Next: land onto main in epic order — node .ai/scripts/worktree-land-next.mjs",
            "Untracked .docs/board and .docs/journey on main are expected (they ride feature/*). Do not git add them on main; land-main clears the epic being merged.",
            "Park any primaryOtherDirty (process copies) before worktree-land-main.",
          ]
        : [
            "Finish integrate + handoff + worktree-push for each missing epic branch.",
            "Then re-run: node .ai/scripts/worktree-origin-ready.mjs",
          ],
    },
    null,
    2
  )
);
if (!originReady) process.exit(1);
