/**
 * Smoke: living summary HTML grows with board moves; testing fail returns to doing
 * (claim kept); handoff records git command + Supervisor / Manager note.
 * Cleans up after itself. Run: node scripts/_smoke-living-summary.mjs
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
const EPIC_SLUG = "smoke-living";
const team = loadConfig().team_key || "APP";
const ID1 = `${team}-${EPIC_N}-1`;
const epicDir = path.join(LINEAR, "epics", `${EPIC_N}-${EPIC_SLUG}`);
const htmlPath = path.join(
  epicDir,
  "summaries",
  `${EPIC_N}-${EPIC_SLUG}-beginner.html`
);
const GIT_LINE =
  `cd .worktrees/${EPIC_N}-${EPIC_SLUG}/_epic && git push -u origin feature/${EPIC_N}-${EPIC_SLUG}`;
const NOTE = `I recorded the living-summary smoke.

You can confirm it doing the following:
1. Open the living summary HTML
2. Expect the git command and this note`;

function run(args) {
  const r = spawnSync(process.execPath, [board, ...args], {
    cwd: LINEAR,
    encoding: "utf8",
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

function claimsHas(id) {
  const claimsFile = path.join(LINEAR, "agents", "claims.json");
  if (!fs.existsSync(claimsFile)) return false;
  const reg = JSON.parse(fs.readFileSync(claimsFile, "utf8"));
  return Boolean(reg.claims?.[id]);
}

wipe();

let failed = false;
try {
  run(["epic", "create", EPIC_N, EPIC_SLUG, "--title", "Smoke living summary"]);
  run(["issue", "create", EPIC_N, "--title", "Living", "--role", "orchestrator"]);

  const claim = run(["claim", ID1, "--agent", "smoke-living"]);
  if (claim.status !== 0) {
    console.error("FAIL: claim", claim.out);
    failed = true;
  } else {
    let payload = {};
    try {
      payload = JSON.parse(claim.stdout);
    } catch {
      /* ignore */
    }
    if (!String(payload.next || "").includes("worktree-add")) {
      console.error("FAIL: claim should print required worktree-add next", claim.stdout);
      failed = true;
    } else {
      console.log("OK: claim points at worktree-add");
    }
  }
  if (!fs.existsSync(htmlPath)) {
    console.error("FAIL: living HTML missing after claim", htmlPath);
    failed = true;
  } else {
    const html = fs.readFileSync(htmlPath, "utf8");
    if (!html.includes(ID1)) {
      console.error("FAIL: living HTML should list the ticket after claim");
      failed = true;
    } else if (!/study guide/i.test(html)) {
      console.error("FAIL: living HTML should read as a study guide");
      failed = true;
    } else if (!html.includes(`ticket/${EPIC_N}-${EPIC_SLUG}/${ID1}`)) {
      console.error("FAIL: living HTML should name the ticket branch");
      failed = true;
    } else {
      console.log("OK: living HTML exists after claim and lists", ID1);
    }
  }

  const ev = run([
    "evidence",
    ID1,
    "--agent",
    "smoke-living",
    "--title",
    "SQL example",
    "--path",
    "0.database/canonical_sql/01_demo.sql",
    "--lang",
    "sql",
    "--code",
    "ALTER TABLE account ADD COLUMN demo VARCHAR(8) NULL;",
    "--command",
    "python 0.database/scripts/setup_db.py",
    "--result",
    "OK column demo",
  ]);
  if (ev.status !== 0) {
    console.error("FAIL: evidence", ev.out);
    failed = true;
  } else {
    console.log("OK: evidence recorded");
  }
  if (fs.existsSync(htmlPath)) {
    const html = fs.readFileSync(htmlPath, "utf8");
    if (!html.includes("ALTER TABLE account ADD COLUMN demo")) {
      console.error("FAIL: living HTML should show evidence SQL");
      failed = true;
    } else if (!html.includes("What was built")) {
      console.error("FAIL: living HTML should have What was built");
      failed = true;
    } else {
      console.log("OK: living HTML shows ticket evidence");
    }
  }

  run(["checklist", ID1, "1", "--agent", "smoke-living"]);
  const toTesting = run(["move", ID1, "testing", "--agent", "smoke-living"]);
  if (toTesting.status !== 0) {
    console.error("FAIL: move testing", toTesting.out);
    failed = true;
  }

  const failCmd = run([
    "fail",
    ID1,
    "--agent",
    "smoke-living",
    "--reason",
    "HTTP gate failed",
  ]);
  if (failCmd.status !== 0) {
    console.error("FAIL: fail from testing", failCmd.out);
    failed = true;
  } else {
    let payload = {};
    try {
      payload = JSON.parse(failCmd.stdout);
    } catch {
      /* ignore */
    }
    if (payload.column !== "doing") {
      console.error("FAIL: fail from testing should land in doing", failCmd.out);
      failed = true;
    } else {
      console.log("OK: fail from testing returned to doing");
    }
  }
  if (!claimsHas(ID1)) {
    console.error("FAIL: fail from testing should keep the claim");
    failed = true;
  } else {
    console.log("OK: claim kept after testing fail");
  }

  if (fs.existsSync(htmlPath)) {
    const html = fs.readFileSync(htmlPath, "utf8");
    if (!/FAILED verification/i.test(html) || !html.includes("HTTP gate failed")) {
      console.error("FAIL: living HTML should show the testing bounce-back");
      failed = true;
    } else {
      console.log("OK: living HTML records testing → doing");
    }
  }

  const handoff = run([
    "handoff",
    EPIC_N,
    "--git",
    GIT_LINE,
    "--note",
    NOTE,
  ]);
  if (handoff.status !== 0) {
    console.error("FAIL: handoff", handoff.out);
    failed = true;
  } else {
    console.log("OK: handoff recorded");
  }

  const html = fs.existsSync(htmlPath) ? fs.readFileSync(htmlPath, "utf8") : "";
  if (!html.includes(`.worktrees/${EPIC_N}-${EPIC_SLUG}/_epic`)) {
    console.error("FAIL: living HTML should include the git command");
    failed = true;
  } else {
    console.log("OK: living HTML includes the git command");
  }
  if (!html.includes("I recorded the living-summary smoke.")) {
    console.error("FAIL: living HTML should include the Supervisor / Manager note");
    failed = true;
  } else {
    console.log("OK: living HTML includes the Supervisor / Manager note");
  }
} finally {
  wipe();
}

process.exit(failed ? 1 : 0);
