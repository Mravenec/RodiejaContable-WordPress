/**
 * Smoke: epic % rises when a checklist item is marked (not only on column move).
 * Cleans up after itself. Run: node scripts/_smoke-progress-checklist.mjs
 */
import { spawnSync } from "child_process";
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { loadConfig } from "./lib/fs-board.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const LINEAR = path.resolve(__dirname, "..");
const board = path.join(LINEAR, "scripts", "board.mjs");
const EPIC_N = "94";
const EPIC_SLUG = "smoke-pct-check";
const team = loadConfig().team_key || "APP";
const ID1 = `${team}-${EPIC_N}-1`;

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
      fs.writeFileSync(claimsFile, JSON.stringify(reg, null, 2) + "\n", "utf8");
    }
  } catch {
    /* ignore */
  }
}

function progressPct() {
  const r = run(["progress", "--epic", EPIC_N]);
  const m = r.out.match(/(\d+)%/);
  return m ? Number(m[1]) : null;
}

wipe();

let failed = false;
try {
  run(["epic", "create", EPIC_N, EPIC_SLUG, "--title", "Smoke checklist %"]);
  run(["issue", "create", EPIC_N, "--title", "First", "--role", "orchestrator"]);

  const atBacklog = progressPct();
  if (atBacklog !== 0) {
    console.error("FAIL: backlog with empty marks should be 0%", atBacklog);
    failed = true;
  } else {
    console.log("OK: backlog 0%");
  }

  const claim = run(["claim", ID1, "--agent", "smoke-pct"]);
  if (claim.status !== 0) {
    console.error("FAIL: claim", claim.out);
    failed = true;
  }

  const afterClaim = progressPct();
  if (afterClaim !== 16) {
    console.error("FAIL: doing with 0/2 checklist expected 16%, got", afterClaim);
    failed = true;
  } else {
    console.log("OK: claim → doing 16%");
  }

  const mark = run(["checklist", ID1, "1", "--agent", "smoke-pct"]);
  if (mark.status !== 0) {
    console.error("FAIL: checklist 1", mark.out);
    failed = true;
  }
  let parsed;
  try {
    parsed = JSON.parse(mark.out);
  } catch {
    parsed = {};
  }
  if (parsed.epicProgress !== 33) {
    console.error("FAIL: checklist JSON epicProgress expected 33, got", parsed.epicProgress, mark.out);
    failed = true;
  }

  const afterMark = progressPct();
  if (afterMark !== 33) {
    console.error("FAIL: after checklist expected 33%, got", afterMark);
    failed = true;
  } else {
    console.log("OK: checklist item raises epic % to 33%");
  }
} finally {
  wipe();
}

if (failed) {
  process.exit(1);
}
console.log("OK: checklist items contribute to epic %");
