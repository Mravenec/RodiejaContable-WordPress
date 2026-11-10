#!/usr/bin/env node
/**
 * private.board CLI — filesystem only, no cloud issue-tracker API/MCP.
 *
 * Usage: node scripts/board.mjs <command> [args]
 */
import fs from "fs";
import path from "path";
import {
  ROOT,
  loadConfig,
  columns,
  resolveColumn,
  epicDirs,
  loadEpicMeta,
  findEpic,
  listIssuesInEpic,
  listAllIssues,
  findIssue,
  progressForIssues,
  issuesOpenBeforeDone,
  isIssueBlocked,
  appendHistory,
  writeIssueFile,
  slugify,
  ensureBoardFolders,
  appendBoardLog,
  loadHarness,
  splitFrontmatter,
  joinFrontmatter,
} from "./lib/fs-board.mjs";
import {
  checklistSummary,
  markChecklistInDescription,
  parseChecklistSpec,
  validateSequentialChecklistMark,
} from "./lib/checklist.mjs";
import {
  readClaims,
  writeClaims,
  getActiveClaim,
  isClaimStale,
  assertClaimOwner,
} from "./lib/claims.mjs";
import { stringifyYaml } from "./lib/yaml-lite.mjs";
import { writeEpicSummary, writeHandoffFile, defaultEpicGitCommand, readHandoff } from "./lib/summarize.mjs";
import { snapshotEpicToDocs, restoreEpicFromDocs, listBoardSnapshots } from "./lib/board-snapshot.mjs";
import { findTicketWorktreeDir } from "./lib/worktree-paths.mjs";
import { isDirty } from "./lib/worktree-git.mjs";
import {
  formatEvidenceEntry,
  insertEvidence,
  hasEvidenceFences,
  fenceCount,
} from "./lib/evidence.mjs";
import {
  pulseClock,
  pauseClock,
  closeClock,
  finishEpicClock,
  readClock,
  clockTotals,
  currentMonthKey,
  collectMonthKeys,
  boardHoursForMonth,
} from "./lib/work-clock.mjs";

function args() {
  return process.argv.slice(2);
}

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

function has(argv, name) {
  return argv.includes(name);
}

/** Host git root when this board is <host>/.linear (not the engine folder). */
function hostGitRootFromBoard() {
  const parent = path.resolve(ROOT, "..");
  const linearAtParent = path.join(parent, ".linear");
  if (
    fs.existsSync(path.join(ROOT, "config.yaml")) &&
    fs.existsSync(path.join(linearAtParent, "config.yaml")) &&
    path.resolve(linearAtParent) === ROOT
  ) {
    return parent;
  }
  return null;
}

/**
 * Parked tickets unlock dependents. A dirty worktree means the next
 * worktree-add would start without those files.
 */
function assertTicketWorktreeCommitted(id, force) {
  if (force) return;
  const host = hostGitRootFromBoard();
  if (!host) return;
  const found = findTicketWorktreeDir(host, ROOT, id);
  if (!found?.dir || !fs.existsSync(found.dir)) return;
  if (!isDirty(found.dir)) return;
  throw new Error(
    `${id} still has uncommitted files in:\n  ${found.dir}\n` +
      `Commit then park: node .ai/scripts/worktree-finish.mjs ${id} --agent <name>\n` +
      `(Do not unlock dependents on a dirty ticket branch.)`
  );
}

function usage() {
  console.log(`private.board — board CLI

  node scripts/board.mjs epics
  node scripts/board.mjs epic create <n> <slug> [--title "..."]
  node scripts/board.mjs epic show <n|slug>
  node scripts/board.mjs issue create <epic> --title "..." --role backend [--blocks ID]
  node scripts/board.mjs list [--epic N] [--role R] [--column C]
  node scripts/board.mjs next --role R [--epic N]
  node scripts/board.mjs claim <id> --agent NAME
  node scripts/board.mjs ping <id> --agent NAME
  node scripts/board.mjs clock pulse <id> [--agent NAME]
  node scripts/board.mjs clock pause <id> --reason "waiting for approval"
  node scripts/board.mjs clock resume <id> [--agent NAME]
  node scripts/board.mjs clock status [id|n|slug]
  node scripts/board.mjs hours [--month YYYY-MM]
  node scripts/board.mjs release <id> --agent NAME [--reason "..."]
  node scripts/board.mjs fail <id> --agent NAME --reason "..."
  node scripts/board.mjs move <id> <column> [--agent NAME] [--force] [--reason "..."]
  node scripts/board.mjs summarize <id> [--agent NAME] [--force]
  node scripts/board.mjs summarize-epic <n|slug> [--force]
  node scripts/board.mjs handoff <n|slug> --git "..." [--note-file path | --note "..."]
  node scripts/board.mjs checklist <id> <n> [--agent NAME] [--force]
  node scripts/board.mjs evidence <id> --title SQL [--path file] [--lang sql] [--code-file f]
  node scripts/board.mjs checklist-status <id>
  node scripts/board.mjs progress [--epic N]
  node scripts/board.mjs history <id>
  node scripts/board.mjs watchdog
  node scripts/board.mjs refresh-epic <n|slug>
  node scripts/board.mjs snapshot [n|slug]
  node scripts/board.mjs restore <n|slug>
`);
}

function nowIso() {
  return new Date().toISOString();
}

