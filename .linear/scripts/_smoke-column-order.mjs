/**
 * Smoke: column order — testing before verification gate; summarize waits for whole epic.
 * Cleans up after itself. Run: node scripts/_smoke-column-order.mjs
 */
import { spawnSync } from "child_process";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { loadConfig } from "./lib/fs-board.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const LINEAR = path.resolve(__dirname, "..");
const board = path.join(LINEAR, "scripts", "board.mjs");
const EPIC_N = "93";
const EPIC_SLUG = "smoke-cols";
const team = loadConfig().team_key || "APP";
const ID1 = `${team}-${EPIC_N}-1`;
const ID2 = `${team}-${EPIC_N}-2`;

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

function wipe() {
  const p = path.join(LINEAR, "epics", `${EPIC_N}-${EPIC_SLUG}`);
  if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
  const claimsFile = path.join(LINEAR, "agents", "claims.json");
  if (!fs.existsSync(claimsFile)) return;
  try {
    const reg = JSON.parse(fs.readFileSync(claimsFile, "utf8"));
    if (reg.claims) {
      delete reg.claims[ID1];
      delete reg.claims[ID2];
      fs.writeFileSync(claimsFile, JSON.stringify(reg, null, 2) + "\n", "utf8");
    }
  } catch {
    /* ignore */
  }
}

wipe();

let failed = false;
try {
  run(["epic", "create", EPIC_N, EPIC_SLUG, "--title", "Smoke columns"]);
  run(["issue", "create", EPIC_N, "--title", "First", "--role", "orchestrator"]);
  run(["issue", "create", EPIC_N, "--title", "Second", "--role", "orchestrator"]);

  const claim = run(["claim", ID1, "--agent", "smoke-agent"]);
  if (claim.status !== 0) {
    console.error("FAIL: claim", ID1, claim.out);
    failed = true;
  }

  const skip = run(["checklist", ID1, "2", "--agent", "smoke-agent"]);
  if (skip.status === 0) {
    console.error("FAIL: out-of-order checklist 2 should be refused", skip.out);
    failed = true;
  } else if (!/Out of order/i.test(skip.out)) {
    console.error("FAIL: expected Out of order", skip.out);
    failed = true;
  } else {
    console.log("OK: out-of-order checklist mark refused");
  }

  const batch = run(["checklist", ID1, "1,2", "--agent", "smoke-agent"]);
  if (batch.status === 0) {
    console.error("FAIL: batch checklist should be refused", batch.out);
    failed = true;
  } else {
    console.log("OK: batch checklist mark refused");
  }

  const mark1 = run(["checklist", ID1, "1", "--agent", "smoke-agent"]);
  if (mark1.status !== 0) {
    console.error("FAIL: checklist 1", mark1.out);
    failed = true;
  }

  const toTesting = run(["move", ID1, "testing", "--agent", "smoke-agent"]);
  if (toTesting.status !== 0) {
    console.error(
      "FAIL: move testing with open verify item should succeed",
      toTesting.out
    );
    failed = true;
  } else {
    console.log("OK: move testing allowed with incomplete checklist");
  }

  const doneTooSoon = run(["move", ID1, "done", "--agent", "smoke-agent"]);
  if (doneTooSoon.status === 0) {
    console.error("FAIL: move done should fail while checklist incomplete");
    failed = true;
  } else {
    console.log("OK: move done refused until verification checklist is complete");
  }

  const mark2 = run(["checklist", ID1, "2", "--agent", "smoke-agent"]);
  if (mark2.status !== 0) {
    console.error("FAIL: checklist 2 in testing", mark2.out);
    failed = true;
  }

  const toDone = run(["move", ID1, "done", "--agent", "smoke-agent"]);
  if (toDone.status !== 0) {
    console.error("FAIL: move done after complete checklist", toDone.out);
    failed = true;
  } else {
    console.log("OK:", ID1, "parked in done");
  }

  const summarizeEarly = run([
    "move",
    ID1,
    "summarize",
    "--agent",
    "smoke-agent",
  ]);
  if (summarizeEarly.status === 0) {
    console.error("FAIL: summarize should wait for", ID2, summarizeEarly.out);
    failed = true;
  } else if (!new RegExp(`Still open: ${ID2}`, "i").test(summarizeEarly.out)) {
    console.error("FAIL: expected still-open message", summarizeEarly.out);
    failed = true;
  } else {
    console.log("OK: summarize refused while sibling still open");
  }

  run(["claim", ID2, "--agent", "smoke-agent"]);
  run(["checklist", ID2, "1", "--agent", "smoke-agent"]);
  run(["move", ID2, "testing", "--agent", "smoke-agent"]);
  run(["checklist", ID2, "2", "--agent", "smoke-agent"]);
  const done2 = run(["move", ID2, "done", "--agent", "smoke-agent"]);
  if (done2.status !== 0) {
    console.error("FAIL:", ID2, "done", done2.out);
    failed = true;
  }

  const summarizeOk = run([
    "move",
    ID1,
    "summarize",
    "--agent",
    "smoke-agent",
  ]);
  if (summarizeOk.status !== 0) {
    console.error("FAIL: summarize after whole epic in done", summarizeOk.out);
    failed = true;
  } else {
    console.log("OK: summarize allowed after every ticket is in done");
  }
} finally {
  wipe();
}

process.exit(failed ? 1 : 0);
