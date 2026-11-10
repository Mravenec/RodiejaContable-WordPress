/**
 * Smoke: active work clock pauses on idle / explicit pause; handoff freezes totals.
 * Cleans up after itself. Run: node scripts/_smoke-work-clock.mjs
 */
import { spawnSync } from "child_process";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { loadConfig } from "./lib/fs-board.mjs";
import { readClock, clockTotals } from "./lib/work-clock.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const LINEAR = path.resolve(__dirname, "..");
const board = path.join(LINEAR, "scripts", "board.mjs");
const EPIC_N = "95";
const EPIC_SLUG = "smoke-clock";
const team = loadConfig().team_key || "APP";
const ID1 = `${team}-${EPIC_N}-1`;
const epicDir = path.join(LINEAR, "epics", `${EPIC_N}-${EPIC_SLUG}`);
const htmlPath = path.join(
  epicDir,
  "summaries",
  `${EPIC_N}-${EPIC_SLUG}-beginner.html`
);
const IDLE = 80;

function sleep(ms) {
  spawnSync(process.execPath, ["-e", `Atomics.wait(new Int32Array(new SharedArrayBuffer(4)),0,0,${ms})`], {
    encoding: "utf8",
  });
}

function run(args) {
  const r = spawnSync(process.execPath, [board, ...args], {
    cwd: LINEAR,
    encoding: "utf8",
    env: { ...process.env, BOARD_TIME_IDLE_MS: String(IDLE) },
  });
  return {
    status: r.status,
    stdout: r.stdout || "",
    out: (r.stdout || "") + (r.stderr || ""),
  };
}

function wipe() {
  if (fs.existsSync(epicDir)) fs.rmSync(epicDir, { recursive: true, force: true });
  const claimsFile = path.join(LINEAR, "agents", "claims.json");
  if (!fs.existsSync(claimsFile)) return;
  try {
    const reg = JSON.parse(fs.readFileSync(claimsFile, "utf8"));
    if (reg.claims) {
      delete reg.claims[ID1];
      fs.writeFileSync(claimsFile, JSON.stringify(reg, null, 2) + "\n", "utf8");
    }
  } catch {
    /* ignore */
  }
}

wipe();

let failed = false;
try {
  run(["epic", "create", EPIC_N, EPIC_SLUG, "--title", "Smoke clock"]);
  run(["issue", "create", EPIC_N, "--title", "Clock", "--role", "orchestrator"]);
  const claim = run(["claim", ID1, "--agent", "smoke-clock"]);
  if (claim.status !== 0) {
    console.error("FAIL: claim", claim.out);
    failed = true;
  }

  const afterClaim = clockTotals(readClock(epicDir, loadConfig()));
  if (!afterClaim.issues[ID1]?.running) {
    console.error("FAIL: clock should be running after claim", afterClaim);
    failed = true;
  } else {
    console.log("OK: clock running after claim");
  }

  const paused = run([
    "clock",
    "pause",
    ID1,
    "--reason",
    "waiting for approval",
  ]);
  if (paused.status !== 0) {
    console.error("FAIL: clock pause", paused.out);
    failed = true;
  }
  const afterPause = clockTotals(readClock(epicDir, loadConfig()));
  if (!afterPause.issues[ID1]?.paused || afterPause.issues[ID1]?.running) {
    console.error("FAIL: pause should stop the open segment", afterPause.issues[ID1]);
    failed = true;
  } else {
    console.log("OK: clock pause (approval wait) stops counting");
  }

  sleep(IDLE + 40);
  const resume = run(["clock", "resume", ID1, "--agent", "smoke-clock"]);
  if (resume.status !== 0) {
    console.error("FAIL: clock resume", resume.out);
    failed = true;
  }

  run(["checklist", ID1, "1", "--agent", "smoke-clock"]);
  sleep(IDLE + 40);
  run(["move", ID1, "testing", "--agent", "smoke-clock"]);

  const afterIdle = readClock(epicDir, loadConfig());
  const segs = afterIdle.issues[ID1]?.segments || [];
  const idleClose = segs.some((s) => s.close === "idle") || segs.some((s) => s.close === "pause");
  if (!idleClose) {
    console.error("FAIL: expected an idle or pause close after a long gap", segs);
    failed = true;
  } else {
    console.log("OK: long gap was not counted as one continuous block");
  }

  run(["checklist", ID1, "2", "--agent", "smoke-clock"]);
  run(["move", ID1, "done", "--agent", "smoke-clock"]);
  const afterDone = clockTotals(readClock(epicDir, loadConfig()));
  if (afterDone.issues[ID1]?.running) {
    console.error("FAIL: parked in done should close the ticket clock");
    failed = true;
  } else {
    console.log("OK: done parks and stops the ticket clock");
  }

  const handoff = run([
    "handoff",
    EPIC_N,
    "--git",
    `cd .worktrees/${EPIC_N}-${EPIC_SLUG}/_epic && git push -u origin feature/${EPIC_N}-${EPIC_SLUG}`,
    "--note",
    "I finished the clock smoke.\n\nYou can confirm it doing the following:\n1. Open the living summary",
  ]);
  if (handoff.status !== 0) {
    console.error("FAIL: handoff", handoff.out);
    failed = true;
  }
  const finished = clockTotals(readClock(epicDir, loadConfig()));
  if (!finished.finished_at) {
    console.error("FAIL: handoff should freeze epic time", finished);
    failed = true;
  } else {
    console.log("OK: handoff freezes epic time", finished.active_label);
  }

  const html = fs.existsSync(htmlPath) ? fs.readFileSync(htmlPath, "utf8") : "";
  if (!html.includes("Active work time") || !html.includes(finished.active_label)) {
    console.error("FAIL: living HTML should show active hours/minutes");
    failed = true;
  } else {
    console.log("OK: living HTML shows active work time");
  }
} finally {
  wipe();
}

process.exit(failed ? 1 : 0);
