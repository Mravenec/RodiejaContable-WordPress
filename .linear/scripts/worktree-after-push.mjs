#!/usr/bin/env node
/**
 * After the human pushes feature/{n}-{slug} from _epic on the host repo:
 *   - remove the _epic worktree
 *   - delete the local epic branch (commits stay on origin)
 *
 *   node scripts/worktree-after-push.mjs 1 --repo C:\path\to\host
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
import { resolveEpicKeyFromRef } from "./lib/worktree-paths.mjs";
import { gitOk, pruneEpicWorktreeAfterPush } from "./lib/worktree-git.mjs";

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

function run(cmd, args, cwd) {
  return spawnSync(cmd, args, { cwd, encoding: "utf8" });
}

function git(args, cwd, { allowFail = false } = {}) {
  const r = run("git", args, cwd);
  if (r.status !== 0 && !allowFail) {
    process.stderr.write(r.stderr || r.stdout || "git failed\n");
    process.exit(r.status ?? 1);
  }
  return r;
}

function primaryCheckout(cwd) {
  const r = git(["worktree", "list", "--porcelain"], cwd);
  const line = (r.stdout || "")
    .split(/\r?\n/)
    .find((l) => l.startsWith("worktree "));
  if (!line) {
    console.error("Could not read git worktree list.");
    process.exit(1);
  }
  return line.slice("worktree ".length).trim();
}

function looksLikeEngine(dir) {
  return (
    fs.existsSync(path.join(dir, "config.yaml")) &&
    fs.existsSync(path.join(dir, "scripts", "board.mjs")) &&
    !fs.existsSync(path.join(dir, ".linear", "config.yaml"))
  );
}

const argv = process.argv.slice(2);
const repoFlag = flag(argv, "--repo");
const ref = argv.find((a) => a && a !== "--repo" && !String(a).startsWith("--"));

if (!ref) {
  console.error(
    "Usage: node scripts/worktree-after-push.mjs <n|slug|{n}-{slug}> --repo <host-git-root>"
  );
  process.exit(1);
}

const cwd = repoFlag ? path.resolve(String(repoFlag)) : process.cwd();

if (looksLikeEngine(cwd)) {
  console.error(
    "This folder is the board engine. Pass the host git repo:\n" +
      `  node scripts/worktree-after-push.mjs ${ref} --repo <host-git-root>`
  );
  process.exit(1);
}

if (!gitOk(["rev-parse", "--is-inside-work-tree"], cwd)) {
  console.error("Not a git checkout. Pass --repo <host-git-root>.");
  process.exit(1);
}

const primary = primaryCheckout(cwd);
const linear = path.join(primary, ".linear");
if (!fs.existsSync(path.join(linear, "config.yaml"))) {
  console.error(`No .linear/config.yaml under primary checkout:\n  ${primary}`);
  process.exit(1);
}

let epicKey;
try {
  epicKey = resolveEpicKeyFromRef(linear, ref);
} catch (err) {
  console.error(err.message || String(err));
  process.exit(1);
}

const result = pruneEpicWorktreeAfterPush(primary, epicKey);
if (!result.ok && result.error) {
  console.error(result.error);
  process.exit(1);
}
console.log(JSON.stringify({ prunedEpic: true, ...result }, null, 2));
if (!result.ok) process.exit(1);