function refreshEpicMd(config, epicPath, meta) {
  const issues = listIssuesInEpic(config, epicPath);
  const { total, done, pct } = progressForIssues(config, issues);
  const byCol = {};
  for (const c of columns(config)) byCol[c.id] = [];
  for (const i of issues) {
    byCol[i.column] = byCol[i.column] || [];
    byCol[i.column].push(`- \`${i.meta.id}\` ${i.meta.title} _(role:${i.meta.role})_`);
  }
  const issueList = columns(config)
    .map((c) => {
      const lines = byCol[c.id] || [];
      return `### ${c.folder}\n\n${lines.length ? lines.join("\n") : "_empty_"}\n`;
    })
    .join("\n");

  const tplPath = path.join(ROOT, "templates", "epic.md");
  let tpl = fs.existsSync(tplPath)
    ? fs.readFileSync(tplPath, "utf8")
    : "# {{TITLE}}\n\nProgress {{PROGRESS}}%\n\n{{ISSUE_LIST}}\n";
  const outMd = tpl
    .replaceAll("{{TITLE}}", meta.title)
    .replaceAll("{{ID}}", String(meta.id))
    .replaceAll("{{SLUG}}", meta.slug)
    .replaceAll("{{STATUS}}", meta.status)
    .replaceAll("{{PROGRESS}}", String(pct))
    .replaceAll("{{DONE}}", String(done))
    .replaceAll("{{TOTAL}}", String(total))
    .replaceAll("{{COLOR}}", meta.color || "#6B7280")
    .replaceAll("{{ISSUE_LIST}}", issueList);

  fs.writeFileSync(path.join(epicPath, "epic.md"), outMd, "utf8");

  const metaPath = path.join(epicPath, "meta.yaml");
  let extra = {};
  try {
    extra = loadEpicMeta(epicPath) || {};
  } catch {
    extra = {};
  }
  meta.updated_at = nowIso();
  if (pct === 100 && total > 0) meta.status = "completed";
  else if (pct > 0 && meta.status === "planned") meta.status = "in_progress";
  const out = { ...extra, ...meta };
  fs.writeFileSync(metaPath, stringifyYaml(out) + "\n", "utf8");
  return { total, done, pct };
}

function clockSafe(fn) {
  try {
    return fn();
  } catch (err) {
    process.stderr.write(`Work clock skipped: ${err.message}\n`);
    return null;
  }
}

function clockPulseIssue(config, issue, event) {
  return clockSafe(() =>
    pulseClock(config, issue.epicPath, issue.meta.id, {
      agent: issue.meta.agent,
      event,
    })
  );
}

function clockCloseIssue(config, issue, reason) {
  return clockSafe(() =>
    closeClock(config, issue.epicPath, issue.meta.id, reason)
  );
}

function hostRootFromLinear() {
  return hostGitRootFromBoard() || path.resolve(ROOT, "..");
}

function persistBoardSnapshot(epicPath) {
  try {
    const result = snapshotEpicToDocs(epicPath, hostRootFromLinear());
    if (result.ok) return result;
  } catch (err) {
    process.stderr.write(`Board snapshot skipped: ${err.message}\n`);
  }
  return null;
}

async function refreshLivingSummary(config, epicPath, epicMeta) {
  try {
    const html = await regenerateEpicSummaryHtml(config, epicPath, epicMeta);
    persistBoardSnapshot(epicPath);
    return html;
  } catch (err) {
    process.stderr.write(`Living summary refresh skipped: ${err.message}\n`);
    persistBoardSnapshot(epicPath);
    return null;
  }
}

function moveHistoryLine(from, to, reason, order) {
  const back = (order[to] ?? 0) < (order[from] ?? 0);
  let why = reason;
  if (!why && back && from === "testing" && to === "doing") {
    why = "verification failed — returned to implementation";
  } else if (!why && back) {
    why = "returned to an earlier column";
  }
  return why ? `moved ${from} → ${to} (${why})` : `moved ${from} → ${to}`;
}

function cmdEpics(config) {
  const rows = [];
  for (const dir of epicDirs()) {
    const meta = loadEpicMeta(dir);
    const issues = listIssuesInEpic(config, dir);
    const p = progressForIssues(config, issues);
    rows.push({
      id: meta.id,
      key: `${meta.id}-${meta.slug}`,
      title: meta.title,
      status: meta.status,
      issues: p.total,
      progress: `${p.pct}%`,
    });
  }
  rows.sort((a, b) => Number(b.id) - Number(a.id));
  if (!rows.length) {
    console.log("No epics. Create one: node scripts/board.mjs epic create 1 my-slug");
    return;
  }
  console.table(rows);
}

async function cmdEpicCreate(config, argv) {
  const n = argv[2];
  const slug = argv[3];
  if (!n || !slug) throw new Error("Usage: epic create <n> <slug> [--title]");
  const title =
    flag(argv, "--title") ||
    `${config.team_key} · Epic ${n} — ${slug}`;
  const dirName = `${n}-${slug}`;
  const epicPath = path.join(ROOT, "epics", dirName);
  if (fs.existsSync(epicPath)) throw new Error(`Epic exists: ${dirName}`);
  ensureBoardFolders(config, epicPath);
  const meta = {
    id: Number(n),
    slug,
    title,
    color: flag(argv, "--color") || "#b45309",
    status: "planned",
    created_at: nowIso(),
    updated_at: nowIso(),
  };
  fs.writeFileSync(
    path.join(epicPath, "meta.yaml"),
    stringifyYaml(meta) + "\n",
    "utf8"
  );
  refreshEpicMd(config, epicPath, meta);
  await refreshLivingSummary(config, epicPath, meta);
  console.log(`Created epic ${dirName}`);
}

function cmdEpicShow(config, argv) {
  const ref = argv[2];
  if (!ref) throw new Error("Usage: epic show <n|slug>");
  const { path: epicPath, meta } = findEpic(ref);
  const issues = listIssuesInEpic(config, epicPath);
  const p = progressForIssues(config, issues);
  console.log(JSON.stringify({ meta, progress: p, issues: issues.map((i) => ({
    id: i.meta.id,
    title: i.meta.title,
    role: i.meta.role,
    column: i.column,
    agent: i.meta.agent,
    blocked_by: i.meta.blocked_by || [],
  })) }, null, 2));
}

