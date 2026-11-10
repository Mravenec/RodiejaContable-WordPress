/**
 * Smoke: cross-epic blocked_by — create two epics, assert next/claim respect APP-1-1.
 * Cleans up after itself. Run: node .linear/scripts/_smoke-cross-epic.mjs
 */
import { spawnSync } from "child_process";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const LINEAR = path.resolve(__dirname, "..");
const board = path.join(LINEAR, "scripts", "board.mjs");

function run(args) {
  const r = spawnSync(process.execPath, [board, ...args], {
    cwd: LINEAR,
    encoding: "utf8",
  });
  return {
    status: r.status,
    out: (r.stdout || "") + (r.stderr || ""),
  };
}

function wipe(epicFolder) {
  const p = path.join(LINEAR, "epics", epicFolder);
  if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
}

wipe("91-smoke-a");
wipe("92-smoke-b");

let failed = false;
try {
  run(["epic", "create", "91", "smoke-a", "--title", "Smoke-A"]);
  run(["epic", "create", "92", "smoke-b", "--title", "Smoke-B"]);
  run(["issue", "create", "91", "--title", "DB gate", "--role", "database"]);
  run([
    "issue",
    "create",
    "92",
    "--title",
    "DB waits on epic 91",
    "--role",
    "database",
    "--blocked-by",
    "APP-91-1",
  ]);

  const nextWhileBlocked = run(["next", "--role", "database", "--epic", "92"]);
  if (nextWhileBlocked.out.includes("APP-92-1")) {
    console.error("FAIL: next returned APP-92-1 while APP-91-1 unfinished");
    failed = true;
  } else {
    console.log("OK: next skipped blocked APP-92-1");
  }

  const claimBlocked = run([
    "claim",
    "APP-92-1",
    "--agent",
    "smoke-agent",
  ]);
  if (claimBlocked.status === 0) {
    console.error("FAIL: claim APP-92-1 should fail while blocked");
    failed = true;
  } else if (!/blocked/i.test(claimBlocked.out)) {
    console.error("FAIL: claim error should mention blocked:", claimBlocked.out);
    failed = true;
  } else {
    console.log("OK: claim refused while cross-epic blocked");
  }

  const nextA = run(["next", "--role", "database", "--epic", "91"]);
  if (!nextA.out.includes("APP-91-1")) {
    console.error("FAIL: expected APP-91-1 available", nextA.out);
    failed = true;
  } else {
    console.log("OK: APP-91-1 available");
  }
} finally {
  wipe("91-smoke-a");
  wipe("92-smoke-b");
}

process.exit(failed ? 1 : 0);
