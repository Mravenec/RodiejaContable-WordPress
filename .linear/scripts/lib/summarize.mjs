/**
 * Beginner HTML summary for the last board column (`summarize`).
 * One document per Epic — each issue is a section. English, harness-adapted, no product brands.
 */
import fs from "fs";
import path from "path";
import { checklistSummary } from "./checklist.mjs";
import { parseYaml, stringifyYaml } from "./yaml-lite.mjs";
import { readClock, clockTotals } from "./work-clock.mjs";
import { extractEvidence, hasEvidenceFences } from "./evidence.mjs";

function esc(s) {
  return String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

/** Turn manager notes / ticket goals into readable HTML (paragraphs + fenced code). */
export function renderStudyHtml(text) {
  const raw = String(text || "").trim();
  if (!raw) return "";
  const parts = [];
  const fence = /```([a-zA-Z0-9_-]*)[ \t]*\r?\n([\s\S]*?)```/g;
  let last = 0;
  let m;
  while ((m = fence.exec(raw))) {
    if (m.index > last) {
      parts.push(renderStudyParagraphs(raw.slice(last, m.index)));
    }
    const lang = (m[1] || "code").trim() || "code";
    const code = String(m[2] || "").replace(/\r?\n$/, "");
    const langKey = lang.toLowerCase();
    if (langKey === "html" || langKey === "html-preview") {
      parts.push(renderHtmlWindow(code, lang === "html-preview" ? "HTML preview" : "html"));
    } else {
      parts.push(
        `<span class="filepath">${esc(lang)}</span><pre><code>${esc(code)}</code></pre>`
      );
    }
    last = m.index + m[0].length;
  }
  if (last < raw.length) parts.push(renderStudyParagraphs(raw.slice(last)));
  return parts.filter(Boolean).join("\n");
}

function escAttr(s) {
  return esc(s).replace(/`/g, "&#96;");
}

/** Compact iframe + source so a reviewer sees the page, not only markup. */
export function renderHtmlWindow(code, label) {
  const src = String(code || "");
  return `<div class="html-window">
  <span class="filepath">${esc(label || "HTML preview")}</span>
  <iframe class="html-frame" sandbox="allow-same-origin" srcdoc="${escAttr(src)}" title="${esc(label || "HTML preview")}"></iframe>
</div>
<span class="filepath">${esc(label || "html")} source</span>
<pre><code>${esc(src)}</code></pre>`;
}

function renderStudyParagraphs(chunk) {
  const t = String(chunk || "").trim();
  if (!t) return "";
  return t
    .split(/\n\s*\n/)
    .map((p) => p.trim())
    .filter(Boolean)
    .map((p) => `<p>${esc(p).replace(/\r?\n/g, "<br>")}</p>`)
    .join("\n");
}

function extractSection(body, heading) {
  const re = new RegExp(
    `##\\s*${heading}\\s*\\n([\\s\\S]*?)(?=\\n##\\s+|\\s*$)`,
    "i"
  );
  const m = String(body || "").match(re);
  return (m?.[1] || "").trim();
}

function parseHistoryLines(body) {
  const raw = extractSection(body, "History");
  if (!raw) return [];
  return raw
    .split(/\r?\n/)
    .map((l) => l.replace(/^-\s*/, "").trim())
    .filter(Boolean);
}

function columnOrderMap(config) {
  const map = {};
  for (const c of config?.board?.columns || []) map[c.id] = c.order;
  if (!Object.keys(map).length) {
    Object.assign(map, {
      backlog: 0,
      doing: 1,
      testing: 2,
      done: 3,
      summarize: 4,
    });
  }
  return map;
}

export function classifyHistoryLine(line, config) {
  const order = columnOrderMap(config);
  const fail = /FAILED/i.test(line);
  const m = String(line).match(/moved\s+(\w+)\s+→\s+(\w+)/i);
  if (fail) return { kind: "fail", line };
  if (m) {
    const from = m[1].toLowerCase();
    const to = m[2].toLowerCase();
    const back = (order[to] ?? 0) < (order[from] ?? 0);
    if (back) return { kind: "bounce", line, from, to };
    return { kind: "forward", line, from, to };
  }
  return { kind: "note", line };
}

export function defaultEpicGitCommand(epicMeta) {
  const key = `${epicMeta?.id}-${epicMeta?.slug}`;
  return `cd .worktrees/${key}/_epic && git push -u origin feature/${key}`;
}

export function handoffFilePath(epicPath) {
  return path.join(epicPath, "summaries", "handoff.md");
}

export function readHandoff(epicPath) {
  const p = handoffFilePath(epicPath);
  if (!fs.existsSync(p)) return null;
  const raw = fs.readFileSync(p, "utf8");
  const fm = String(raw).match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n?([\s\S]*)$/);
  if (!fm) return { git: "", note: String(raw).trim(), recorded_at: "" };
  let git = "";
  let recorded_at = "";
  try {
    const meta = parseYaml(fm[1]);
    git = String(meta.git || "").trim();
    recorded_at = String(meta.recorded_at || "").trim();
  } catch {
    const g = fm[1].match(/^git:\s*(.*)$/m);
    git = (g?.[1] || "").replace(/^["']|["']$/g, "").trim();
  }
  return { git, note: fm[2].trim(), recorded_at };
}

export function writeHandoffFile(epicPath, { git, note }) {
  const dir = path.join(epicPath, "summaries");
  fs.mkdirSync(dir, { recursive: true });
  const gitLine = String(git || "").replace(/\r?\n/g, " ").trim();
  const body = `---\ngit: ${JSON.stringify(gitLine)}\nrecorded_at: ${new Date().toISOString()}\n---\n\n${String(note || "").trim()}\n`;
  fs.writeFileSync(path.join(dir, "handoff.md"), body, "utf8");
}

export function readBoardLog(epicPath) {
  const logPath = path.join(epicPath, "board", "_history", "log.jsonl");
  if (!fs.existsSync(logPath)) return [];
  return fs
    .readFileSync(logPath, "utf8")
    .split(/\r?\n/)
    .filter(Boolean)
    .map((line) => {
      try {
        return JSON.parse(line);
      } catch {
        return null;
      }
    })
    .filter(Boolean);
}

function defaultProfile(harnessName) {
  return {
    id: harnessName || "neutral",
    label: "private.board harness",
    tagline: "Board-driven work with clear gates and roles.",
    forWhom:
      "This page is a study guide for someone new to this harness. It does not assume you already know board columns, roles, or stack jargon. Each ticket must show the real change: compact code, the command that ran, and the result — not only a glossary copied from every other epic.",
    howToRead: [
      "Read Who this is for and Basic concepts first.",
      "Then walk each Ticket section — one issue at a time, same as agents do.",
      "Open What was built: those dark windows are this ticket’s SQL, Java, .http, or React — not placeholders.",
      "Use Verify when you want to confirm the work yourself.",
      "If a word is unclear, jump to FAQ.",
    ],
    concepts: [
      {
        term: "Epic",
        def: "A larger goal. Issues belong to one epic. Progress % rises with each column move (weighted) and reaches 100% in summarize. This HTML is the living guide for the whole epic — it grows as tickets move.",
      },
      {
        term: "Issue / ticket",
        def: "One unit of work as a Markdown file that moves across board columns. On this page each ticket is its own section.",
      },
      {
        term: "Checklist",
        def: "Ordered steps inside the issue. Agents must mark them one by one — never batch.",
      },
      {
        term: "Role",
        def: "Who may claim the issue (for example database, backend, frontend, orchestrator).",
      },
      {
        term: "Gate",
        def: "A rule that blocks the next layer until the previous proof exists (tests, HTTP 2xx, build).",
      },
      {
        term: "Evidence",
        def: "Compact fenced snippets on the ticket (## Evidence): the file that changed, the command that ran, and the result. The living HTML renders those windows. Without them every summary looks the same.",
      },
    ],
    layers: [],
    boardFlow: [
      "backlog — waiting to be claimed",
      "doing — implementation only (no verification section yet)",
      "testing — local verification and harness flags",
      "done — work accepted; wait here until the whole epic is done",
      "summarize — epic wrap-up: beginner HTML already grew along the way; this column records the git command and Supervisor / Manager note",
    ],
    faq: [
      {
        q: "How is work time counted?",
        a: "Active time only counts while the board sees work (claim, ping, checklist, move). If nothing happens for a few minutes — for example waiting for a Run / Approve click — that wait is not counted. Overnight and parked-in-done time are not counted. The page shows hours and minutes from first claim until the git command and Supervisor / Manager note.",
      },
      {
        q: "Why is there a summarize column?",
        a: "The HTML starts earlier (a living work log). Summarize is the wrap-up: every ticket is done, the git command and Supervisor / Manager note are recorded, and a beginner can read the whole story including retries.",
      },
      {
        q: "What if testing fails?",
        a: "Move the ticket back to doing, fix it, then move to testing again. Those bounce-backs stay in this page so a reviewer sees real work, not only the happy path.",
      },
      {
        q: "Why one HTML file per epic?",
        a: "An epic is one story. Tickets become sections so you read one guide instead of many nearly identical pages.",
      },
      {
        q: "Do I need the optional UI?",
        a: "No. The board of record is files under .linear/. The UI only visualizes them.",
      },
      {
        q: "Why is there one folder per ticket?",
        a: "After claim, worktree-add creates .worktrees/{n}-{slug}/{ISSUE_ID} on branch ticket/{n}-{slug}/{ISSUE_ID}. The board list and those folders must match — three tickets means three folders. worktree-add starts from a committed dependency (including other epics). Git cannot name a ticket branch feature/{n}-{slug}/{ISSUE_ID} while the epic branch is feature/{n}-{slug} (one name would be both a file and a directory).",
      },
      {
        q: "When do ticket branches become one feature branch?",
        a: "After every ticket is in done: agents already committed each ticket folder and merged into feature/{n}-{slug} (worktree-integrate). Smoke the combined tree, then summarize. You only git push that epic branch — not a push per ticket. The living HTML explains that merges already ran.",
      },
      {
        q: "What if I get lost?",
        a: "Re-read Basic concepts, then the ticket sections. Open the issue Markdown if you need the exact wording agents used.",
      },
      {
        q: "Why do some summaries look identical?",
        a: "That happens when agents skip Evidence. After each checklist item they must record compact code + command + result (`board.mjs evidence`). This page then shows those windows so a beginner and a senior can both see what actually shipped.",
      },
    ],
  };
}

export async function resolveSummaryProfile(harness) {
  const base = defaultProfile(harness?.name || "neutral");
  if (typeof harness?.summaryProfile === "function") {
    return { ...base, ...harness.summaryProfile() };
  }
  return base;
}

function css() {
  return `:root{
  --green900:#0f3d2e; --green700:#1b5e42; --green500:#2e8b63;
  --cream50:#f7f4ec; --cream100:#efe9dc; --copper600:#b06a2c;
  --ink900:#1c2622; --ink600:#4a5852; --danger:#a23b2d;
  --code-bg:#0f1a15; --code-fg:#e8f0ea;
}
*{box-sizing:border-box;}
html{-webkit-text-size-adjust:100%; scroll-behavior:smooth;}
body{
  font-family:"Segoe UI",system-ui,-apple-system,BlinkMacSystemFont,sans-serif;
  line-height:1.65; color:var(--ink900); background:var(--cream50);
  margin:0; font-size:16px; overflow-x:hidden;
}
p,li{max-width:72ch;}
.wrap{max-width:100%; margin:0 auto; padding:10px 14px calc(56px + env(safe-area-inset-bottom,0px));}
header.hero{
  background:linear-gradient(135deg,var(--green900),var(--green700));
  color:#fff; border-radius:14px; padding:22px 18px; margin-bottom:18px;
  box-shadow:0 4px 16px rgba(15,61,46,.14);
}
header.hero h1{margin:0 0 8px; font-size:1.35rem; font-weight:800; letter-spacing:-.3px;}
header.hero p{margin:4px 0; opacity:.92; font-size:.94rem;}
.badge{display:inline-block; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3);
  padding:3px 10px; border-radius:999px; font-size:.76rem; margin:0 6px 6px 0;}
h2{color:var(--green900); font-weight:800; margin-top:28px; font-size:1.22rem;
  border-bottom:2px solid var(--cream100); padding-bottom:6px;}
h3{color:var(--green700); font-weight:700; margin-top:20px; font-size:1.02rem;}
a{color:var(--green700);}
.card{background:#fff; border:1px solid var(--cream100); border-radius:12px;
  padding:16px; margin:12px 0; box-shadow:0 1px 4px rgba(15,61,46,.06);}
.step{border-left:5px solid var(--green500);}
.note{border-left:5px solid var(--copper600); background:#fdf7f0;}
.warn{border-left:5px solid var(--danger); background:#fbf0ee;}
.ok{border-left:5px solid var(--green500); background:#f0f7f3;}
.beginner p{margin:10px 0;}
.beginner p:first-of-type{margin-top:0;}
code{background:var(--cream100); padding:1px 6px; border-radius:5px; font-size:.9em;
  font-family:"SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace; color:var(--green900);}
pre{background:var(--code-bg); color:var(--code-fg); padding:14px; border-radius:10px;
  overflow-x:auto; font-size:.79rem; line-height:1.5;
  font-family:"SFMono-Regular",Consolas,"Liberation Mono",Menlo,monospace;}
pre code{background:none; color:inherit; padding:0;}
.filepath{display:block; background:var(--green900); color:#cfe6da; font-size:.78rem;
  padding:6px 12px; border-radius:8px 8px 0 0; margin-bottom:-8px; font-family:Consolas,monospace;}
.filepath + pre{border-top-left-radius:0; border-top-right-radius:0;}
.html-window{margin:12px 0;}
.html-frame{display:block;width:100%;height:240px;border:1px solid var(--cream100);border-radius:0 0 10px 10px;background:#fff;}
.html-window .filepath + .html-frame{margin-top:0;}
table{width:100%; border-collapse:collapse; margin:14px 0; background:#fff;}
th,td{border:1px solid var(--cream100); padding:10px 11px; text-align:left; font-size:.86rem;}
th{background:var(--green700); color:#fff; font-weight:700;}
tr:nth-child(even){background:var(--cream50);}
.table-scroll{overflow-x:auto; -webkit-overflow-scrolling:touch; margin:14px 0;}
.table-scroll table{min-width:480px; margin:0;}
.toc{columns:1;}
.toc a{
  display:block; padding:13px 14px; margin-bottom:8px;
  background:var(--cream50); border:1px solid var(--cream100);
  border-radius:10px; text-decoration:none; font-size:.94rem;
}
.faq dt{font-weight:700; color:var(--green900); margin-top:14px;}
.faq dd{margin:6px 0 0 0; color:var(--ink600);}
.checklist{list-style:none; padding-left:0;}
.checklist li{padding:6px 0 6px 30px; position:relative;}
.checklist li::before{content:"\\2611"; position:absolute; left:0; font-size:1.2rem; color:var(--green700);}
.checklist li.pending::before{content:"\\2610";}
.pill{font-size:.72rem; background:var(--green500); color:#fff; padding:1px 8px; border-radius:999px;}
.pill-bounce{background:var(--danger);}
.pill-fail{background:var(--danger);}
.hist-bounce,.hist-fail{color:var(--danger);}
.note-pre{white-space:pre-wrap; font-family:inherit; margin:0;}
.small{font-size:.85rem; color:var(--ink600);}
.diagram{background:var(--cream50); border:1px dashed var(--cream100); padding:14px; border-radius:8px;
  font-family:monospace; font-size:.74rem; white-space:pre; overflow-x:auto; line-height:1.5;}
.mobile-jump{
  display:flex; gap:8px; position:sticky; top:0; z-index:20;
  background:rgba(247,244,236,.94); backdrop-filter:blur(8px);
  padding:8px 0 10px; margin:-10px -14px 14px; padding-left:14px; padding-right:14px;
  border-bottom:1px solid var(--cream100);
}
.mobile-jump a{
  flex:1; text-align:center; padding:10px 8px;
  background:#fff; border:1px solid var(--cream100); border-radius:10px;
  text-decoration:none; font-size:.82rem; font-weight:600; color:var(--green900);
}
.ticket{border-top:3px solid var(--green500); padding-top:8px; margin-top:36px;}
hr{border:none; border-top:1px solid var(--cream100); margin:30px 0;}
@media (prefers-reduced-motion:reduce){html{scroll-behavior:auto;}}
@media (min-width:900px){
  body{
    font-size:17px; line-height:1.7;
    background:linear-gradient(180deg,#ddd8cb 0%,#e8e3d8 80px,#ebe6db 100%);
    min-height:100vh;
  }
  .mobile-jump{display:none;}
  .wrap{
    max-width:920px; margin:32px auto 72px; padding:52px 60px 64px;
    background:#fff; border-radius:10px;
    box-shadow:0 1px 2px rgba(15,61,46,.04),0 8px 32px rgba(15,61,46,.10),0 0 0 1px rgba(15,61,46,.05);
  }
  header.hero{border-radius:18px; padding:36px 40px; margin-bottom:32px;}
  header.hero h1{font-size:2rem; letter-spacing:-.6px;}
  header.hero p{font-size:1rem;}
  .badge{font-size:.8rem; margin-bottom:0;}
  h2{font-size:1.45rem; margin-top:44px; padding-bottom:10px;}
  h3{font-size:1.12rem; margin-top:28px;}
  .card{padding:22px 26px; margin:18px 0; border-radius:14px;}
  pre{font-size:.88rem; padding:18px 20px;}
  .diagram{font-size:.84rem; padding:20px;}
  .toc{columns:2; gap:28px;}
  .toc a{padding:3px 0; margin-bottom:0; background:none; border:none; border-radius:0; font-size:inherit;}
  .toc a:hover{text-decoration:underline;}
  .table-scroll{overflow:visible;}
  .table-scroll table{min-width:0; width:100%;}
  th,td{padding:10px 14px; font-size:.93rem;}
}`;
}

function renderIssueSection(issue, profile, config, issueTime, epicKey) {
  const meta = issue.meta || {};
  const body = issue.body || "";
  const objective =
    extractSection(body, "Objetivo") ||
    extractSection(body, "Objective") ||
    meta.title ||
    "";
  const { items, total, done } = checklistSummary(body);
  const history = parseHistoryLines(body).map((h) =>
    classifyHistoryLine(h, config)
  );
  const bounceCount = history.filter((h) => h.kind === "bounce" || h.kind === "fail")
    .length;
  const anchor = esc(meta.id || "ticket");

  const checklistHtml = items.length
    ? items
        .map(
          (it) =>
            `<li class="${it.done ? "" : "pending"}"><strong>Step ${it.index}.</strong> ${esc(it.text)}${it.done ? "" : ' <span class="pill">pending</span>'}</li>`
        )
        .join("\n")
    : "<li class=\"pending\">No checklist items were found on this issue.</li>";

  const historyLis = history.length
    ? history
        .map((h) => {
          const cls =
            h.kind === "bounce"
              ? "hist-bounce"
              : h.kind === "fail"
                ? "hist-fail"
                : "";
          const pill =
            h.kind === "bounce"
              ? ' <span class="pill pill-bounce">returned</span>'
              : h.kind === "fail"
                ? ' <span class="pill pill-fail">failed</span>'
                : "";
          return `<li class="${cls}"><code>${esc(h.line)}</code>${pill}</li>`;
        })
        .join("\n")
    : "<li><em>No history lines yet.</em></li>";

  const roleHints =
    profile.roleExplain?.[meta.role] ||
    "This role owns a slice of the harness. The checklist is the contract for what was supposed to change.";

  const verifyList =
    (profile.verifyHints &&
      (profile.verifyHints[meta.role] || profile.verifyHints.default)) || [
      "Open the issue Markdown under the epic board folder.",
      "Confirm checklist items match what you expect.",
      "If this role had harness flags (tests_ok / http_ok / build_ok), they should be true in frontmatter.",
    ];
  const verifyHints = verifyList.map((x) => `<li>${esc(x)}</li>`).join("\n");
  const verifyCmds = (profile.verifyCommands?.[meta.role] || [])
    .map(
      (cmd) =>
        `<span class="filepath">copy-paste</span><pre><code>${esc(cmd)}</code></pre>`
    )
    .join("\n");

  const evidenceBody = extractEvidence(body);
  const expectedWindows = profile.evidenceWindows?.[meta.role] ||
    profile.evidenceWindows?.default ||
    [];
  const expectedHtml = expectedWindows.length
    ? `<ul>${expectedWindows.map((w) => `<li>${esc(w)}</li>`).join("\n")}</ul>`
    : "";
  const builtHtml = hasEvidenceFences(body)
    ? renderStudyHtml(evidenceBody)
    : `<div class="card warn">
  <p><strong>No layer evidence yet.</strong> This ticket has board history but no compact code/command/result windows. A beginner cannot see what changed; a senior cannot audit the layer. After each checklist item record evidence:</p>
  <pre><code>node scripts/board.mjs evidence ${esc(meta.id || "ID")} --title "…" --path "file" --lang sql --code-file … --command "…" --result-file …</code></pre>
  ${expectedHtml ? `<p class="small">Expected windows for role <code>${esc(meta.role || "—")}</code>:</p>${expectedHtml}` : ""}
</div>`;

  const statusPill =
    issue.column === "summarize"
      ? `<span class="pill">summarize</span>`
      : `<span class="pill" style="background:var(--copper600)">column:${esc(issue.column || "—")}</span>`;
  const retryPill =
    bounceCount > 0
      ? ` <span class="pill pill-bounce">${bounceCount} return${bounceCount === 1 ? "" : "s"}</span>`
      : "";

  return `
<section class="ticket" id="${anchor}">
  <h2>${esc(meta.id)} — ${esc(meta.title || "Ticket")}</h2>
  <p>${statusPill}${retryPill} <span class="badge" style="background:var(--cream100);color:var(--green900);border-color:var(--cream100)">role:${esc(meta.role || "—")}</span>${meta.agent ? ` · agent <code>${esc(meta.agent)}</code>` : ""}${issueTime?.active_label ? ` · active <strong>${esc(issueTime.active_label)}</strong>` : ""}</p>
  <p class="small">Work folder (required after claim): <code>.worktrees/${esc(epicKey || "n-slug")}/${esc(meta.id)}</code> · ticket branch <code>ticket/${esc(epicKey || "n-slug")}/${esc(meta.id)}</code> · ships on <code>feature/${esc(epicKey || "n-slug")}</code> after integrate (one push).</p>

  <h3>Goal</h3>
  <div class="card ok">
    ${renderStudyHtml(objective) || "<p><em>No objective section — title is the goal signal.</em></p>"}
    <p class="small">Checklist progress: <strong>${done}/${total}</strong>.</p>
  </div>

  <h3>Role</h3>
  <div class="card note">
    <p>${esc(roleHints)}</p>
  </div>

  <h3>Checklist</h3>
  <div class="card">
    <ul class="checklist">
${checklistHtml}
    </ul>
  </div>

  <h3>What was built (this ticket)</h3>
  ${
    hasEvidenceFences(body)
      ? `<div class="card ok beginner">
  ${expectedHtml ? `<p class="small">Windows this role should include:</p>${expectedHtml}` : ""}
  ${builtHtml}
</div>`
      : builtHtml
  }

  <h3>Board history</h3>
  <div class="card">
    <p class="small">Forward moves, checklist marks, and returns (for example testing → doing when verification fails) all stay here. This is not a substitute for the code windows above.</p>
    <ul>
${historyLis}
    </ul>
  </div>

  <h3>How you can verify this ticket</h3>
  <div class="card step">
    <ul>
${verifyHints}
    </ul>
    ${verifyCmds}
  </div>
</section>`;
}

/**
 * Living beginner HTML for the whole epic.
 * Every ticket is a section (any column). Handoff (git + Supervisor note) is the wrap-up.
 */
export function buildEpicSummaryHtml(opts) {
  const {
    issues,
    epicMeta,
    profile,
    config,
    relativeHtmlPath,
    boardLog = [],
    handoff = null,
    clock = null,
  } = opts;
  const harnessLabel = profile.label || config.harness || "harness";
  const generatedAt = new Date().toISOString();
  const epicTitle = epicMeta?.title || epicMeta?.slug || "Epic";
  const epicId = epicMeta?.id ?? "";
  const epicKey = `${epicId}-${epicMeta?.slug || ""}`;
  const list = issues || [];
  const summarized = list.filter((i) => i.column === "summarize");
  const inProgress = list.filter((i) => i.column !== "summarize");
  const allSummarized = list.length > 0 && inProgress.length === 0;
  const gitCmd = (handoff?.git || "").trim() || defaultEpicGitCommand(epicMeta);
  const handoffReady = Boolean(handoff?.note);

  const conceptRows = (profile.concepts || [])
    .map((c) => `<p><strong>${esc(c.term)}</strong> — ${esc(c.def)}</p>`)
    .join("\n");

  const layerRows = (profile.layers || [])
    .map(
      (l) =>
        `<tr><td><code>${esc(l.path || l.name)}</code></td><td>${esc(l.why)}</td></tr>`
    )
    .join("\n");

  const flowDiagram = (profile.boardFlow || [])
    .map((line, i) => `${i === 0 ? "" : "  → "}${line}`)
    .join("\n");

  const howTo = (profile.howToRead || [])
    .map((x) => `<li>${esc(x)}</li>`)
    .join("\n");

  const faqHtml = (profile.faq || [])
    .map((f) => `<dt>${esc(f.q)}</dt><dd>${esc(f.a)}</dd>`)
    .join("\n");

  const tocTickets = list
    .map(
      (i) =>
        `<a href="#${esc(i.meta.id)}">${esc(i.meta.id)} — ${esc(i.meta.title || "")} <span class="small">(${esc(i.column)})</span></a>`
    )
    .join("\n");

  const ticketSections = list
    .map((i) =>
      renderIssueSection(i, profile, config, clock?.issues?.[i.meta.id], epicKey)
    )
    .join("\n");

  const logRows = (boardLog || [])
    .map((ev) => {
      const bounce =
        ev.type === "move" &&
        classifyHistoryLine(
          `moved ${ev.from || ""} → ${ev.to || ""}`,
          config
        ).kind === "bounce";
      const fail = ev.type === "fail";
      const cls = bounce || fail ? "hist-bounce" : "";
      const label = fail
        ? `${ev.issueId} failed → ${ev.reason || "backlog"}`
        : ev.type === "move"
          ? `${ev.issueId} ${ev.from} → ${ev.to}${ev.reason ? ` (${ev.reason})` : ""}`
          : ev.type === "claim"
            ? `${ev.issueId} claimed by ${ev.agent}`
            : ev.type === "handoff"
              ? "Supervisor / Manager note recorded"
              : `${ev.type || "event"} ${ev.issueId || ""}`.trim();
      return `<li class="${cls}"><code>${esc(ev.at || "")}</code> ${esc(label)}</li>`;
    })
    .join("\n");

  const stageBadge = allSummarized
    ? handoffReady
      ? "summarize · handoff recorded"
      : "summarize · handoff pending"
    : "living summary";

  const handoffHtml = handoffReady
    ? `<div class="card ok" id="handoff-body">
  <strong>Supervisor / Manager</strong>
  ${renderStudyHtml(handoff.note)}
</div>
<span class="filepath">copy-paste git (proposed — human runs it)</span>
<pre><code>${esc(gitCmd)}</code></pre>
${handoff.recorded_at ? `<p class="small">Recorded ${esc(handoff.recorded_at)}</p>` : ""}`
    : `<div class="card warn">
  <p>The Supervisor / Manager note is not on the board yet. When every ticket is in <code>summarize</code>, record it so this page can track the epic:</p>
  <pre><code>node scripts/board.mjs handoff ${esc(String(epicId))} --git ${esc(JSON.stringify(gitCmd))} --note-file summaries/handoff-note.txt</code></pre>
  <p class="small">Until then, the suggested git line is:</p>
  <pre><code>${esc(gitCmd)}</code></pre>
</div>`;

  return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Epic ${esc(String(epicId))} — ${esc(epicTitle)} — Living summary</title>
<style>
${css()}
</style>
</head>
<body>
<div class="wrap">

<nav class="mobile-jump" aria-label="Quick jumps">
  <a href="#toc">Index</a>
  <a href="#tickets">Tickets</a>
  <a href="#handoff">Handoff</a>
  <a href="#faq">FAQ</a>
</nav>

<header class="hero">
  <div>
    <span class="badge">Epic ${esc(String(epicId))}</span>
    <span class="badge">${esc(epicKey)}</span>
    <span class="badge">${esc(harnessLabel)}</span>
    <span class="badge">${esc(stageBadge)}</span>
  </div>
  <h1>${esc(epicTitle)}</h1>
  <p>This page is a study guide for the epic. Tickets must show <strong>this work’s</strong> compact code, commands, and results — not the same glossary copied from every other summary. Dark windows are artifacts to study.</p>
  <p class="small" style="opacity:.85;">Slug <code>${esc(epicMeta?.slug || "")}</code> · ${summarized.length}/${list.length} in summarize · Generated ${esc(generatedAt)}${clock?.active_label ? ` · active work <strong>${esc(clock.active_label)}</strong> (elapsed ${esc(clock.wall_label || "—")})` : ""}</p>
</header>

${
  `<div class="card ${clock?.started_at ? "ok" : "note"}" id="time">
  <strong>Active work time</strong>
  <p>Counted only while the board sees work. Waiting for a Run / Approve click, overnight, or parked-in-done time is not included.</p>
  ${
    clock?.started_at
      ? `<ul>
    <li>Active (calendar of working windows): <strong>${esc(clock.active_label)}</strong></li>
    <li>Agent effort (sum of tickets): <strong>${esc(clock.effort_label)}</strong></li>
    <li>Elapsed wall (first work → last event${clock.finished_at ? " / handoff" : ""}): <strong>${esc(clock.wall_label)}</strong></li>
  </ul>
  <p class="small">Started ${esc(clock.started_at || "—")}${clock.finished_at ? ` · finished ${esc(clock.finished_at)}` : ""} · idle gap ${Math.round((clock.idle_ms || 0) / 60000)} min</p>`
      : `<p class="small">No active time yet. The clock starts on the first claim.</p>`
  }
</div>`
}

<div class="card beginner" id="for-whom">
  <strong>Who this is for</strong>
  <p>${esc(profile.forWhom)}</p>
</div>

<div class="card note" id="how">
  <strong>How to read this page</strong>
  <ul>${howTo}</ul>
</div>

<div class="card" id="toc">
  <h3 style="margin-top:0">Index</h3>
  <div class="toc">
    <a href="#for-whom">Who this is for</a>
    <a href="#concepts">Basic concepts</a>
    <a href="#board">Board flow</a>
    ${layerRows ? '<a href="#layers">Stack layers</a>' : ""}
    <a href="#time">Active time</a>
    <a href="#log">Epic work log</a>
    <a href="#tickets">Tickets (sections)</a>
    ${tocTickets}
    <a href="#handoff">Git command + Supervisor / Manager</a>
    <a href="#faq">FAQ for beginners</a>
    <a href="#file">Where this file lives</a>
  </div>
</div>

<h2 id="concepts">Basic concepts (read once)</h2>
<div class="card beginner">
${conceptRows || "<p>No extra glossary for this harness.</p>"}
</div>

<h2 id="board">Board flow</h2>
<div class="card">
  <div class="diagram">${esc(flowDiagram)}</div>
  <p class="small">This page refreshes on claim, checklist, fail, and every column move (including testing → doing). Epic % is column + checklist and reaches 100% when every issue is in <code>summarize</code> with its checklist complete.</p>
</div>

${
  layerRows
    ? `<h2 id="layers">Stack layers (this harness)</h2>
<div class="table-scroll">
<table>
  <tr><th>Layer / path</th><th>Why it exists</th></tr>
  ${layerRows}
</table>
</div>
<p class="small">${esc(profile.layersNote || "Work must respect layer order. Never invert the chain.")}</p>`
    : ""
}

<h2 id="log">Epic work log</h2>
<div class="card">
  <p class="small">Board-wide timeline (same events as <code>board/_history/log.jsonl</code>). Returns to an earlier column are marked.</p>
  <ul>
${logRows || "<li><em>No board events yet.</em></li>"}
  </ul>
</div>

<hr>

<h2 id="tickets">Tickets (sections)</h2>
<p class="small">Every ticket in this epic — not only those already in <code>summarize</code>.</p>
${ticketSections || '<div class="card warn"><p>No tickets yet.</p></div>'}

<h2 id="handoff">Git command + Supervisor / Manager</h2>
<p class="small">Recorded on the board at wrap-up so the SUMMARIZE column tracks the same git line and note the agent proposed.</p>
${handoffHtml}

<h2 id="faq">FAQ for beginners</h2>
<div class="card">
  <dl class="faq">
${faqHtml}
  </dl>
</div>

<h2 id="file">Where this file lives</h2>
<div class="card note">
  <p>Board path (relative to the private.board root):</p>
  <pre><code>${esc(relativeHtmlPath)}</code></pre>
  <p class="small">Refreshes on board moves. Wrap-up: <code>node scripts/board.mjs handoff ${esc(String(epicId))} --git "…" --note-file …</code> then <code>summarize-epic ${esc(String(epicId))}</code>.</p>
</div>

</div>
</body>
</html>
`;
}

export function epicSummaryRelativePath(epicMeta) {
  const id = epicMeta?.id ?? "epic";
  const slug = String(epicMeta?.slug || "epic")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
  return path.join("summaries", `${id}-${slug}-beginner.html`);
}

/** @deprecated Use epicSummaryRelativePath — kept for older docs. */
export function summaryRelativePath(issueId) {
  const safe = String(issueId).replace(/[^\w.-]+/g, "_");
  return path.join("summaries", `${safe}-beginner.html`);
}

/**
 * Writes one epic living HTML under epic/summaries/ and returns { absPath, relativePath }.
 * Pass all issues in the epic (any column). Refreshes from first claim / move.
 */
export async function writeEpicSummary({
  config,
  harness,
  epicPath,
  epicMeta,
  issues,
}) {
  const profile = await resolveSummaryProfile(harness);
  const relativePath = epicSummaryRelativePath(epicMeta).replace(/\\/g, "/");
  const absPath = path.join(epicPath, relativePath);
  fs.mkdirSync(path.dirname(absPath), { recursive: true });
  const html = buildEpicSummaryHtml({
    issues: issues || [],
    epicMeta,
    profile,
    config,
    boardLog: readBoardLog(epicPath),
    handoff: readHandoff(epicPath),
    clock: clockTotals(readClock(epicPath, config)),
    relativeHtmlPath: path
      .join("epics", path.basename(epicPath), relativePath)
      .replace(/\\/g, "/"),
  });
  fs.writeFileSync(absPath, html, "utf8");
  const metaPath = path.join(epicPath, "meta.yaml");
  let meta = { ...(epicMeta || {}) };
  if (fs.existsSync(metaPath)) {
    try {
      meta = { ...meta, ...parseYaml(fs.readFileSync(metaPath, "utf8")) };
    } catch {
      /* keep epicMeta */
    }
  }
  meta.summary_html = relativePath;
  meta.summary_updated_at = new Date().toISOString();
  fs.writeFileSync(metaPath, stringifyYaml(meta) + "\n", "utf8");
  return { absPath, relativePath };
}

/**
 * Compatibility wrapper: regenerates the epic HTML (not a per-issue file).
 */
export async function writeIssueSummary({
  config,
  harness,
  issue,
  epicPath,
  epicMeta,
  issues,
}) {
  return writeEpicSummary({
    config,
    harness,
    epicPath,
    epicMeta,
    issues: issues || [issue],
  });
}
