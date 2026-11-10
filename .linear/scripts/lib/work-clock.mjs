/**
 * Active work clock for an epic (and each ticket).
 *
 * The board cannot see Cursor / Claude "Run" / "Approve" dialogs. Instead:
 * - Every board pulse (claim, ping, checklist, move, fail, clock pulse) extends
 *   the open segment if the gap since the last pulse is <= idle_ms.
 * - A longer gap is treated as paused (approval wait, overnight, human away)
 *   and is not added to active time.
 * - Parking in done / summarize / backlog / release / handoff closes the segment.
 * - Explicit: clock pause / clock resume.
 *
 * File: epics/<n>-<slug>/summaries/time.json
 */
import fs from "fs";
import path from "path";

export const DEFAULT_IDLE_MS = 180000;

export function idleMs(config) {
  const env = Number(process.env.BOARD_TIME_IDLE_MS || process.env.LINEAR_TIME_IDLE_MS);
  if (Number.isFinite(env) && env > 0) return env;
  const n = Number(config?.time?.idle_ms);
  return Number.isFinite(n) && n > 0 ? n : DEFAULT_IDLE_MS;
}

export function timeFilePath(epicPath) {
  return path.join(epicPath, "summaries", "time.json");
}

export function emptyClock(config) {
  return {
    idle_ms: idleMs(config),
    started_at: null,
    finished_at: null,
    issues: {},
    land: emptyLandClock(),
  };
}

export function emptyLandClock() {
  return {
    started_at: null,
    finished_at: null,
    paused: false,
    open: null,
    segments: [],
  };
}

export function ensureLandClock(doc) {
  if (!doc.land || typeof doc.land !== "object") doc.land = emptyLandClock();
  if (!Array.isArray(doc.land.segments)) doc.land.segments = [];
  return doc.land;
}

export function readClock(epicPath, config) {
  const p = timeFilePath(epicPath);
  if (!fs.existsSync(p)) return emptyClock(config);
  try {
    const doc = JSON.parse(fs.readFileSync(p, "utf8"));
    if (!doc || typeof doc !== "object") return emptyClock(config);
    if (!doc.issues || typeof doc.issues !== "object") doc.issues = {};
    ensureLandClock(doc);
    return doc;
  } catch {
    return emptyClock(config);
  }
}

export function writeClock(epicPath, doc) {
  const p = timeFilePath(epicPath);
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, JSON.stringify(doc, null, 2) + "\n", "utf8");
}

function ensureIssue(doc, issueId) {
  if (!doc.issues[issueId]) {
    doc.issues[issueId] = {
      active_ms: 0,
      paused: false,
      open: null,
      segments: [],
    };
  }
  return doc.issues[issueId];
}

function closeOpen(rec, reason, endIso) {
  if (!rec.open) return;
  const start = Date.parse(rec.open.start);
  const last = Date.parse(endIso || rec.open.last || rec.open.start);
  const ms = Math.max(0, last - start);
  rec.segments.push({
    start: rec.open.start,
    end: new Date(last).toISOString(),
    ms,
    close: reason || "close",
    agent: rec.open.agent || null,
  });
  rec.active_ms = (rec.active_ms || 0) + ms;
  rec.open = null;
}

export function formatDuration(ms) {
  const n = Math.max(0, Math.round(Number(ms) || 0));
  const s = Math.floor(n / 1000);
  const h = Math.floor(s / 3600);
  const m = Math.floor((s % 3600) / 60);
  const sec = s % 60;
  if (h > 0 && m > 0) return `${h}h ${m}m`;
  if (h > 0) return `${h}h`;
  if (m > 0) return sec ? `${m}m ${sec}s` : `${m}m`;
  return `${sec}s`;
}

