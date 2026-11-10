/**
 * Land hygiene: LANDED must not leave epic % stuck below 100%.
 * Epic % reaches 100% only when every ticket is in `summarize` with checklists complete.
 * Feature-branch / restore snapshots often park tickets in `done` — promote before LANDED snap.
 */
import fs from "fs";
import path from "path";
import {
  appendHistory,
  columns,
  listIssuesInEpic,
  progressForIssues,
  writeIssueFile,
} from "./fs-board.mjs";

/**
 * Promote parked `done` tickets into `summarize`, complete checklists, refuse open work.
 * @returns {{ ok: boolean, pct: number, promoted: string[], open: string[], error?: string }}
 */
export function ensureEpicTicketsSummarized(config, epicPath) {
  const cols = columns(config);
  const summarize = cols.find((c) => c.id === "summarize");
  if (!summarize) {
    return {
      ok: false,
      pct: 0,
      promoted: [],
      open: [],
      error: "Board config missing summarize column",
    };
  }

  const promoted = [];
  const open = [];

  for (const issue of listIssuesInEpic(config, epicPath)) {
    let body = String(issue.body || "").replace(/- \[ \]/g, "- [x]");

    if (issue.column === "summarize") {
      if (body !== issue.body) {
        writeIssueFile(issue.path, issue.meta, body);
      }
      continue;
    }

    if (issue.column === "done") {
      body = appendHistory(
        body,
        "moved done → summarize (land hygiene — LANDED requires 100%)"
      );
      const destDir = path.join(epicPath, "board", summarize.folder);
      fs.mkdirSync(destDir, { recursive: true });
      const dest = path.join(destDir, issue.file);
      if (issue.path !== dest) {
        fs.renameSync(issue.path, dest);
      }
      writeIssueFile(dest, issue.meta, body);
      promoted.push(String(issue.meta.id));
      continue;
    }

    open.push(`${issue.meta.id}@${issue.column}`);
  }

  if (open.length) {
    return {
      ok: false,
      pct: 0,
      promoted,
      open,
      error: `Refuse LANDED while tickets are still open (not done/summarize): ${open.join(", ")}. Finish → summarize first.`,
    };
  }

  const issues = listIssuesInEpic(config, epicPath);
  const prog = progressForIssues(config, issues);
  if (prog.pct < 100) {
    return {
      ok: false,
      pct: prog.pct,
      promoted,
      open: [],
      error: `Epic board is ${prog.pct}% after hygiene (need 100% = all tickets in summarize with checklists complete).`,
    };
  }

  return { ok: true, pct: prog.pct, promoted, open: [] };
}