function nextSeq(config, epicMeta, issues) {
  const prefix = `${config.team_key}-${epicMeta.id}-`;
  let max = 0;
  for (const i of issues) {
    const id = i.meta.id || "";
    if (id.startsWith(prefix)) {
      const n = Number(id.slice(prefix.length));
      if (n > max) max = n;
    }
  }
  return max + 1;
}

async function cmdIssueCreate(config, argv) {
  const epicRef = argv[2];
  const title = flag(argv, "--title");
  const role = flag(argv, "--role");
  if (!epicRef || !title || !role) {
    throw new Error(
      'Usage: issue create <epic> --title "..." --role backend [--blocks ID]'
    );
  }
  if (!config.roles.includes(role)) {
    throw new Error(`Invalid role "${role}". Allowed: ${config.roles.join(", ")}`);
  }
  const { path: epicPath, meta: epicMeta } = findEpic(epicRef);
  ensureBoardFolders(config, epicPath);
  const issues = listIssuesInEpic(config, epicPath);
  const seq = nextSeq(config, epicMeta, issues);
  const id = `${config.team_key}-${epicMeta.id}-${seq}`;
  const blocks = [];
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === "--blocks" && argv[i + 1]) blocks.push(argv[i + 1]);
  }
  const blocked_by = [];
  for (let i = 0; i < argv.length; i++) {
    if (argv[i] === "--blocked-by" && argv[i + 1]) blocked_by.push(argv[i + 1]);
  }
  const short = slugify(title);
  const file = `${id}-${short}.md`;
  const backlog = resolveColumn(config, "backlog");
  const dest = path.join(epicPath, "board", backlog.folder, file);
  const body = `## Objetivo

${title}

## Checklist

Mark in order, one \`checklist <id> n\` command per item, immediately after that step is finished.

- [ ] Implement
- [ ] Verify locally (in testing — after \`move testing\`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: \`evidence <id> --title …\`.

## History

- ${nowIso()} created → backlog
`;
  const meta = {
    id,
    title,
    role,
    epic: epicMeta.id,
    estimate: Number(flag(argv, "--estimate") || 1),
    blocked_by,
    blocks,
    agent: null,
    claimed_at: null,
  };
  writeIssueFile(dest, meta, body);
  appendBoardLog(epicPath, { type: "create", issueId: id, column: "backlog" });
  refreshEpicMd(config, epicPath, epicMeta);
  await refreshLivingSummary(config, epicPath, epicMeta);
  console.log(`Created ${id} in ${backlog.folder}`);
}

function cmdList(config, argv) {
  const epicF = flag(argv, "--epic");
  const roleF = flag(argv, "--role");
  const colF = flag(argv, "--column");
  const dirs = epicF ? [findEpic(epicF).path] : epicDirs();
  const rows = [];
  for (const dir of dirs) {
    let issues = listIssuesInEpic(config, dir);
    if (roleF) issues = issues.filter((i) => i.meta.role === roleF);
    if (colF) {
      const c = resolveColumn(config, colF);
      issues = issues.filter((i) => i.column === c.id);
    }
    for (const i of issues) {
      rows.push({
        id: i.meta.id,
        title: i.meta.title,
        role: i.meta.role,
        column: i.column,
        epic: i.meta.epic,
        agent: i.meta.agent || "",
      });
    }
  }
  console.table(rows);
}

function cmdNext(config, argv) {
  const role = flag(argv, "--role");
  if (!role) throw new Error("Usage: next --role <role> [--epic N]");
  const epicF = flag(argv, "--epic");
  const dirs = epicF ? [findEpic(epicF).path] : epicDirs();
  const backlog = resolveColumn(config, "backlog");
  const reg = readClaims(config);
  const ttl = config.heartbeat_ttl_ms || 300000;
  // Cross-epic blocked_by: resolve deps against the full board, not only this epic
  const boardIndex = listAllIssues(config);

  for (const dir of dirs) {
    const all = listIssuesInEpic(config, dir);
    const candidates = all.filter((i) => i.column === backlog.id && i.meta.role === role);
    for (const i of candidates) {
      const claim = reg.claims[i.meta.id];
      if (claim && !isClaimStale(claim, ttl)) continue;
      if (isIssueBlocked(config, i, boardIndex)) continue;
      console.log(
        JSON.stringify(
          {
            available: true,
            id: i.meta.id,
            title: i.meta.title,
            epic: i.meta.epic,
            path: i.path,
          },
          null,
          2
        )
      );
      return;
    }
  }
  console.log(JSON.stringify({ available: false, message: `No issues for role ${role}` }, null, 2));
}

function moveIssueFile(issue, destFolder, epicPath) {
  const destDir = path.join(epicPath, "board", destFolder);
  fs.mkdirSync(destDir, { recursive: true });
  const dest = path.join(destDir, path.basename(issue.path));
  fs.renameSync(issue.path, dest);
  return dest;
}