function mergeUnionMs(segments) {
  const ranges = segments
    .map((s) => ({
      start: Date.parse(s.start),
      end: Date.parse(s.end),
    }))
    .filter((r) => Number.isFinite(r.start) && Number.isFinite(r.end) && r.end >= r.start)
    .sort((a, b) => a.start - b.start);
  if (!ranges.length) return 0;
  const out = [{ ...ranges[0] }];
  for (let i = 1; i < ranges.length; i++) {
    const cur = ranges[i];
    const last = out[out.length - 1];
    if (cur.start <= last.end) last.end = Math.max(last.end, cur.end);
    else out.push({ ...cur });
  }
  return out.reduce((n, r) => n + (r.end - r.start), 0);
}

function snapshotSegments(doc) {
  const all = [];
  for (const rec of Object.values(doc.issues || {})) {
    for (const s of rec.segments || []) all.push(s);
    if (rec.open) {
      all.push({
        start: rec.open.start,
        end: rec.open.last || rec.open.start,
      });
    }
  }
  return all;
}

export function currentMonthKey(now = new Date()) {
  return now.toISOString().slice(0, 7);
}

export function monthKeyBounds(ym) {
  if (!/^\d{4}-\d{2}$/.test(String(ym || ""))) {
    throw new Error(`Invalid month (use YYYY-MM): ${ym}`);
  }
  const y = Number(ym.slice(0, 4));
  const m = Number(ym.slice(5, 7));
  return { start: Date.UTC(y, m - 1, 1), end: Date.UTC(y, m, 1) };
}

function addMonthKey(set, iso) {
  if (!iso) return;
  const t = Date.parse(iso);
  if (!Number.isFinite(t)) return;
  set.add(new Date(t).toISOString().slice(0, 7));
}

export function collectMonthKeys(docs, now = new Date()) {
  const set = new Set([currentMonthKey(now)]);
  for (const doc of docs || []) {
    addMonthKey(set, doc.started_at);
    addMonthKey(set, doc.finished_at);
    for (const rec of Object.values(doc.issues || {})) {
      for (const s of rec.segments || []) {
        addMonthKey(set, s.start);
        addMonthKey(set, s.end);
      }
      if (rec.open) {
        addMonthKey(set, rec.open.start);
        addMonthKey(set, rec.open.last);
      }
    }
    const land = doc.land || {};
    addMonthKey(set, land.started_at);
    addMonthKey(set, land.finished_at);
    for (const s of land.segments || []) {
      addMonthKey(set, s.start);
      addMonthKey(set, s.end);
    }
    if (land.open) {
      addMonthKey(set, land.open.start);
      addMonthKey(set, land.open.last);
    }
  }
  return [...set].sort().reverse();
}

function lastActivityMs(doc) {
  let lastMs = 0;
  for (const rec of Object.values(doc.issues || {})) {
    for (const s of rec.segments || []) {
      const e = Date.parse(s.end);
      if (e > lastMs) lastMs = e;
    }
    if (rec.open) {
      const e = Date.parse(rec.open.last || rec.open.start);
      if (e > lastMs) lastMs = e;
    }
  }
  return lastMs;
}

function clipMs(start, end, ms0, ms1) {
  if (!Number.isFinite(start) || !Number.isFinite(end) || end < start) return 0;
  const a = Math.max(start, ms0);
  const b = Math.min(end, ms1);
  return b > a ? b - a : 0;
}

/**
 * Active (union of working windows) and elapsed (claim→handoff wall) clipped to YYYY-MM.
 */
