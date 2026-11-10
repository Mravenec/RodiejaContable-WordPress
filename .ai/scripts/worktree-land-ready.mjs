#!/usr/bin/env node
/**
 * Gate after all epics are landed on main.
 *   node .ai/scripts/worktree-land-ready.mjs
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { git, gitOk, remoteHeadsHas, sweepMergedTicketBranches } from "./lib/worktree-git.mjs";
import { listLandQueue, backfillInferredLands } from "./lib/land-main.mjs";
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
const backfilled = backfillInferredLands(primary, linear);
const ticketSweep = sweepMergedTicketBranches(primary, null);
const queue = listLandQueue(primary, linear);
const pending = queue.filter((r) => r.status !== "landed");
const landReady = pending.length === 0 && queue.length > 0;

console.log(
  JSON.stringify(
    {
      landReady,
      backfilled,
      ticketBranches: ticketSweep,
      epics: queue.map((r) => ({
        epicKey: r.epicKey,
        status: r.status,
        onOrigin: r.onOrigin,
        merge_commit: r.merge_commit,
      })),
      pending: pending.map((r) => r.epicKey),
      next: landReady
        ? [
            "Every epic is landed on main. Product + journeys are on origin/main.",
            "Hours worked includes build + land clocks.",
          ]
        : [
            `Next: node .ai/scripts/worktree-land-next.mjs`,
            pending[0]
              ? `Then: node .ai/scripts/worktree-land-main.mjs ${pending[0].epicKey}`
              : null,
          ].filter(Boolean),
    },
    null,
    2
  )
);
if (!landReady) process.exit(1);
if (!ticketSweep.ok && ticketSweep.remaining.length) {
  console.error(
    `Leftover local ticket branches (should be empty after land): ${ticketSweep.remaining.join(", ")}`
  );
  process.exit(1);
}