async function cmdClaim(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  if (!id || !agent) throw new Error("Usage: claim <id> --agent NAME");
  const issue = findIssue(config, id);
  if (issue.column !== "backlog") {
    throw new Error(`${id} is in ${issue.column}, not backlog`);
  }
  if (isIssueBlocked(config, issue, listAllIssues(config))) {
    throw new Error(`${id} is blocked by unfinished dependencies`);
  }
  const existing = getActiveClaim(config, id);
  if (existing) throw new Error(`${id} already claimed by ${existing.agentId}`);

  const reg = readClaims(config);
  // clean stale on this id
  if (reg.claims[id] && isClaimStale(reg.claims[id], config.heartbeat_ttl_ms || 300000)) {
    delete reg.claims[id];
  }
  const ts = nowIso();
  reg.claims[id] = {
    agentId: agent,
    claimedAt: ts,
    lastHeartbeat: ts,
    role: issue.meta.role,
  };
  writeClaims(config, reg);

  const doing = resolveColumn(config, "doing");
  issue.meta.agent = agent;
  issue.meta.claimed_at = ts;
  const body = appendHistory(issue.body, `claimed by ${agent} → doing`);
  const dest = moveIssueFile(issue, doing.folder, issue.epicPath);
  writeIssueFile(dest, issue.meta, body);
  appendBoardLog(issue.epicPath, { type: "claim", issueId: id, agent });
  clockPulseIssue(config, { ...issue, meta: issue.meta }, "claim");
  refreshEpicMd(config, issue.epicPath, issue.epicMeta);
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(
    JSON.stringify(
      {
        claimed: true,
        id,
        agent,
        column: "doing",
        next: `node .ai/scripts/worktree-add.mjs ${id}`,
        next_engine: `node scripts/worktree-add.mjs ${id} --repo <host-git-root>`,
        next_note:
          "Required: one claimed ticket = one worktree folder .worktrees/{n}-{slug}/{ISSUE_ID} on ticket/{n}-{slug}/{ISSUE_ID}",
      },
      null,
      2
    )
  );
}

function cmdPing(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  if (!id || !agent) throw new Error("Usage: ping <id> --agent NAME");
  assertClaimOwner(config, id, agent);
  const issue = findIssue(config, id);
  const reg = readClaims(config);
  reg.claims[id].lastHeartbeat = nowIso();
  writeClaims(config, reg);
  clockPulseIssue(config, { ...issue, meta: { ...issue.meta, agent } }, "ping");
  console.log(JSON.stringify({ ok: true, lastHeartbeat: reg.claims[id].lastHeartbeat }, null, 2));
}

async function cmdRelease(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  const reason = flag(argv, "--reason") || "";
  if (!id || !agent) throw new Error("Usage: release <id> --agent NAME");
  assertClaimOwner(config, id, agent);
  const issue = findIssue(config, id);
  const reg = readClaims(config);
  delete reg.claims[id];
  writeClaims(config, reg);
  issue.meta.agent = null;
  issue.meta.claimed_at = null;
  const backlog = resolveColumn(config, "backlog");
  const body = appendHistory(issue.body, `released by ${agent}${reason ? `: ${reason}` : ""} → backlog`);
  const dest = moveIssueFile(issue, backlog.folder, issue.epicPath);
  writeIssueFile(dest, issue.meta, body);
  appendBoardLog(issue.epicPath, { type: "release", issueId: id, agent, reason });
  clockCloseIssue(config, issue, "release");
  refreshEpicMd(config, issue.epicPath, issue.epicMeta);
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(JSON.stringify({ released: true, id }, null, 2));
}

async function cmdFail(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  const reason = flag(argv, "--reason");
  if (!id || !agent || !reason) {
    throw new Error('Usage: fail <id> --agent NAME --reason "..."');
  }
  assertClaimOwner(config, id, agent);
  const issue = findIssue(config, id);
  const fromTesting = issue.column === "testing";
  const destId = fromTesting ? "doing" : "backlog";
  const destCol = resolveColumn(config, destId);
  if (!fromTesting) {
    const reg = readClaims(config);
    delete reg.claims[id];
    writeClaims(config, reg);
    issue.meta.agent = null;
    issue.meta.claimed_at = null;
  }
  const hist = fromTesting
    ? `FAILED verification by ${agent}: ${reason} → doing`
    : `FAILED by ${agent}: ${reason} → backlog`;
  const body = appendHistory(issue.body, hist);
  const dest = moveIssueFile(issue, destCol.folder, issue.epicPath);
  writeIssueFile(dest, issue.meta, body);
  appendBoardLog(issue.epicPath, {
    type: "fail",
    issueId: id,
    agent,
    reason,
    from: issue.column,
    to: destId,
  });
  refreshEpicMd(config, issue.epicPath, issue.epicMeta);
  clockPulseIssue(config, { ...issue, meta: { ...issue.meta, agent } }, "fail");
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(JSON.stringify({ failed: true, id, reason, column: destId }, null, 2));
}