export function clockTotalsForMonth(doc, ym) {
  const { start: ms0, end: ms1 } = monthKeyBounds(ym);
  const clipped = [];
  for (const rec of Object.values(doc.issues || {})) {
    for (const s of rec.segments || []) {
      const start = Date.parse(s.start);
      const end = Date.parse(s.end);
      const ms = clipMs(start, end, ms0, ms1);
      if (ms > 0) {
        clipped.push({
          start: new Date(Math.max(start, ms0)).toISOString(),
          end: new Date(Math.min(end, ms1)).toISOString(),
        });
      }
    }
    if (rec.open) {
      const start = Date.parse(rec.open.start);
      const end = Date.parse(rec.open.last || rec.open.start);
      const ms = clipMs(start, end, ms0, ms1);
      if (ms > 0) {
        clipped.push({
          start: new Date(Math.max(start, ms0)).toISOString(),
          end: new Date(Math.min(end, ms1)).toISOString(),
        });
      }
    }
  }
  const active = mergeUnionMs(clipped);
  const land = doc.land || emptyLandClock();
  for (const s of land.segments || []) {
    const start = Date.parse(s.start);
    const end = Date.parse(s.end);
    const ms = clipMs(start, end, ms0, ms1);
    if (ms > 0) {
      clipped.push({
        start: new Date(Math.max(start, ms0)).toISOString(),
        end: new Date(Math.min(end, ms1)).toISOString(),
      });
    }
  }
  if (land.open) {
    const start = Date.parse(land.open.start);
    const end = Date.parse(land.open.last || land.open.start);
    const ms = clipMs(start, end, ms0, ms1);
    if (ms > 0) {
      clipped.push({
        start: new Date(Math.max(start, ms0)).toISOString(),
        end: new Date(Math.min(end, ms1)).toISOString(),
      });
    }
  }
  const activeWithLand = mergeUnionMs(clipped);
  const started = doc.started_at ? Date.parse(doc.started_at) : 0;
  const ended = doc.finished_at
    ? Date.parse(doc.finished_at)
    : lastActivityMs(doc) || started;
  const wall = started ? clipMs(started, ended || started, ms0, ms1) : 0;
  const landStarted = land.started_at ? Date.parse(land.started_at) : 0;
  const landEnded = land.finished_at
    ? Date.parse(land.finished_at)
    : land.open
      ? Date.parse(land.open.last || land.open.start)
      : landStarted;
  const land_wall_ms = landStarted
    ? clipMs(landStarted, landEnded || landStarted, ms0, ms1)
    : 0;
  const landOnly = [];
  for (const s of land.segments || []) {
    const start = Date.parse(s.start);
    const end = Date.parse(s.end);
    const ms = clipMs(start, end, ms0, ms1);
    if (ms > 0) {
      landOnly.push({
        start: new Date(Math.max(start, ms0)).toISOString(),
        end: new Date(Math.min(end, ms1)).toISOString(),
      });
    }
  }
  if (land.open) {
    const start = Date.parse(land.open.start);
    const end = Date.parse(land.open.last || land.open.start);
    const ms = clipMs(start, end, ms0, ms1);
    if (ms > 0) {
      landOnly.push({
        start: new Date(Math.max(start, ms0)).toISOString(),
        end: new Date(Math.min(end, ms1)).toISOString(),
      });
    }
  }
  const land_active_ms = mergeUnionMs(landOnly);
  return {
    active_ms: activeWithLand,
    active_label: formatDuration(activeWithLand),
    wall_ms: wall + land_wall_ms,
    wall_label: formatDuration(wall + land_wall_ms),
    build_active_ms: active,
    build_wall_ms: wall,
    land_active_ms,
    land_active_label: formatDuration(land_active_ms),
    land_wall_ms,
    land_wall_label: formatDuration(land_wall_ms),
  };
}

/**
 * Sum of each epic's month-clipped active and elapsed (all epics on the board).
 */
export function boardHoursForMonth(rows, ym) {
  const epics = [];
  let activeSum = 0;
  let wallSum = 0;
  let landActiveSum = 0;
  let landWallSum = 0;
  for (const row of rows || []) {
    const t = clockTotalsForMonth(row.doc || { issues: {} }, ym);
    if (!t.active_ms && !t.wall_ms && !t.land_active_ms && !t.land_wall_ms) {
      continue;
    }
    epics.push({
      id: row.id,
      slug: row.slug,
      key: row.key,
      title: row.title,
      color: row.color,
      active_ms: t.active_ms,
      active_label: t.active_label,
      wall_ms: t.wall_ms,
      wall_label: t.wall_label,
      land_active_ms: t.land_active_ms,
      land_active_label: t.land_active_label,
      land_wall_ms: t.land_wall_ms,
      land_wall_label: t.land_wall_label,
    });
    activeSum += t.active_ms;
    wallSum += t.wall_ms;
    landActiveSum += t.land_active_ms || 0;
    landWallSum += t.land_wall_ms || 0;
  }
  return {
    month: ym,
    active_ms: activeSum,
    active_label: formatDuration(activeSum),
    wall_ms: wallSum,
    wall_label: formatDuration(wallSum),
    land_active_ms: landActiveSum,
    land_active_label: formatDuration(landActiveSum),
    land_wall_ms: landWallSum,
    land_wall_label: formatDuration(landWallSum),
    epics,
  };
}

