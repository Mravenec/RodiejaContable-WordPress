/**
 * Share the primary board into a worktree without ever recurse-deleting it.
 *
 * Windows `mklink /J` junctions look like normal directories to `lstat`
 * (`isSymbolicLink()` is false). `fs.rmSync` / `git worktree remove` then
 * follow the junction and delete the **target** — the only live board.
 *
 * Rule: unlink a junction/symlink with `rmdir` (no /S) or `unlink`.
 * Never `rmSync({ recursive })` on `.linear/epics` or `.linear/agents`.
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";

export const BOARD_SHARE_NAMES = ["epics", "agents"];

/** Windows junctions are reparse points; `lstat.isSymbolicLink()` is false. */
export function isWindowsReparsePoint(dirPath) {
  if (process.platform !== "win32" || !dirPath) return false;
  const r = spawnSync("fsutil", ["reparsepoint", "query", path.resolve(dirPath)], {
    encoding: "utf8",
  });
  return r.status === 0;
}

/**
 * Git tracks `.linear/epics/.gitkeep`. A new worktree therefore has a *real*
 * empty placeholder — not a junction. Safe to replace with a share link.
 * Never treat a junction (would follow and wipe the live board) or a folder
 * that already has epic tickets as a placeholder.
 */
export function isGitPlaceholderShareDir(dirPath) {
  if (!dirPath || !fs.existsSync(dirPath)) return false;
  if (isWindowsReparsePoint(dirPath)) return false;
  const st = fs.lstatSync(dirPath);
  if (st.isSymbolicLink()) return false;
  if (!st.isDirectory()) return false;
  const names = fs.readdirSync(dirPath);
  return names.length === 0 || (names.length === 1 && names[0] === ".gitkeep");
}

function removeGitPlaceholderShareDir(dirPath) {
  const abs = path.resolve(dirPath);
  for (const name of fs.readdirSync(abs)) {
    fs.unlinkSync(path.join(abs, name));
  }
  fs.rmdirSync(abs);
}

/**
 * Remove a junction or symlink only. A real non-empty directory is refused
 * (Windows `rmdir` without /S fails). That is the safety property.
 */
export function unlinkLinkOnly(linkPath) {
  if (!linkPath || !fs.existsSync(linkPath)) return { ok: true, skipped: true };
  const abs = path.resolve(linkPath);
  if (process.platform === "win32") {
    const r = spawnSync("cmd.exe", ["/c", "rmdir", abs], { encoding: "utf8" });
    if (r.status === 0) return { ok: true, abs };
    try {
      if (fs.existsSync(abs) && fs.lstatSync(abs).isSymbolicLink()) {
        fs.unlinkSync(abs);
        return { ok: true, abs };
      }
    } catch {
      return { ok: true, abs };
    }
    if (isGitPlaceholderShareDir(abs)) {
      removeGitPlaceholderShareDir(abs);
      return { ok: true, abs, placeholder: true };
    }
    throw new Error(
      `Refusing to recurse-delete ${abs} (rmdir failed). ` +
        `Unlink the junction only — never delete the primary .linear/epics target.`
    );
  }
  const st = fs.lstatSync(abs);
  if (st.isSymbolicLink()) {
    fs.unlinkSync(abs);
    return { ok: true, abs };
  }
  if (isGitPlaceholderShareDir(abs)) {
    removeGitPlaceholderShareDir(abs);
    return { ok: true, abs, placeholder: true };
  }
  try {
    fs.unlinkSync(abs);
    return { ok: true, abs };
  } catch {
    fs.rmdirSync(abs);
    return { ok: true, abs };
  }
}

export function isBoardSharePath(p) {
  const n = path.basename(p);
  const parent = path.basename(path.dirname(p));
  return parent === ".linear" && BOARD_SHARE_NAMES.includes(n);
}

/** Replace a path: board shares are unlink-only; other trees may recurse. */
export function rmIfExistsSafe(p) {
  if (!p || !fs.existsSync(p)) return;
  if (isBoardSharePath(p)) {
    unlinkLinkOnly(p);
    return;
  }
  const st = fs.lstatSync(p);
  if (st.isSymbolicLink()) {
    fs.unlinkSync(p);
    return;
  }
  fs.rmSync(p, { recursive: true, force: true });
}

export function unlinkSharedBoardLinks(worktreeDir) {
  if (!worktreeDir) return;
  for (const name of BOARD_SHARE_NAMES) {
    unlinkLinkOnly(path.join(worktreeDir, ".linear", name));
  }
}

export function shareDir(linkPath, target) {
  fs.mkdirSync(target, { recursive: true });
  fs.mkdirSync(path.dirname(linkPath), { recursive: true });
  if (fs.existsSync(linkPath)) {
    if (isGitPlaceholderShareDir(linkPath)) {
      removeGitPlaceholderShareDir(linkPath);
    } else {
      unlinkLinkOnly(linkPath);
    }
  }
  if (process.platform === "win32") {
    const r = spawnSync(
      "cmd.exe",
      ["/c", "mklink", "/J", path.resolve(linkPath), path.resolve(target)],
      { encoding: "utf8" }
    );
    if (r.status !== 0) {
      throw new Error(
        r.stderr || r.stdout || "mklink /J failed (directory junction)."
      );
    }
    return;
  }
  fs.symlinkSync(target, linkPath, "dir");
}
