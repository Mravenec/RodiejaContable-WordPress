/**
 * Smoke: land-status helper loads and returns a shape.
 *   node scripts/_smoke-land-status.mjs
 */
import assert from "assert";
import path from "path";
import { fileURLToPath } from "url";
import { readLandStatus, hostGitRootFromBoard } from "./lib/land-status.mjs";
import { ROOT } from "./lib/fs-board.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const sample = path.join(ROOT, "epics");
// Engine may have zero epics — still must export and not throw on missing file.
const fake = path.join(sample, "_smoke-missing-epic");
const status = readLandStatus(fake);
assert.ok(status && typeof status === "object");
assert.ok("status" in status);
assert.ok(status.status === null || typeof status.status === "string");
const host = hostGitRootFromBoard();
console.log(
  JSON.stringify(
    { ok: true, host: host || null, sampleStatus: status },
    null,
    2
  )
);