export function clockTotals(doc) {
  const issues = {};
  let effort = 0;
  let lastMs = 0;
  for (const [id, rec] of Object.entries(doc.issues || {})) {
    let active = rec.active_ms || 0;
    if (rec.open) {
      const start = Date.parse(rec.open.start);
      const last = Date.parse(rec.open.last || rec.open.start);
      active += Math.max(0, last - start);
    }
    issues[id] = {
      active_ms: active,
      active_label: formatDuration(active),
      paused: Boolean(rec.paused),
      running: Boolean(rec.open),
      segments: (rec.segments || []).length + (rec.open ? 1 : 0),
    };
    effort += active;
    for (const s of rec.segments || []) {
      const e = Date.parse(s.end);
      if (e > lastMs) lastMs = e;
    }
    if (rec.open) {
      const e = Date.parse(rec.open.last || rec.open.start);
      if (e > lastMs) lastMs = e;
    }
  }
  const started = doc.started_at ? Date.parse(doc.started_at) : 0;
  const ended = doc.finished_at
    ? Date.parse(doc.finished_at)
    : lastMs || started;
  const wall = started && ended && ended >= started ? ended - started : 0;
  const active = mergeUnionMs(snapshotSegments(doc));
  return {
    started_at: doc.started_at || null,
    finished_at: doc.finished_at || null,
    idle_ms: doc.idle_ms || DEFAULT_IDLE_MS,
    active_ms: active,
    active_label: formatDuration(active),
    effort_ms: effort,
    effort_label: formatDuration(effort),
    wall_ms: wall,
    wall_label: formatDuration(wall),
    issues,
  };
}

export function pulseClock(config, epicPath, issueId, { agent, event } = {}) {
  if (!issueId) return clockTotals(emptyClock(config));
  const doc = readClock(epicPath, config);
  doc.idle_ms = idleMs(config);
  const nowIso = new Date().toISOString();
  const now = Date.parse(nowIso);
  if (!doc.started_at) doc.started_at = nowIso;
  doc.finished_at = null;
  const rec = ensureIssue(doc, issueId);
  rec.paused = false;
  const idle = idleMs(config);
  if (!rec.open) {
    rec.open = { start: nowIso, last: nowIso, agent: agent || null, event: event || "pulse" };
  } else {
    const last = Date.parse(rec.open.last || rec.open.start);
    if (now - last <= idle) {
      rec.open.last = nowIso;
      rec.open.agent = agent || rec.open.agent;
      rec.open.event = event || rec.open.event;
    } else {
      closeOpen(rec, "idle", rec.open.last);
      rec.open = { start: nowIso, last: nowIso, agent: agent || null, event: event || "pulse" };
    }
  }
  writeClock(epicPath, doc);
  return clockTotals(doc);
}

export function pauseClock(config, epicPath, issueId, reason) {
  const doc = readClock(epicPath, config);
  const rec = ensureIssue(doc, issueId);
  closeOpen(rec, reason || "pause", rec.open?.last);
  rec.paused = true;
  writeClock(epicPath, doc);
  return clockTotals(doc);
}

export function closeClock(config, epicPath, issueId, reason) {
  const doc = readClock(epicPath, config);
  const rec = ensureIssue(doc, issueId);
  rec.paused = false;
  closeOpen(rec, reason || "close", rec.open?.last);
  writeClock(epicPath, doc);
  return clockTotals(doc);
}