async function cmdMove(config, argv) {
  const id = argv[1];
  const colName = argv[2];
  const agent = flag(argv, "--agent");
  const force = has(argv, "--force");
  const reason = flag(argv, "--reason");
  if (!id || !colName) {
    throw new Error(
      'Usage: move <id> <column> [--agent] [--force] [--reason "..."]'
    );
  }
  const issue = findIssue(config, id);
  const target = resolveColumn(config, colName);
  if (issue.column === target.id) {
    console.log(JSON.stringify({ ok: true, noop: true, column: target.id }, null, 2));
    return;
  }

  if (["testing", "done", "summarize"].includes(target.id) && !force) {
    // done → summarize: claim may already be cleared or optional; technical work was accepted at done
    const fromDoneToSummarize =
      target.id === "summarize" && issue.column === "done";
    if (!fromDoneToSummarize) {
      try {
        assertClaimOwner(config, id, agent || issue.meta.agent, { force });
      } catch (e) {
        if (agent || issue.meta.agent) throw e;
        if (!force) throw e;
      }
    }
  }

  const harness = await loadHarness(config);
  const epicIssues = listIssuesInEpic(config, issue.epicPath);
  const ctx = {
    meta: issue.meta,
    body: issue.body,
    column: issue.column,
    force,
    issuePath: issue.path,
    epicPath: issue.epicPath,
    issues: epicIssues,
  };

  if (target.id === "testing") {
    const gate = await harness.canMoveToTesting(ctx);
    if (!gate.ok) throw new Error(gate.reason);
  }
  if (target.id === "done") {
    const gate = await harness.canMoveToDone({ ...ctx, column: issue.column });
    if (!gate.ok) throw new Error(gate.reason);
    assertTicketWorktreeCommitted(id, force);
  }
  if (target.id === "summarize") {
    if (!force) {
      const open = issuesOpenBeforeDone(epicIssues);
      if (open.length) {
        throw new Error(
          `Summarize waits until every ticket in this epic is in done. Still open: ${open
            .map((i) => i.meta.id)
            .join(", ")}`
        );
      }
    }
    const gateFn = harness.canMoveToSummarize || (async () => ({ ok: true }));
    const gate = await gateFn({ ...ctx, column: issue.column });
    if (!gate.ok) throw new Error(gate.reason);
  }

  const order = Object.fromEntries(columns(config).map((c) => [c.id, c.order]));
  let body = appendHistory(
    issue.body,
    moveHistoryLine(issue.column, target.id, reason, order)
  );
  let meta = { ...issue.meta };
  if (target.id === "backlog") {
    meta.agent = null;
    meta.claimed_at = null;
    const reg = readClaims(config);
    delete reg.claims[id];
    writeClaims(config, reg);
  }
  // Keep claim through done so done → summarize does not need --force.
  // Clear claim when entering summarize (or backlog).
  if (target.id === "summarize") {
    const reg = readClaims(config);
    delete reg.claims[id];
    writeClaims(config, reg);
  }

  let summaryInfo = null;
  if (target.id === "summarize") {
    clockPulseIssue(config, { ...issue, meta }, "move");
    clockCloseIssue(config, { ...issue, meta }, "summarize");
    const destPreview = moveIssueFile(issue, target.folder, issue.epicPath);
    // Move file first so listIssuesInEpic sees this issue in summarize
    writeIssueFile(destPreview, meta, body);
    const allIssues = listIssuesInEpic(config, issue.epicPath).map((i) =>
      i.meta.id === id ? { ...i, column: "summarize", meta, body } : i
    );
    summaryInfo = await writeEpicSummary({
      config,
      harness,
      epicPath: issue.epicPath,
      epicMeta: issue.epicMeta,
      issues: allIssues,
    });
    meta.summary_html = summaryInfo.relativePath.replace(/\\/g, "/");
    body = appendHistory(body, `epic summary HTML → ${meta.summary_html}`);
    writeIssueFile(destPreview, meta, body);
    // Stamp shared path on every summarize-column issue
    for (const peer of allIssues) {
      if (peer.column !== "summarize" || peer.meta.id === id) continue;
      const peerMeta = {
        ...peer.meta,
        summary_html: meta.summary_html,
      };
      writeIssueFile(peer.path, peerMeta, peer.body);
    }
    appendBoardLog(issue.epicPath, {
      type: "move",
      issueId: id,
      from: issue.column,
      to: target.id,
      reason: reason || undefined,
      summary_html: summaryInfo.relativePath,
    });
    const p = refreshEpicMd(config, issue.epicPath, issue.epicMeta);
    console.log(
      JSON.stringify(
        {
          moved: true,
          id,
          column: target.id,
          epicProgress: p.pct,
          summary_html: path
            .join(path.basename(issue.epicPath), summaryInfo.relativePath)
            .replace(/\\/g, "/"),
        },
        null,
        2
      )
    );
    return;
  }

  const dest = moveIssueFile(issue, target.folder, issue.epicPath);
  writeIssueFile(dest, meta, body);
  appendBoardLog(issue.epicPath, {
    type: "move",
    issueId: id,
    from: issue.column,
    to: target.id,
    reason: reason || undefined,
  });
  if (target.id === "done" || target.id === "backlog") {
    clockPulseIssue(config, { ...issue, meta }, "move");
    clockCloseIssue(config, { ...issue, meta }, target.id);
  } else {
    clockPulseIssue(config, { ...issue, meta }, "move");
  }
  const p = refreshEpicMd(config, issue.epicPath, issue.epicMeta);
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(
    JSON.stringify(
      {
        moved: true,
        id,
        column: target.id,
        epicProgress: p.pct,
      },
      null,
      2
    )
  );
}

async function regenerateEpicSummaryHtml(config, epicPath, epicMeta) {
  const harness = await loadHarness(config);
  const issues = listIssuesInEpic(config, epicPath);
  const summaryInfo = await writeEpicSummary({
    config,
    harness,
    epicPath,
    epicMeta,
    issues,
  });
  const rel = summaryInfo.relativePath.replace(/\\/g, "/");
  for (const peer of issues) {
    if (peer.column !== "summarize") continue;
    const peerMeta = { ...peer.meta, summary_html: rel };
    writeIssueFile(peer.path, peerMeta, peer.body);
  }
  return summaryInfo;
}

