#!/usr/bin/env node
/**
 * Optional neutral demo epic — safe to re-run (wipes this demo only).
 * Prefer starting real work with: node .ai/scripts/epic-new.mjs …
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { spawnSync } from "child_process";
import { ROOT, loadConfig } from "./lib/fs-board.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function run(args) {
  const r = spawnSync(process.execPath, [path.join(__dirname, "board.mjs"), ...args], {
    cwd: ROOT,
    encoding: "utf8",
  });
  if (r.stdout) process.stdout.write(r.stdout);
  if (r.stderr) process.stderr.write(r.stderr);
  if (r.status !== 0) process.exit(r.status ?? 1);
}

const config = loadConfig();
const n = "1";
const slug = "hello-board";
const epicPath = path.join(ROOT, "epics", `${n}-${slug}`);

if (fs.existsSync(epicPath)) {
  fs.rmSync(epicPath, { recursive: true, force: true });
}

const claimsFile = path.join(ROOT, config.claims_file || "agents/claims.json");
fs.writeFileSync(claimsFile, JSON.stringify({ claims: {} }, null, 2) + "\n");

run([
  "epic",
  "create",
  n,
  slug,
  "--title",
  `${config.team_key} · Epic ${n} — Hello board`,
  "--color",
  "#0f3d2e",
]);

const issues = [
  { title: "DB · placeholder schema note", role: "database" },
  {
    title: "BE · health endpoint sketch",
    role: "backend",
    blockedBy: `${config.team_key}-${n}-1`,
  },
  {
    title: "FE · hello page after GATE_HTTP",
    role: "frontend",
    blockedBy: `${config.team_key}-${n}-2`,
  },
  { title: "Orch · journey hygiene", role: "orchestrator" },
];

for (const iss of issues) {
  const args = ["issue", "create", n, "--title", iss.title, "--role", iss.role];
  if (iss.blockedBy) args.push("--blocked-by", iss.blockedBy);
  run(args);
}

run(["progress", "--epic", n]);
console.log("\nOptional demo ready (not required for real work). Try:");
console.log(`  node scripts/board.mjs next --role backend --epic ${n}`);
console.log(
  `  node scripts/board.mjs claim ${config.team_key}-${n}-2 --agent backend-agent-1`
);
console.log("\nFor real work from zero:");
console.log('  node ../../.ai/scripts/epic-new.mjs 1 my-feature --title "My feature"');
