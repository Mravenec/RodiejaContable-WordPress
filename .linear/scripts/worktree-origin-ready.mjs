#!/usr/bin/env node
/**
 * Gate before merging anything into main (engine copy).
 *
 *   node scripts/worktree-origin-ready.mjs --repo C:\path\to\host
 */
import fs from "fs";
import path from "path";
import { listEpicKeys, epicBranchName } from "./lib/worktree-paths.mjs";
import {
  gitOk,
  git,
  remoteHeadsHas,
  isPersistDocRel,
  run,
} from "./lib/worktree-git.mjs";

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

const argv = process.argv.slice(2);
const repoFlag = flag(argv, "--repo");
const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();

if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass the host git repo:\n" +
      "  node scripts/worktree-origin-ready.mjs --repo <host-git-root>"
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
            "Do not merge into main yet — that is a later, separate step.",
            "Untracked .docs/board and .docs/journey on main are expected (they ride feature/*). Do not git add them on main.",
            "Park any primaryOtherDirty (process copies) before a future merge into main.",
          ]
        : [
            "Finish integrate + handoff + worktree-push for each missing epic branch.",
            `Then re-run: node scripts/worktree-origin-ready.mjs --repo ${primary}`,
          ],
    },
    null,
    2
  )
);
if (!originReady) process.exit(1);