async function cmdSummarize(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  const force = has(argv, "--force");
  if (!id) throw new Error("Usage: summarize <id> [--agent NAME] [--force]");
  const issue = findIssue(config, id);

  // Already in summarize → regenerate epic HTML only (do not wait on siblings)
  if (issue.column === "summarize") {
    const summaryInfo = await regenerateEpicSummaryHtml(
      config,
      issue.epicPath,
      issue.epicMeta
    );
    const body = appendHistory(
      issue.body,
      `epic summary HTML regenerated → ${summaryInfo.relativePath.replace(/\\/g, "/")}`
    );
    writeIssueFile(issue.path, { ...issue.meta, summary_html: summaryInfo.relativePath.replace(/\\/g, "/") }, body);
    console.log(
      JSON.stringify(
        {
          regenerated: true,
          id,
          summary_html: path
            .join(path.basename(issue.epicPath), summaryInfo.relativePath)
            .replace(/\\/g, "/"),
        },
        null,
        2
      )
    );
    return;
  }

  const harness = await loadHarness(config);
  const epicIssues = listIssuesInEpic(config, issue.epicPath);
  const gateFn = harness.canMoveToSummarize || (async () => ({ ok: true }));
  const gate = await gateFn({
    meta: issue.meta,
    body: issue.body,
    column: issue.column,
    force,
    issuePath: issue.path,
    epicPath: issue.epicPath,
    issues: epicIssues,
  });
  if (!gate.ok) throw new Error(gate.reason);

  await cmdMove(config, [
    "move",
    id,
    "summarize",
    ...(agent ? ["--agent", agent] : []),
    ...(force ? ["--force"] : []),
  ]);
}

async function cmdSummarizeEpic(config, argv) {
  const ref = argv[1];
  if (!ref) throw new Error("Usage: summarize-epic <n|slug>");
  const { path: epicPath, meta } = findEpic(ref);
  const summaryInfo = await regenerateEpicSummaryHtml(config, epicPath, meta);
  console.log(
    JSON.stringify(
      {
        regenerated: true,
        epic: meta.id,
        summary_html: path
          .join(path.basename(epicPath), summaryInfo.relativePath)
          .replace(/\\/g, "/"),
      },
      null,
      2
    )
  );
}

async function cmdChecklist(config, argv) {
  const id = argv[1];
  const n = argv[2];
  const agent = flag(argv, "--agent");
  const force = has(argv, "--force");
  if (!id || !n) throw new Error("Usage: checklist <id> <n>");
  const issue = findIssue(config, id);
  if ((issue.column === "doing" || issue.column === "testing") && !force) {
    assertClaimOwner(config, id, agent || issue.meta.agent, { force });
  }
  const validation = validateSequentialChecklistMark(issue.raw, n);
  // validate against body+full — checklist is in body; use full raw for parse which includes body only after fm
  const fullBody = issue.body;
  const v2 = validateSequentialChecklistMark(fullBody, n);
  if (!v2.ok) throw new Error(`${id}: ${v2.error}`);
  if (v2.noop) {
    console.log(JSON.stringify({ ok: true, noop: true }, null, 2));
    return;
  }
  const indices = parseChecklistSpec(n);
  const newBody = markChecklistInDescription(fullBody, indices);
  const withHist = appendHistory(newBody, `checklist item ${n} marked`);
  writeIssueFile(issue.path, issue.meta, withHist);
  appendBoardLog(issue.epicPath, { type: "checklist", issueId: id, item: n });
  clockPulseIssue(config, issue, "checklist");
  const summary = checklistSummary(withHist);
  const p = refreshEpicMd(config, issue.epicPath, issue.epicMeta);
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(
    JSON.stringify(
      {
        ok: true,
        id,
        checklist: `${summary.done}/${summary.total}`,
        epicProgress: p.pct,
      },
      null,
      2
    )
  );
}

function readFlagFile(val) {
  if (!val || val === true) return "";
  const abs = path.isAbsolute(val) ? val : path.resolve(process.cwd(), val);
  if (!fs.existsSync(abs)) {
    throw new Error(`File not found: ${abs}`);
  }
  return fs.readFileSync(abs, "utf8");
}

async function cmdEvidence(config, argv) {
  const id = argv[1];
  const agent = flag(argv, "--agent");
  const title = flag(argv, "--title");
  if (!id || !title || title === true) {
    throw new Error(
      'Usage: evidence <id> --title "…" [--path file] [--lang sql] [--code-file f | --code "…"] [--command "…"] [--result-file f | --result "…"] [--why "…"]'
    );
  }
  const issue = findIssue(config, id);
  if ((issue.column === "doing" || issue.column === "testing") && !has(argv, "--force")) {
    assertClaimOwner(config, id, agent || issue.meta.agent, { force: false });
  }
  const codeFile = flag(argv, "--code-file");
  const resultFile = flag(argv, "--result-file");
  const code =
    codeFile && codeFile !== true
      ? readFlagFile(codeFile)
      : flag(argv, "--code") && flag(argv, "--code") !== true
        ? String(flag(argv, "--code"))
        : "";
  const result =
    resultFile && resultFile !== true
      ? readFlagFile(resultFile)
      : flag(argv, "--result") && flag(argv, "--result") !== true
        ? String(flag(argv, "--result"))
        : "";
  const command =
    flag(argv, "--command") && flag(argv, "--command") !== true
      ? String(flag(argv, "--command"))
      : "";
  const why =
    flag(argv, "--why") && flag(argv, "--why") !== true
      ? String(flag(argv, "--why"))
      : "";
  const filePath =
    flag(argv, "--path") && flag(argv, "--path") !== true
      ? String(flag(argv, "--path"))
      : "";
  const lang =
    flag(argv, "--lang") && flag(argv, "--lang") !== true
      ? String(flag(argv, "--lang"))
      : "text";
  if (!code && !command && !result) {
    throw new Error("evidence needs --code / --code-file, --command, and/or --result / --result-file");
  }
  const entry = formatEvidenceEntry({
    title,
    path: filePath,
    lang,
    code,
    command,
    result,
    why,
  });
  const newBody = insertEvidence(issue.body, entry);
  writeIssueFile(issue.path, issue.meta, newBody);
  appendBoardLog(issue.epicPath, { type: "evidence", issueId: id, title });
  clockPulseIssue(config, issue, "evidence");
  await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  console.log(
    JSON.stringify(
      {
        ok: true,
        id,
        evidence: true,
        fences: fenceCount(newBody),
        recorded: hasEvidenceFences(newBody),
      },
      null,
      2
    )
  );
}

