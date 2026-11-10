#!/usr/bin/env node
/**
 * Layer audit: fail when the WordPress plugin tree drifts from native rules.
 *
 * worktree-finish, worktree-integrate and worktree-land-smoke run the same
 * check and refuse on violations.
 *
 *   node .ai/scripts/layer-audit.mjs
 *   node .ai/scripts/layer-audit.mjs --root .worktrees/1-foo/APP-1-2
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { runLayerAudit, formatAudit } from "./lib/layer-audit.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

const argv = process.argv.slice(2);
const i = argv.indexOf("--root");
const root = path.resolve(i === -1 ? SCRIPT_ROOT : argv[i + 1] || SCRIPT_ROOT);
if (!fs.existsSync(path.join(root, ".ai"))) {
  console.error(`No .ai/ under ${root}`);
  process.exit(1);
}

const audit = runLayerAudit(root);
console.log(JSON.stringify({ root, ...audit }, null, 2));
if (!audit.ok) {
  console.error(formatAudit(audit));
  process.exit(1);
}
