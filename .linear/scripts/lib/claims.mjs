import fs from "fs";
import path from "path";
import { ROOT } from "./fs-board.mjs";

export function claimsPath(config) {
  return path.join(ROOT, config.claims_file || "agents/claims.json");
}

export function readClaims(config) {
  const p = claimsPath(config);
  try {
    return JSON.parse(fs.readFileSync(p, "utf8"));
  } catch {
    return { claims: {} };
  }
}

export function writeClaims(config, reg) {
  const p = claimsPath(config);
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, JSON.stringify(reg, null, 2) + "\n", "utf8");
}

export function isClaimStale(entry, ttlMs) {
  if (!entry?.lastHeartbeat) return true;
  return Date.now() - new Date(entry.lastHeartbeat).getTime() > ttlMs;
}

export function getActiveClaim(config, issueId) {
  const reg = readClaims(config);
  const claim = reg.claims[issueId];
  if (!claim) return null;
  const ttl = config.heartbeat_ttl_ms || 300000;
  if (isClaimStale(claim, ttl)) return null;
  return claim;
}

export function assertClaimOwner(config, issueId, agentId, { force = false } = {}) {
  if (force) return;
  const claim = getActiveClaim(config, issueId);
  if (!claim) {
    throw new Error(
      `TOOL GATE: no active claim on ${issueId}. Use claim first (or --force).`
    );
  }
  if (agentId && claim.agentId !== agentId) {
    throw new Error(
      `TOOL GATE: ${issueId} claimed by "${claim.agentId}", not "${agentId}".`
    );
  }
}
