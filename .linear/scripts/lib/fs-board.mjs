import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { parseYaml, stringifyYaml } from "./yaml-lite.mjs";
import { checklistSummary } from "./checklist.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
export const ROOT = path.resolve(__dirname, "../..");

export function loadConfig() {
  const raw = fs.readFileSync(path.join(ROOT, "config.yaml"), "utf8");
  return parseYaml(raw);
}

export function columns(config) {
  return [...(config.board?.columns ?? [])].sort((a, b) => a.order - b.order);
}

export function columnById(config, id) {
  const c = columns(config).find(
    (x) => x.id === id || x.folder === id || x.folder.endsWith(id)
  );
  if (!c) throw new Error(`Column unknown: ${id}`);
  return c;
}

export function resolveColumn(config, name) {
  const n = String(name).toLowerCase().replace(/^\d+\.\s*/, "");
  const cols = columns(config);
  const hit = cols.find(
    (c) =>
      c.id === name ||
      c.id === n ||
      c.folder === name ||
      c.folder.toLowerCase() === String(name).toLowerCase() ||
      c.folder.toLowerCase().endsWith(n)
  );
  if (!hit) {
    throw new Error(
      `Column "${name}" not found. Use: ${cols.map((c) => c.id).join(", ")}`
    );
  }
  return hit;
}

export function splitFrontmatter(text) {
  const m = String(text).match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n?([\s\S]*)$/);
  if (!m) return { meta: {}, body: String(text) };
  return { meta: parseYaml(m[1]), body: m[2] };
}

export function joinFrontmatter(meta, body) {
  const yaml = stringifyYaml(meta);
  const b = body.startsWith("\n") ? body : `\n${body.replace(/^\n/, "")}`;
  return `---\n${yaml}\n---\n${b.startsWith("\n") ? b.slice(1) : b}`;
}

export function epicDirs() {
  const base = path.join(ROOT, "epics");
  if (!fs.existsSync(base)) return [];
  return fs
    .readdirSync(base, { withFileTypes: true })
    .filter((d) => d.isDirectory() && !d.name.startsWith("."))
    .map((d) => path.join(base, d.name));
}

export function loadEpicMeta(epicPath) {
  const metaPath = path.join(epicPath, "meta.yaml");
  if (!fs.existsSync(metaPath)) {
    throw new Error(`Missing meta.yaml in ${epicPath}`);
  }
  return parseYaml(fs.readFileSync(metaPath, "utf8"));
}

export function findEpic(ref) {
  const dirs = epicDirs();
  const s = String(ref);
  for (const dir of dirs) {
    const name = path.basename(dir);
    const meta = loadEpicMeta(dir);
    // Match only against the requested ref. Never use meta.id/slug alone in a
    // folder-shape check (e.g. name === `${meta.id}-${meta.slug}`) — that is
    // always true for every epic folder and broke multi-epic findEpic.
    if (
      name === s ||
      String(meta.id) === s ||
      meta.slug === s ||
      name.startsWith(`${s}-`)
    ) {
      return { path: dir, meta, name };
    }
  }
  throw new Error(`Epic not found: ${ref}`);
}

export function listIssuesInEpic(config, epicPath) {
  const cols = columns(config);
  const issues = [];
  for (const col of cols) {
    const dir = path.join(epicPath, "board", col.folder);
    if (!fs.existsSync(dir)) continue;
    for (const file of fs.readdirSync(dir)) {
      if (!file.endsWith(".md")) continue;
      const full = path.join(dir, file);
      const raw = fs.readFileSync(full, "utf8");
      const { meta, body } = splitFrontmatter(raw);
      issues.push({
        path: full,
        file,
        column: col.id,
        folder: col.folder,
        meta,
        body,
        raw,
      });
    }
  }
  return issues;
}

export function findIssue(config, id) {
  for (const epicPath of epicDirs()) {
    const issues = listIssuesInEpic(config, epicPath);
    const hit = issues.find(
      (i) =>
        i.meta.id === id ||
        i.file.startsWith(`${id}-`) ||
        i.file === `${id}.md`
    );
    if (hit) {
      return {
        ...hit,
        epicPath,
        epicMeta: loadEpicMeta(epicPath),
      };
    }
  }
  throw new Error(`Issue not found: ${id}`);
}

/** True if column order >= unlock_column (default: done). Used for blocked_by. */
export function isWorkComplete(config, columnId) {
  const unlockId = config.progress?.unlock_column || "done";
  const cols = columns(config);
  const unlock = cols.find((c) => c.id === unlockId);
  const cur = cols.find((c) => c.id === columnId);
  if (!unlock || !cur) return columnId === unlockId;
  return cur.order >= unlock.order;
}

/**
 * One ticket’s share of epic % (0..1).
 *
 * Required units = checklist items + column steps to summarize (`maxOrder`).
 * Done units = marked checklist items + current `column.order`.
 * No checklist → columns only (same as the old column-weighted formula).
 */
