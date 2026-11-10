/**
 * Shared naming for host git worktrees (ticket vs epic integration).
 * Epic key matches journey + board folder: {n}-{slug}
 */
import fs from "fs";
import path from "path";

export const ISSUE_ID_RE = /^[A-Za-z][A-Za-z0-9]*-\d+-\d+$/;

export function parseIssueId(id) {
  const m = String(id).match(/^([A-Za-z][A-Za-z0-9]*)-(\d+)-(\d+)$/);
  if (!m) return null;
  return { team: m[1], epic: Number(m[2]), seq: Number(m[3]) };
}

export function resolveEpicKey(linearRoot, issueId) {
  const parsed = parseIssueId(issueId);
  if (!parsed) {
    throw new Error(`ISSUE_ID must look like APP-1-2, got: ${issueId}`);
  }
  const epicsRoot = path.join(linearRoot, "epics");
  if (!fs.existsSync(epicsRoot)) {
    throw new Error(`No epics folder:\n  ${epicsRoot}`);
  }
  const dirs = fs.readdirSync(epicsRoot).filter((name) => {
    const full = path.join(epicsRoot, name);
    if (!fs.statSync(full).isDirectory() || name.startsWith(".")) return false;
    return name.split("-")[0] === String(parsed.epic);
  });
  if (dirs.length !== 1) {
    throw new Error(
      `Cannot resolve epic folder for ${issueId} (epic ${parsed.epic}). Found: ${
        dirs.join(", ") || "none"
      }`
    );
  }
  return dirs[0];
}

export function listEpicKeys(linearRoot) {
  const epicsRoot = path.join(linearRoot, "epics");
  if (!fs.existsSync(epicsRoot)) return [];
  return fs
    .readdirSync(epicsRoot)
    .filter((name) => {
      const full = path.join(epicsRoot, name);
      return (
        fs.statSync(full).isDirectory() &&
        !name.startsWith(".") &&
        /^\d+-/.test(name)
      );
    })
    .sort((a, b) => {
      const na = Number(a.split("-")[0]);
      const nb = Number(b.split("-")[0]);
      return na - nb || a.localeCompare(b);
    });
}

export function resolveEpicKeyFromRef(linearRoot, ref) {
  const epicsRoot = path.join(linearRoot, "epics");
  if (!fs.existsSync(epicsRoot)) {
    throw new Error(`No epics folder:\n  ${epicsRoot}`);
  }
  const dirs = fs.readdirSync(epicsRoot).filter((name) => {
    const full = path.join(epicsRoot, name);
    return fs.statSync(full).isDirectory() && !name.startsWith(".");
  });
  const hit = dirs.find(
    (name) =>
      name === String(ref) ||
      name.split("-")[0] === String(ref) ||
      name.slice(name.indexOf("-") + 1) === String(ref)
  );
  if (!hit) {
    throw new Error(`No epic matching "${ref}" under ${epicsRoot}`);
  }
  return hit;
}

export function epicBranchName(epicKey) {
  return `feature/${epicKey}`;
}

/**
 * Ticket branches must NOT nest under `feature/{n}-{slug}/…`.
 * Git cannot store both `feature/1-foo` (epic) and `feature/1-foo/APP-1-1`
 * (ticket) — one ref would be a file and a directory. Tickets use `ticket/`.
 */
export function ticketBranchName(epicKey, issueId) {
  return `ticket/${epicKey}/${issueId}`;
}

/** Current name plus older layouts, for integrate / start-point lookup. */
export function ticketBranchAliases(epicKey, issueId) {
  return [
    ticketBranchName(epicKey, issueId),
    `feature/${epicKey}/${issueId}`,
    `feature/${issueId}`,
  ];
}

export function resolveExistingTicketBranch(gitOk, primary, epicKey, issueId) {
  for (const name of ticketBranchAliases(epicKey, issueId)) {
    if (gitOk(["show-ref", "--verify", "--quiet", `refs/heads/${name}`], primary)) {
      return name;
    }
  }
  return null;
}

export function ticketWorktreeDir(primary, epicKey, issueId) {
  return path.join(primary, ".worktrees", epicKey, issueId);
}

export const PARKED_COLUMNS = new Set(["done", "summarize"]);

/** Ticket trees live under `.worktrees/{epicKey}/{ISSUE_ID}`. That parent is a
 *  plain folder, not a git worktree — git cannot nest a worktree inside another.
 *  The epic integration checkout is therefore a sibling: `_epic`. */
export function epicWorktreeDir(primary, epicKey) {
  return path.join(primary, ".worktrees", epicKey, "_epic");
}

export function findTicketWorktreeDir(primary, linearRoot, issueId) {
  let epicKey = null;
  try {
    epicKey = resolveEpicKey(linearRoot, issueId);
  } catch {
    epicKey = null;
  }
  if (epicKey) {
    const nested = ticketWorktreeDir(primary, epicKey, issueId);
    if (fs.existsSync(nested)) {
      return { dir: nested, epicKey, branch: ticketBranchName(epicKey, issueId) };
    }
  }
  const legacy = path.join(primary, ".worktrees", issueId);
  if (fs.existsSync(legacy)) {
    return {
      dir: legacy,
      epicKey,
      branch: `feature/${issueId}`,
      legacy: true,
    };
  }
  return null;
}