function cmdChecklistStatus(config, argv) {
  const id = argv[1];
  if (!id) throw new Error("Usage: checklist-status <id>");
  const issue = findIssue(config, id);
  const { items, total, done } = checklistSummary(issue.body);
  console.log(`\n📋 ${id}: checklist ${done}/${total}\n`);
  for (const i of items) {
    console.log(`  ${i.done ? "✅" : "⬜"} ${i.index}. ${i.text}`);
  }
  console.log("");
}

function cmdHours(config, argv) {
  const month = flag(argv, "--month") || currentMonthKey();
  const rows = epicDirs().map((dir) => {
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
  console.log(
    JSON.stringify({ months, ...boardHoursForMonth(rows, month) }, null, 2)
  );
}

function cmdProgress(config, argv) {
  const epicF = flag(argv, "--epic");
  const dirs = epicF ? [findEpic(epicF).path] : epicDirs();
  for (const dir of dirs) {
    const meta = loadEpicMeta(dir);
    const issues = listIssuesInEpic(config, dir);
    const p = progressForIssues(config, issues);
    console.log(
      `${meta.title}: ${p.pct}% (${p.done}/${p.total} in summarize · column+checklist)`
    );
    const t = clockTotals(readClock(dir, config));
    if (t.started_at) {
      console.log(
        `  time: active ${t.active_label} · effort ${t.effort_label} · elapsed ${t.wall_label}`
      );
    }
  }
}

function cmdHistory(config, argv) {
  const id = argv[1];
  if (!id) throw new Error("Usage: history <id>");
  const issue = findIssue(config, id);
  const m = issue.body.match(/## History\s*\n([\s\S]*?)(?=\n## |\s*$)/i);
  console.log(m ? m[0] : "(no history section)");
}

function cmdWatchdog(config) {
  const reg = readClaims(config);
  const ttl = config.heartbeat_ttl_ms || 300000;
  const reverted = [];
  for (const [issueId, claim] of Object.entries(reg.claims)) {
    if (!isClaimStale(claim, ttl)) continue;
    try {
      const issue = findIssue(config, issueId);
      delete reg.claims[issueId];
      issue.meta.agent = null;
      issue.meta.claimed_at = null;
      const backlog = resolveColumn(config, "backlog");
      const body = appendHistory(
        issue.body,
        `WATCHDOG: stale claim by ${claim.agentId} → backlog`
      );
      const dest = moveIssueFile(issue, backlog.folder, issue.epicPath);
      writeIssueFile(dest, issue.meta, body);
      appendBoardLog(issue.epicPath, {
        type: "watchdog",
        issueId,
        agentId: claim.agentId,
      });
      refreshEpicMd(config, issue.epicPath, issue.epicMeta);
      clockCloseIssue(config, issue, "watchdog");
      reverted.push({ issueId, agentId: claim.agentId });
    } catch (e) {
      delete reg.claims[issueId];
      reverted.push({ issueId, error: e.message });
    }
  }
  writeClaims(config, reg);
  console.log(
    JSON.stringify(
      {
        reverted: reverted.length,
        activeClaims: Object.keys(reg.claims).length,
        details: reverted,
      },
      null,
      2
    )
  );
}

async function cmdHandoff(config, argv) {
  const ref = argv[1];
  if (!ref) {
    throw new Error(
      'Usage: handoff <n|slug> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" [--note-file path | --note "..."]'
    );
  }
  const { path: epicPath, meta } = findEpic(ref);
  const gitFlag = flag(argv, "--git");
  const noteFile = flag(argv, "--note-file");
  const noteFlag = flag(argv, "--note");
  const existing = readHandoff(epicPath);
  const git =
    (typeof gitFlag === "string" && gitFlag) ||
    existing?.git ||
    defaultEpicGitCommand(meta);
  let note = existing?.note || "";
  if (typeof noteFile === "string" && noteFile) {
    const abs = path.isAbsolute(noteFile)
      ? noteFile
      : path.resolve(process.cwd(), noteFile);
    if (!fs.existsSync(abs)) throw new Error(`Note file not found: ${abs}`);
    note = fs.readFileSync(abs, "utf8");
  } else if (typeof noteFlag === "string" && noteFlag !== "true") {
    note = noteFlag;
  }
  if (!String(note).trim()) {
    throw new Error(
      "Handoff needs the Supervisor / Manager note. Pass --note-file <path> or --note \"...\"."
    );
  }
  writeHandoffFile(epicPath, { git, note });
  appendBoardLog(epicPath, { type: "handoff", git });
  const time = clockSafe(() => finishEpicClock(config, epicPath));
  const summaryInfo = await regenerateEpicSummaryHtml(config, epicPath, meta);
  console.log(
    JSON.stringify(
      {
        recorded: true,
        epic: `${meta.id}-${meta.slug}`,
        git,
        handoff: path.join(path.basename(epicPath), "summaries/handoff.md").replace(/\\/g, "/"),
        summary_html: path
          .join(path.basename(epicPath), summaryInfo.relativePath)
          .replace(/\\/g, "/"),
        time: time
          ? {
              active: time.active_label,
              effort: time.effort_label,
              wall: time.wall_label,
            }
          : undefined,
      },
      null,
      2
    )
  );
}

async function cmdClock(config, argv) {
  const sub = argv[1];
  const ref = argv[2];
  if (!sub || !["pulse", "pause", "resume", "status"].includes(sub)) {
    throw new Error(
      'Usage: clock pulse|pause|resume <id> | clock status [id|n|slug]'
    );
  }
  if (sub === "status") {
    const target = ref || flag(argv, "--epic");
    if (!target) throw new Error("Usage: clock status <id|n|slug>");
    try {
      const issue = findIssue(config, target);
      const totals = clockTotals(readClock(issue.epicPath, config));
      console.log(
        JSON.stringify(
          { epic: `${issue.epicMeta.id}-${issue.epicMeta.slug}`, ...totals, issue: totals.issues[issue.meta.id] },
          null,
          2
        )
      );
      return;
    } catch {
      const { path: epicPath, meta } = findEpic(target);
      const totals = clockTotals(readClock(epicPath, config));
      console.log(
        JSON.stringify({ epic: `${meta.id}-${meta.slug}`, ...totals }, null, 2)
      );
      return;
    }
  }
  if (!ref) throw new Error(`Usage: clock ${sub} <id> [--agent NAME] [--reason "..."]`);
  const issue = findIssue(config, ref);
  const agent = flag(argv, "--agent") || issue.meta.agent;
  if (sub === "pause") {
    const reason = flag(argv, "--reason") || "waiting for approval";
    const totals = pauseClock(config, issue.epicPath, issue.meta.id, reason);
    appendBoardLog(issue.epicPath, { type: "clock-pause", issueId: issue.meta.id, reason });
    await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
    console.log(
      JSON.stringify(
        { paused: true, id: issue.meta.id, reason, active: totals.issues[issue.meta.id]?.active_label },
        null,
        2
      )
    );
    return;
  }
  const event = sub === "resume" ? "resume" : "pulse";
  const totals = pulseClock(config, issue.epicPath, issue.meta.id, { agent, event });
  if (sub === "resume") {
    appendBoardLog(issue.epicPath, { type: "clock-resume", issueId: issue.meta.id, agent });
    await refreshLivingSummary(config, issue.epicPath, issue.epicMeta);
  }
  console.log(
    JSON.stringify(
      {
        ok: true,
        id: issue.meta.id,
        event,
        active: totals.issues[issue.meta.id]?.active_label,
        epic_active: totals.active_label,
      },
      null,
      2
    )
  );
}

async function cmdRefreshEpic(config, argv) {
  const ref = argv[2] || argv[1];
  if (!ref || ref === "refresh-epic") throw new Error("Usage: refresh-epic <n|slug>");
  const { path: epicPath, meta } = findEpic(ref);
  const p = refreshEpicMd(config, epicPath, meta);
  await refreshLivingSummary(config, epicPath, meta);
  console.log(JSON.stringify({ refreshed: true, progress: p }, null, 2));
}

function cmdSnapshot(config, argv) {
  const ref = argv[1];
  const host = hostRootFromLinear();
  const dirs = ref ? [findEpic(ref).path] : epicDirs();
  const out = [];
  for (const epicPath of dirs) {
    out.push(snapshotEpicToDocs(epicPath, host));
  }
  console.log(JSON.stringify({ snapshots: out, host }, null, 2));
}

function cmdRestore(config, argv) {
  const ref = argv[1];
  if (!ref) throw new Error("Usage: restore <n|slug>");
  const host = hostRootFromLinear();
  let epicKey = ref;
  if (!/^\d+-/.test(ref)) {
    try {
      const found = findEpic(ref);
      epicKey = path.basename(found.path);
    } catch {
      const hits = listBoardSnapshots(host).filter(
        (k) => k === ref || k.startsWith(`${ref}-`)
      );
      if (hits.length === 1) epicKey = hits[0];
    }
  }
  const result = restoreEpicFromDocs(host, epicKey, ROOT);
  if (!result.ok) throw new Error(result.reason);
  console.log(JSON.stringify({ restored: true, ...result }, null, 2));
}

async function main() {
  const argv = args();
  const cmd = argv[0];
  if (!cmd || cmd === "-h" || cmd === "--help") {
    usage();
    return;
  }
  const config = loadConfig();

  try {
    if (cmd === "epics") cmdEpics(config);
    else if (cmd === "epic" && argv[1] === "create") await cmdEpicCreate(config, argv);
    else if (cmd === "epic" && argv[1] === "show") cmdEpicShow(config, argv);
    else if (cmd === "issue" && argv[1] === "create") await cmdIssueCreate(config, argv);
    else if (cmd === "list") cmdList(config, argv);
    else if (cmd === "next") cmdNext(config, argv);
    else if (cmd === "claim") await cmdClaim(config, argv);
    else if (cmd === "ping") cmdPing(config, argv);
    else if (cmd === "release") await cmdRelease(config, argv);
    else if (cmd === "fail") await cmdFail(config, argv);
    else if (cmd === "move") await cmdMove(config, argv);
    else if (cmd === "summarize") await cmdSummarize(config, argv);
    else if (cmd === "summarize-epic") await cmdSummarizeEpic(config, argv);
    else if (cmd === "handoff") await cmdHandoff(config, argv);
    else if (cmd === "clock") await cmdClock(config, argv);
    else if (cmd === "hours") cmdHours(config, argv);
    else if (cmd === "checklist") await cmdChecklist(config, argv);
    else if (cmd === "evidence") await cmdEvidence(config, argv);
    else if (cmd === "checklist-status") cmdChecklistStatus(config, argv);
    else if (cmd === "progress") cmdProgress(config, argv);
    else if (cmd === "history") cmdHistory(config, argv);
    else if (cmd === "watchdog") cmdWatchdog(config);
    else if (cmd === "refresh-epic") await cmdRefreshEpic(config, argv);
    else if (cmd === "snapshot") cmdSnapshot(config, argv);
    else if (cmd === "restore") cmdRestore(config, argv);
    else {
      usage();
      process.exitCode = 1;
    }
  } catch (err) {
    console.error(`Error: ${err.message}`);
    process.exitCode = 1;
  }
}

main();