export function issueProgressWeight(config, issue, maxOrder) {
  if (maxOrder <= 0) return 0;
  const cols = columns(config);
  const col = cols.find((c) => c.id === issue.column);
  const order = Math.min(col?.order ?? 0, maxOrder);
  const { total: checkTotal, done: checkDone } = checklistSummary(
    issue.body || ""
  );
  const unitsNeeded = checkTotal + maxOrder;
  if (unitsNeeded <= 0) return 0;
  const unitsDone = Math.min(checkDone, checkTotal) + order;
  return unitsDone / unitsNeeded;
}

/**
 * Epic progress: column moves **and** marked checklist items.
 *
 * Each issue contributes `(checklist_done + column.order) / (checklist_total + maxOrder)`
 * of its share, where `maxOrder` is the order of `progress.done_column`
 * (default: summarize). Epic pct averages those weights.
 *
 * Example (2 checklist items, orders 0..4): backlog 0/2 → 0%; doing + 1 item
 * → 2/6 ≈ 33% of that ticket; summarize + both items → 100%.
 *
 * **100%** only when every issue is in summarize **and** every checklist is
 * complete (empty checklists still reach 100% at summarize).
 *
 * `done` stays the count of issues already in the final column (for UI).
 */
export function progressForIssues(config, issues) {
  const cols = columns(config);
  const doneId = config.progress?.done_column || "summarize";
  const doneCol = cols.find((c) => c.id === doneId);
  const maxOrder =
    doneCol?.order ??
    (cols.length ? Math.max(...cols.map((c) => c.order)) : 0);
  const total = issues.length;
  const done = issues.filter((i) => i.column === doneId).length;

  let weight = 0;
  for (const i of issues) {
    weight += issueProgressWeight(config, i, maxOrder);
  }

  const pct = total === 0 ? 0 : Math.floor((100 * weight) / total);
  return { total, done, pct, weight };
}

/** Tickets still before `done` (backlog / doing / testing). Summarize waits until this is empty. */
export function issuesOpenBeforeDone(issues) {
  return (issues || []).filter(
    (i) => i.column !== "done" && i.column !== "summarize"
  );
}

/** All issues across every epic (for cross-epic blocked_by). */
export function listAllIssues(config) {
  const out = [];
  for (const epicPath of epicDirs()) {
    const epicMeta = loadEpicMeta(epicPath);
    for (const i of listIssuesInEpic(config, epicPath)) {
      out.push({ ...i, epicPath, epicMeta });
    }
  }
  return out;
}

/**
 * True if any blocked_by dependency is missing or not yet at unlock_column.
 * Dependencies may live in **another epic** (cross-epic wait).
 * @param {object} [issueIndex] optional preloaded list (prefer listAllIssues); if omitted, scans all epics
 */
export function isIssueBlocked(config, issue, issueIndex = null) {
  const deps = issue.meta.blocked_by || [];
  if (!deps.length) return false;
  const pool = issueIndex || listAllIssues(config);
  for (const depId of deps) {
    const dep = pool.find((i) => i.meta.id === depId);
    if (!dep) return true;
    if (!isWorkComplete(config, dep.column)) return true;
  }
  return false;
}

export function appendHistory(body, line) {
  const stamp = new Date().toISOString();
  const entry = `- ${stamp} ${line}`;
  if (/## History\s*\n/i.test(body)) {
    return body.replace(/(## History\s*\n)/i, `$1${entry}\n`);
  }
  return `${body.trimEnd()}\n\n## History\n\n${entry}\n`;
}

export function writeIssueFile(filePath, meta, body) {
  fs.writeFileSync(filePath, joinFrontmatter(meta, body), "utf8");
}

export function slugify(s) {
  return String(s)
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "")
    .slice(0, 48);
}

export function ensureBoardFolders(config, epicPath) {
  for (const col of columns(config)) {
    fs.mkdirSync(path.join(epicPath, "board", col.folder), { recursive: true });
  }
  fs.mkdirSync(path.join(epicPath, "board", "_history"), { recursive: true });
}

export function appendBoardLog(epicPath, event) {
  const logPath = path.join(epicPath, "board", "_history", "log.jsonl");
  fs.mkdirSync(path.dirname(logPath), { recursive: true });
  fs.appendFileSync(
    logPath,
    JSON.stringify({ at: new Date().toISOString(), ...event }) + "\n",
    "utf8"
  );
}

export async function loadHarness(config) {
  const name = config.harness || "neutral";
  const modPath = path.join(ROOT, "harness", `${name}.mjs`);
  if (!fs.existsSync(modPath)) {
    throw new Error(`Harness not found: ${name} (${modPath})`);
  }
  return import(`file://${modPath.replace(/\\/g, "/")}`);
}
