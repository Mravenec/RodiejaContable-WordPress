#!/usr/bin/env node
/**
 * Merge origin/feature/{n}-{slug} into main (serial land).
 * Starts the land clock. Does not push main.
 *
 *   node .ai/scripts/worktree-land-main.mjs 1
 *   node .ai/scripts/worktree-land-main.mjs 1-foundation
 */
import fs from "fs";
import path from "path";
import { pathToFileURL, fileURLToPath } from "url";
import { git, gitOk } from "./lib/worktree-git.mjs";
import {
  resolveEpicKey,
  assertPriorEpicsLanded,
  assertMainCleanEnough,
  clearUntrackedPersistForEpic,
  mergeFeatureIntoMain,
  readLand,
  writeLand,
  epicPathForKey,
} from "./lib/land-main.mjs";

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

async function loadClockApi(linearRoot) {
  return import(
    pathToFileURL(path.join(linearRoot, "scripts", "lib", "work-clock.mjs")).href
  );
}

async function loadConfig(linearRoot) {
  try {
    const mod = await import(
      pathToFileURL(path.join(linearRoot, "scripts", "lib", "fs-board.mjs")).href
    );
    return mod.loadConfig();
  } catch {
    return { time: { idle_ms: 180000 } };
  }
}

const ref = process.argv.slice(2).find((a) => a && !String(a).startsWith("--"));
if (!ref) {
  console.error(
    "Usage: node .ai/scripts/worktree-land-main.mjs <n|slug|{n}-{slug}>"
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

let epicKey;
try {
  epicKey = resolveEpicKey(linear, ref);
  assertPriorEpicsLanded(primary, linear, epicKey);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const clean = assertMainCleanEnough(primary, epicKey);
if (!clean.ok) {
  console.error(JSON.stringify({ ok: false, epicKey, error: clean.error }, null, 2));
  process.exit(1);
}

const removed = clearUntrackedPersistForEpic(primary, epicKey);
const epicPath = epicPathForKey(linear, epicKey);
const land = readLand(epicPath);
land.status = "landing";
if (!land.started_at) land.started_at = new Date().toISOString();
writeLand(epicPath, land);

const clockApi = await loadClockApi(linear);
const config = await loadConfig(linear);
const clock = clockApi.pulseLandClock(config, epicPath, {
  agent: "land-agent",
  event: "land-main",
});

const merged = mergeFeatureIntoMain(primary, epicKey);
if (!merged.ok) {
  land.status = "landing";
  land.conflicts = merged.conflicts || [];
  writeLand(epicPath, land);
  console.error(
    JSON.stringify(
      {
        ok: false,
        epicKey,
        removedPersist: removed,
        clock,
        ...merged,
        next: merged.conflicts?.length
          ? "Resolve conflicts on main, git add, git commit, then re-run land-main"
          : "Fix the error and re-run",
      },
      null,
      2
    )
  );
  process.exit(1);
}

land.status = "smoke-main";
land.merge_commit = merged.merge_commit;
land.conflicts = merged.conflicts || [];
land.finished_at = null;
writeLand(epicPath, land);

console.log(
  JSON.stringify(
    {
      ok: true,
      epicKey,
      removedPersist: removed,
      already: Boolean(merged.already),
      merge_commit: merged.merge_commit,
      landStatus: land.status,
      clock,
      next: `node .ai/scripts/worktree-land-smoke.mjs ${epicKey}`,
    },
    null,
    2
  )
);