export function finishEpicClock(config, epicPath) {
  const doc = readClock(epicPath, config);
  const nowIso = new Date().toISOString();
  const now = Date.parse(nowIso);
  const idle = idleMs(config);
  for (const rec of Object.values(doc.issues || {})) {
    if (!rec.open) continue;
    const last = Date.parse(rec.open.last || rec.open.start);
    if (now - last <= idle) rec.open.last = nowIso;
    closeOpen(rec, "handoff", rec.open.last);
    rec.paused = false;
  }
  if (!doc.started_at) doc.started_at = nowIso;
  doc.finished_at = nowIso;
  doc.idle_ms = idle;
  writeClock(epicPath, doc);
  return clockTotals(doc);
}

function closeLandOpen(land, reason, endIso) {
  if (!land.open) return;
  const start = Date.parse(land.open.start);
  const last = Date.parse(endIso || land.open.last || land.open.start);
  const ms = Math.max(0, last - start);
  land.segments.push({
    start: land.open.start,
    end: new Date(last).toISOString(),
    ms,
    close: reason || "close",
    agent: land.open.agent || null,
  });
  land.open = null;
}

export function landClockTotals(doc) {
  const land = doc?.land || emptyLandClock();
  let active = 0;
  for (const s of land.segments || []) active += s.ms || 0;
  if (land.open) {
    const start = Date.parse(land.open.start);
    const last = Date.parse(land.open.last || land.open.start);
    active += Math.max(0, last - start);
  }
  const started = land.started_at ? Date.parse(land.started_at) : 0;
  const ended = land.finished_at
    ? Date.parse(land.finished_at)
    : land.open
      ? Date.parse(land.open.last || land.open.start)
      : started;
  const wall = started && ended && ended >= started ? ended - started : 0;
  return {
    started_at: land.started_at || null,
    finished_at: land.finished_at || null,
    active_ms: active,
    active_label: formatDuration(active),
    wall_ms: wall,
    wall_label: formatDuration(wall),
    running: Boolean(land.open),
    paused: Boolean(land.paused),
  };
}

/** Start or pulse the land-phase clock for an epic. */
export function pulseLandClock(config, epicPath, { agent, event } = {}) {
  const doc = readClock(epicPath, config);
  doc.idle_ms = idleMs(config);
  const land = ensureLandClock(doc);
  const nowIso = new Date().toISOString();
  const now = Date.parse(nowIso);
  if (!land.started_at) land.started_at = nowIso;
  land.finished_at = null;
  land.paused = false;
  const idle = idleMs(config);
  if (!land.open) {
    land.open = {
      start: nowIso,
      last: nowIso,
      agent: agent || null,
      event: event || "land",
    };
  } else {
    const last = Date.parse(land.open.last || land.open.start);
    if (now - last <= idle) {
      land.open.last = nowIso;
      land.open.agent = agent || land.open.agent;
      land.open.event = event || land.open.event;
    } else {
      closeLandOpen(land, "idle", land.open.last);
      land.open = {
        start: nowIso,
        last: nowIso,
        agent: agent || null,
        event: event || "land",
      };
    }
  }
  writeClock(epicPath, doc);
  return landClockTotals(doc);
}

export function finishLandClock(config, epicPath) {
  const doc = readClock(epicPath, config);
  const land = ensureLandClock(doc);
  const nowIso = new Date().toISOString();
  const now = Date.parse(nowIso);
  const idle = idleMs(config);
  if (land.open) {
    const last = Date.parse(land.open.last || land.open.start);
    if (now - last <= idle) land.open.last = nowIso;
    closeLandOpen(land, "land-finish", land.open.last);
  }
  land.paused = false;
  if (!land.started_at) land.started_at = nowIso;
  land.finished_at = nowIso;
  writeClock(epicPath, doc);
  return landClockTotals(doc);
}
