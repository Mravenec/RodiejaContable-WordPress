/**
 * Smoke: unlinking a Windows junction (or posix symlink) must not delete the target.
 * Run: node scripts/_smoke-board-share.mjs
 */
import fs from "fs";
import os from "os";
import path from "path";
import { shareDir, unlinkLinkOnly } from "./lib/board-share.mjs";

const tmp = fs.mkdtempSync(path.join(os.tmpdir(), "board-share-"));
const target = path.join(tmp, "epics");
const linkParent = path.join(tmp, "worktree", ".linear");
const link = path.join(linkParent, "epics");
const canary = path.join(target, "CANARY.txt");

try {
  fs.mkdirSync(target, { recursive: true });
  fs.writeFileSync(canary, "keep-me\n", "utf8");
  shareDir(link, target);
  if (!fs.existsSync(path.join(link, "CANARY.txt"))) {
    throw new Error("junction/symlink did not show the canary");
  }
  unlinkLinkOnly(link);
  if (fs.existsSync(link)) {
    throw new Error("link still exists after unlinkLinkOnly");
  }
  if (!fs.existsSync(canary) || fs.readFileSync(canary, "utf8") !== "keep-me\n") {
    throw new Error("FAIL: target was deleted — junction wipe is back");
  }
  console.log("OK: unlinkLinkOnly removed the share and left the board target intact");
} finally {
  fs.rmSync(tmp, { recursive: true, force: true });
}
