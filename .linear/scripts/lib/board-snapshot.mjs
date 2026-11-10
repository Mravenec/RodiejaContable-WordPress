/**
 * Tracked recovery copy of a live epic board.
 * Live tickets stay in gitignored .linear/epics folders.
 * This copy lives under host .docs/board/{n}-{slug}/ so git can restore them.
 */
import fs from "fs";
import path from "path";

export function boardSnapshotDir(hostRoot, epicKey) {
  return path.join(hostRoot, ".docs", "board", epicKey);
}

function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  for (const name of fs.readdirSync(src)) {
    if (name === "node_modules" || name === ".git") continue;
    const from = path.join(src, name);
    const to = path.join(dest, name);
    const st = fs.lstatSync(from);
    if (st.isSymbolicLink() || st.isDirectory()) {
      // Do not follow junctions. Copy a real directory tree; skip links.
      if (st.isSymbolicLink()) continue;
      copyDir(from, to);
    } else {
      fs.copyFileSync(from, to);
    }
  }
}

export function snapshotEpicToDocs(epicPath, hostRoot) {
  if (!epicPath || !hostRoot || !fs.existsSync(epicPath)) {
    return { ok: false, reason: "missing epic or host" };
  }
  const epicKey = path.basename(epicPath);
  const dest = boardSnapshotDir(hostRoot, epicKey);
  fs.rmSync(dest, { recursive: true, force: true });
  copyDir(epicPath, dest);
  return { ok: true, dest, epicKey };
}

export function restoreEpicFromDocs(hostRoot, epicKey, linearRoot) {
  const src = boardSnapshotDir(hostRoot, epicKey);
  if (!fs.existsSync(src)) {
    return { ok: false, reason: `No snapshot at ${src}` };
  }
  const dest = path.join(linearRoot, "epics", epicKey);
  fs.mkdirSync(path.join(linearRoot, "epics"), { recursive: true });
  fs.rmSync(dest, { recursive: true, force: true });
  copyDir(src, dest);
  return { ok: true, dest, epicKey };
}

export function listBoardSnapshots(hostRoot) {
  const root = path.join(hostRoot, ".docs", "board");
  if (!fs.existsSync(root)) return [];
  return fs
    .readdirSync(root, { withFileTypes: true })
    .filter((d) => d.isDirectory() && d.name !== ".")
    .map((d) => d.name);
}
