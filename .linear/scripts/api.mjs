#!/usr/bin/env node
/**
 * Tiny read API for the optional React UI. No cloud.
 *   node scripts/api.mjs
 *   GET /api/epics  GET /api/epic/:ref  GET /api/hours  GET /api/config
 */
import http from "http";
import fs from "fs";
import path from "path";
import {
  loadConfig,
  epicDirs,
  loadEpicMeta,
  listIssuesInEpic,
  progressForIssues,
  findEpic,
  findIssue,
  columns,
  ROOT,
} from "./lib/fs-board.mjs";
import { readClaims } from "./lib/claims.mjs";
import { checklistSummary } from "./lib/checklist.mjs";
import { readClock, clockTotals, currentMonthKey, collectMonthKeys, boardHoursForMonth, landClockTotals } from "./lib/work-clock.mjs";
import { readLandStatus } from "./lib/land-status.mjs";

const PORT = Number(process.env.BOARD_UI_API_PORT || process.env.LINEAR_UI_API_PORT || 47381);

function json(res, code, data) {
  res.writeHead(code, {
    "Content-Type": "application/json",
    "Access-Control-Allow-Origin": "*",
  });
  res.end(JSON.stringify(data));
}

function epicPayload(config, dir) {
  const meta = loadEpicMeta(dir);
  const issues = listIssuesInEpic(config, dir);
  const progress = progressForIssues(config, issues);
  const board = {};
  for (const c of columns(config)) board[c.id] = [];
  for (const i of issues) {
    board[i.column].push({
      id: i.meta.id,
      title: i.meta.title,
      role: i.meta.role,
      agent: i.meta.agent,
      estimate: i.meta.estimate,
      blocked_by: i.meta.blocked_by || [],
      file: i.file,
      checklist: checklistSummary(i.body),
    });
  }
  return {
    meta,
    progress,
    board,
    columns: columns(config),
    dir: path.basename(dir),
    time: clockTotals(readClock(dir, config)),
    landTime: landClockTotals(readClock(dir, config)),
    land: readLandStatus(dir),
  };
}

function issuePayload(config, issue) {
  const summary = checklistSummary(issue.body);
  const claim = readClaims(config).claims[issue.meta.id] || null;
  const historyMatch = issue.body.match(
    /## History\s*\n([\s\S]*?)(?=\n## |\s*$)/i
  );
  const objectiveMatch = issue.body.match(
    /## Objetivo\s*\n([\s\S]*?)(?=\n## |\s*$)/i
  );
  return {
    id: issue.meta.id,
    title: issue.meta.title,
    meta: issue.meta,
    column: issue.column,
    folder: issue.folder,
    epic: issue.epicMeta,
    checklist: summary,
    objective: (objectiveMatch?.[1] || "").trim(),
    history: (historyMatch?.[1] || "").trim(),
    body: issue.body,
    claim,
    file: issue.file,
    time: clockTotals(readClock(issue.epicPath, config)).issues[issue.meta.id] || null,
  };
}

const server = http.createServer((req, res) => {
  if (req.method === "OPTIONS") {
    res.writeHead(204, {
      "Access-Control-Allow-Origin": "*",
      "Access-Control-Allow-Methods": "GET, OPTIONS",
      "Access-Control-Allow-Headers": "Content-Type",
    });
    return res.end();
  }

  try {
    const config = loadConfig();
    const url = new URL(req.url, `http://127.0.0.1:${PORT}`);

    if (url.pathname === "/api/hours") {
      const dirs = epicDirs();
      const rows = dirs.map((dir) => {
        const meta = loadEpicMeta(dir);
        return {
          id: meta.id,
          slug: meta.slug,
          key: `${meta.id}-${meta.slug}`,
          title: meta.title,
          color: meta.color,
          doc: readClock(dir, config),
        };
      });
      const months = collectMonthKeys(rows.map((r) => r.doc));
      const requested = url.searchParams.get("month");
      const month =
        requested && /^\d{4}-\d{2}$/.test(requested)
          ? requested
          : currentMonthKey();
      return json(res, 200, {
        months,
        ...boardHoursForMonth(rows, month),
      });
    }

    if (url.pathname === "/api/config") {
      return json(res, 200, {
        team_key: config.team_key,
        harness: config.harness,
        roles: config.roles,
        columns: columns(config),
      });
    }

    if (url.pathname === "/api/epics") {
      const list = epicDirs().map((dir) => {
        const meta = loadEpicMeta(dir);
        const issues = listIssuesInEpic(config, dir);
        const progress = progressForIssues(config, issues);
        return {
          id: meta.id,
          slug: meta.slug,
          key: `${meta.id}-${meta.slug}`,
          title: meta.title,
          color: meta.color,
          status: meta.status,
          issues: progress.total,
          progress: progress.pct,
          updated_at: meta.updated_at,
          land: readLandStatus(dir),
        };
      });
      list.sort((a, b) => Number(b.id) - Number(a.id));
      return json(res, 200, { epics: list, claims: readClaims(config).claims });
    }

    const ms = url.pathname.match(/^\/api\/epic\/([^/]+)\/summary$/);
    if (ms) {
      const { path: dir, meta } = findEpic(decodeURIComponent(ms[1]));
      const rel =
        meta.summary_html ||
        `summaries/${meta.id}-${meta.slug}-beginner.html`;
      const abs = path.join(dir, rel);
      if (!fs.existsSync(abs)) {
        return json(res, 404, { error: "summary not generated yet" });
      }
      res.writeHead(200, {
        "Content-Type": "text/html; charset=utf-8",
        "Access-Control-Allow-Origin": "*",
      });
      return res.end(fs.readFileSync(abs));
    }
    const m = url.pathname.match(/^\/api\/epic\/([^/]+)$/);
    if (m) {
      const { path: dir } = findEpic(decodeURIComponent(m[1]));
      return json(res, 200, epicPayload(config, dir));
    }

    const mi = url.pathname.match(/^\/api\/issue\/([^/]+)$/);
    if (mi) {
      const issue = findIssue(config, decodeURIComponent(mi[1]));
      return json(res, 200, issuePayload(config, issue));
    }

    if (url.pathname === "/api/health") {
      return json(res, 200, { ok: true, root: ROOT });
    }

    json(res, 404, { error: "not found" });
  } catch (e) {
    json(res, 500, { error: e.message });
  }
});

server.listen(PORT, "127.0.0.1", () => {
  console.error(`private.board API http://127.0.0.1:${PORT}`);
});
