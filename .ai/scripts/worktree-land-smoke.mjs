#!/usr/bin/env node
/**
 * Cumulative smoke after merging an epic onto main.
 * Refuses a pass (explicit or automatic) when the layer audit finds violations on main.
 *
 *   node .ai/scripts/worktree-land-smoke.mjs 1
 *   node .ai/scripts/worktree-land-smoke.mjs 1 --pass
 *   node .ai/scripts/worktree-land-smoke.mjs 1 --fail --reason "Health Network Error"
 */
import fs from "fs";
import path from "path";
import { pathToFileURL, fileURLToPath } from "url";
import http from "http";
import { git, gitOk } from "./lib/worktree-git.mjs";
import {
  resolveEpicKey,
  readLand,
  writeLand,
  epicPathForKey,
} from "./lib/land-main.mjs";
import { runLayerAudit, formatAudit } from "./lib/layer-audit.mjs";

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

function flag(argv, name) {
  return argv.includes(name);
}

function flagVal(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? null;
}

function probe(url, timeoutMs = 2500) {
  return new Promise((resolve) => {
    const req = http.get(url, (res) => {
      res.resume();
      resolve({ ok: res.statusCode >= 200 && res.statusCode < 300, status: res.statusCode });
    });
    req.on("error", (err) => resolve({ ok: false, error: err.message }));
    req.setTimeout(timeoutMs, () => {
      req.destroy();
      resolve({ ok: false, error: "timeout" });
    });
  });
}

function readJourneyVerify(primary, epicKey) {
  const p = path.join(primary, ".docs", "journey", `${epicKey}.md`);
  if (!fs.existsSync(p)) {
    // after merge it should exist
    return null;
  }
  const text = fs.readFileSync(p, "utf8");
  const m = text.match(/## How to verify\r?\n([\s\S]*?)(?=\n## |\n# |$)/);
  return m ? m[1].trim() : null;
}

const argv = process.argv.slice(2);
const ref = argv.find((a) => a && !String(a).startsWith("--"));
if (!ref) {
  console.error(
    "Usage: node .ai/scripts/worktree-land-smoke.mjs <n|slug|{n}-{slug}> [--pass|--fail --reason …]"
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
const epicKey = resolveEpicKey(linear, ref);
const epicPath = epicPathForKey(linear, epicKey);
const land = readLand(epicPath);

if (!land.merge_commit && land.status !== "smoke-main" && land.status !== "landing") {
  console.error(
    JSON.stringify(
      {
        ok: false,
        error: `Epic ${epicKey} is ${land.status}. Run worktree-land-main first.`,
      },
      null,
      2
    )
  );
  process.exit(1);
}

const clockApi = await import(
  pathToFileURL(path.join(linear, "scripts", "lib", "work-clock.mjs")).href
);
let config = { time: { idle_ms: 180000 } };
try {
  const fsBoard = await import(
    pathToFileURL(path.join(linear, "scripts", "lib", "fs-board.mjs")).href
  );
  config = fsBoard.loadConfig();
} catch {
  /* default */
}
const clock = clockApi.pulseLandClock(config, epicPath, {
  agent: "land-agent",
  event: "land-smoke",
});

const verify = readJourneyVerify(primary, epicKey);
const probes = {
  health: await probe("http://127.0.0.1:8080/health"),
};
const layerAudit = runLayerAudit(primary);
const audit = {
  ok: layerAudit.ok,
  skipped: layerAudit.skipped || false,
  violations: layerAudit.violations.length,
};

const pass = flag(argv, "--pass");
const fail = flag(argv, "--fail");
const reason = flagVal(argv, "--reason");

if (!fail && !layerAudit.ok) {
  land.smoke_ok = false;
  land.smoke_at = new Date().toISOString();
  land.status = "smoke-main";
  writeLand(epicPath, land);
  console.error(formatAudit(layerAudit));
  console.error(
    JSON.stringify(
      {
        ok: false,
        epicKey,
        smoke_ok: false,
        reason: "layer audit failed on main — fix on main (land conflict resolution), commit, re-run land smoke",
        audit,
        probes,
        clock,
      },
      null,
      2
    )
  );
  process.exit(1);
}

if (fail) {
  land.smoke_ok = false;
  land.smoke_at = new Date().toISOString();
  land.status = "smoke-main";
  writeLand(epicPath, land);
  console.error(
    JSON.stringify(
      {
        ok: false,
        epicKey,
        smoke_ok: false,
        reason: reason || "smoke failed",
        verify,
        probes,
        audit,
        clock,
      },
      null,
      2
    )
  );
  process.exit(1);
}

if (pass) {
  land.smoke_ok = true;
  land.smoke_at = new Date().toISOString();
  land.status = "smoke-main";
  writeLand(epicPath, land);
  console.log(
    JSON.stringify(
      {
        ok: true,
        epicKey,
        smoke_ok: true,
        verify,
        probes,
        audit,
        clock,
        next: `Write a Supervisor / Manager land note, then:\nnode .ai/scripts/worktree-land-finish.mjs ${epicKey} --note-file <path>`,
      },
      null,
      2
    )
  );
  process.exit(0);
}

// Auto: if health responds 2xx, mark pass; else print checklist and require --pass
if (probes.health.ok) {
  land.smoke_ok = true;
  land.smoke_at = new Date().toISOString();
  land.status = "smoke-main";
  writeLand(epicPath, land);
  console.log(
    JSON.stringify(
      {
        ok: true,
        epicKey,
        smoke_ok: true,
        auto: "GET /health 2xx",
        verify,
        probes,
        audit,
        clock,
        next: `node .ai/scripts/worktree-land-finish.mjs ${epicKey} --note-file <path>`,
      },
      null,
      2
    )
  );
  process.exit(0);
}

console.log(
  JSON.stringify(
    {
      ok: false,
      pending: true,
      epicKey,
      message:
        "Runtime Health not reachable. Start MariaDB + Spring, verify Vite, then re-run with --pass (or --fail).",
      verify,
      probes,
      audit,
      clock,
      commands: {
        pass: `node .ai/scripts/worktree-land-smoke.mjs ${epicKey} --pass`,
        fail: `node .ai/scripts/worktree-land-smoke.mjs ${epicKey} --fail --reason "…"`,
      },
    },
    null,
    2
  )
);
process.exit(2);
