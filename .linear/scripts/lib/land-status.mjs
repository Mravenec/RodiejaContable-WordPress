/**
 * Land badge status for the board UI API.
 * Prefer summaries/land.json; when the board is vendored at <host>/.linear,
 * also infer "landed" if origin/feature/{epicKey} is already on origin/main
 * (same idea as host harness landStatusForEpic) so day-0 users never see
 * "No updates" after a real land.
 */
import fs from "fs";
import path from "path";
import { spawnSync } from "child_process";
import { ROOT } from "./fs-board.mjs";

function gitSpawn(args, cwd) {
  return spawnSync("git", args, {
    cwd,
    encoding: "utf8",
    timeout: 8000,
    env: { ...process.env, GIT_TERMINAL_PROMPT: "0" },
  });
}

function gitOk(args, cwd) {
  return gitSpawn(args, cwd).status === 0;
}

/**
 * Host git root when this board is <host>/.linear (not the standalone engine).
 */
export function hostGitRootFromBoard(root = ROOT) {
  const parent = path.resolve(root, "..");
  const linearAtParent = path.join(parent, ".linear");
  if (
    fs.existsSync(path.join(root, "config.yaml")) &&
    fs.existsSync(path.join(linearAtParent, "config.yaml")) &&
    path.resolve(linearAtParent) === path.resolve(root) &&
    gitOk(["rev-parse", "--is-inside-work-tree"], parent)
  ) {
    return parent;
  }
  return null;
}

function remoteHeadsHas(host, branch) {
  // Local remote-tracking ref. `git ls-remote` on every 2s UI poll blocks the
  // API (one synchronous GitHub call per epic) until the page looks down.
  return gitOk(
    ["show-ref", "--verify", "--quiet", `refs/remotes/origin/${branch}`],
    host
  );
}

function featureBranchName(epicKey) {
  return `feature/${epicKey}`;
}

/**
 * @param {string} epicDir absolute path to .linear/epics/{n}-{slug}
 * @returns {{ status: string|null, merge_commit: string|null, smoke_ok: boolean|null, inferred?: boolean }}
 */
export function readLandStatus(epicDir) {
  const epicKey = path.basename(epicDir);
  const p = path.join(epicDir, "summaries", "land.json");
  let doc = null;
  if (fs.existsSync(p)) {
    try {
      doc = JSON.parse(fs.readFileSync(p, "utf8"));
    } catch {
      doc = null;
    }
  }

  const fileStatus = doc?.status || null;
  const base = {
    status: fileStatus,
    merge_commit: doc?.merge_commit ?? null,
    smoke_ok: doc?.smoke_ok ?? null,
  };

  const host = hostGitRootFromBoard();
  const br = featureBranchName(epicKey);

  // Truth source for LANDED: origin/main contains origin/feature/{epicKey}.
  // Never trust a hand-written land.json "landed" without GitHub evidence.
  if (host) {
    const onOrigin = remoteHeadsHas(host, br);
    const onMain =
      onOrigin &&
      remoteHeadsHas(host, "main") &&
      gitOk(["merge-base", "--is-ancestor", `origin/${br}`, "origin/main"], host);

    if (onMain) {
      return {
        status: "landed",
        merge_commit: base.merge_commit,
        smoke_ok: base.smoke_ok ?? true,
        inferred: fileStatus !== "landed",
      };
    }

    // File claimed landed but origin/main does not have the feature → demote.
    if (fileStatus === "landed") {
      return {
        status: onOrigin ? "on-origin" : "smoke-main",
        merge_commit: base.merge_commit,
        smoke_ok: base.smoke_ok,
        demoted: true,
        reason: "land.json said landed but origin/main lacks feature branch",
      };
    }

    if (fileStatus === "landing" || fileStatus === "smoke-main") {
      return base;
    }

    if (fileStatus === "on-origin" || onOrigin) {
      return {
        status: "on-origin",
        merge_commit: base.merge_commit,
        smoke_ok: base.smoke_ok,
      };
    }

    return { status: null, merge_commit: null, smoke_ok: null };
  }

  // Standalone board engine (no host git): file only; still refuse bare "landed"
  // without merge_commit (agents must not invent LANDED).
  if (fileStatus === "landed" && !base.merge_commit) {
    return { status: null, merge_commit: null, smoke_ok: null, demoted: true };
  }
  if (
    fileStatus === "landed" ||
    fileStatus === "landing" ||
    fileStatus === "smoke-main" ||
    fileStatus === "on-origin"
  ) {
    return base;
  }
  return { status: null, merge_commit: null, smoke_ok: null };
}
