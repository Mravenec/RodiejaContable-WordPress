#!/usr/bin/env node
/**
 * Commit the ticket worktree (if dirty), then move the board issue to done.
 * Dependents must not be claimed until this succeeds — otherwise their
 * worktree-add would start without the files.
 * Refuses before committing when the layer audit (layer-audit.mjs) finds violations.
 *
 *   node .ai/scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1
 *   node .ai/scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1 --message "APP-1-2: …"
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { spawnSync } from "child_process";
import {
  ISSUE_ID_RE,
  findTicketWorktreeDir,
} from "./lib/worktree-paths.mjs";
import { gitOk, git, commitTicketDir } from "./lib/worktree-git.mjs";
import { runLayerAudit, formatAudit } from "./lib/layer-audit.mjs";

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
const agent = flag(argv, "--agent");
if (!id || !agent) {
  console.error(
    'Usage: node .ai/scripts/worktree-finish.mjs <ISSUE_ID> --agent NAME [--message "…"]'
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
  console.error(`No worktree folder for ${id}`);
  process.exit(1);
}

const audit = runLayerAudit(found.dir);
if (!audit.ok) {
  console.error(formatAudit(audit));
  console.error(
    JSON.stringify(
      {
        finished: false,
        id,
        folder: found.dir,
        board: "not moved — fix the layer violations in the ticket folder, then retry worktree-finish",
        audit: "node .ai/scripts/layer-audit.mjs --root " + found.dir,
      },
      null,
      2
    )
  );
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
