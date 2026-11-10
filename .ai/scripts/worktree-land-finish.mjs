#!/usr/bin/env node
/**
 * Finish land: living HTML land section, journey Status, freeze land clock, push main.
 *
 *   node .ai/scripts/worktree-land-finish.mjs 1 --note-file summaries/land-note.txt
 *   node .ai/scripts/worktree-land-finish.mjs 1 --note "…"
 *   node .ai/scripts/worktree-land-finish.mjs 1 --note-file … --no-push
 */
import fs from "fs";
import path from "path";
import { pathToFileURL, fileURLToPath } from "url";
import { git, gitOk } from "./lib/worktree-git.mjs";
import {
  resolveEpicKey,
  readLand,
  writeLand,
  epicPathForKey,
  updateJourneyLanded,
  pushMain,
} from "./lib/land-main.mjs";
import { snapshotEpicToDocs } from "../../.linear/scripts/lib/board-snapshot.mjs";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const SCRIPT_ROOT = path.resolve(__dirname, "../..");

function primaryCheckout(cwd) {
  const out = git(["worktree", "list", "--porcelain"], cwd);
  const line = out.split(/\r?\n/).find((l) => l.startsWith("worktree "));
  if (!line) {
    console.error("Could not read git worktree list.");
    process.exit(1);
  }
  return line.slice("worktree ".length).trim();
}

function flag(argv, name) {
  return argv.includes(name);
}

function flagVal(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? null;
}

function writeLandHandoff(epicPath, { git: gitLine, note }) {
  const dir = path.join(epicPath, "summaries");
  fs.mkdirSync(dir, { recursive: true });
  const body = `---\ngit: ${JSON.stringify(String(gitLine || "").trim())}\nrecorded_at: ${new Date().toISOString()}\n---\n\n${String(note || "").trim()}\n`;
  fs.writeFileSync(path.join(dir, "land-handoff.md"), body, "utf8");
}

function appendLandToHtml(epicPath, epicKey, land, note) {
  const htmlName = `${epicKey}-beginner.html`;
  const htmlPath = path.join(epicPath, "summaries", htmlName);
  const section = `
<section id="land-main" class="card ok">
  <h2>Land on main</h2>
  <p><strong>Status:</strong> landed · merge <code>${land.merge_commit || ""}</code></p>
  <p><strong>Git:</strong></p>
  <pre><code>git push origin main</code></pre>
  <h3>Supervisor / Manager (land)</h3>
  <div id="land-handoff-body">
${String(note || "")
  .split(/\r?\n/)
  .map((l) => `    <p>${l.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>`)
  .join("\n")}
  </div>
  <p class="small">Recorded ${new Date().toISOString()}</p>
</section>
`;
  if (fs.existsSync(htmlPath)) {
    let html = fs.readFileSync(htmlPath, "utf8");
    if (/id="land-main"/.test(html)) {
      html = html.replace(
        /<section id="land-main"[\s\S]*?<\/section>/,
        section.trim()
      );
    } else if (/id="handoff"/.test(html)) {
      html = html.replace(
        /(<\/section>\s*)(?=<h2 id="faq"|<h2 id="verify"|<\/main>|<\/body>)/,
        `$1\n${section}\n`
      );
      // fallback append before closing body
      if (!/id="land-main"/.test(html)) {
        html = html.replace(/<\/body>/i, `${section}\n</body>`);
      }
    } else {
      html = html.replace(/<\/body>/i, `${section}\n</body>`);
    }
    fs.writeFileSync(htmlPath, html, "utf8");
    return htmlPath;
  }
  // Minimal HTML if living summary missing
  fs.mkdirSync(path.dirname(htmlPath), { recursive: true });
  fs.writeFileSync(
    htmlPath,
    `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${epicKey} land</title></head><body>${section}</body></html>\n`,
    "utf8"
  );
  return htmlPath;
}

const argv = process.argv.slice(2);
const ref = argv.find((a) => a && !String(a).startsWith("--"));
if (!ref) {
  console.error(
    "Usage: node .ai/scripts/worktree-land-finish.mjs <n|slug|{n}-{slug}> --note-file path | --note \"…\" [--no-push]"
  );
  process.exit(1);
}

const noteFile = flagVal(argv, "--note-file");
const noteInline = flagVal(argv, "--note");
let note = noteInline || "";
if (noteFile) {
  const abs = path.isAbsolute(noteFile)
    ? noteFile
    : path.join(process.cwd(), noteFile);
  if (!fs.existsSync(abs)) {
    console.error(`Note file not found: ${abs}`);
    process.exit(1);
  }
  note = fs.readFileSync(abs, "utf8");
}
if (!String(note).trim()) {
  console.error("Land finish requires --note-file or --note (human summary).");
  process.exit(1);
}

const startCwd = fs.existsSync(path.join(SCRIPT_ROOT, ".git"))
  ? SCRIPT_ROOT
  : process.cwd();

if (!gitOk(["rev-parse", "--is-inside-work-tree"], startCwd)) {
  console.error("Run this from a git checkout of the host harness.");
  process.exit(1);
}

const primary = primaryCheckout(startCwd);
const linear = path.join(primary, ".linear");
const epicKey = resolveEpicKey(linear, ref);
const epicPath = epicPathForKey(linear, epicKey);
const land = readLand(epicPath);

if (land.smoke_ok !== true) {
  console.error(
    JSON.stringify(
      {
        ok: false,
        error: `Smoke not passed for ${epicKey} (smoke_ok=${land.smoke_ok}). Run worktree-land-smoke.mjs ${epicKey} --pass`,
      },
      null,
      2
    )
  );
  process.exit(1);
}

const clockApi = await import(
  pathToFileURL(path.join(linear, "scripts", "lib", "work-clock.mjs")).href
);
let config = { time: { idle_ms: 180000 } };
try {
  const fsBoard = await import(
    pathToFileURL(path.join(linear, "scripts", "lib", "fs-board.mjs")).href
  );
  config = fsBoard.loadConfig();
} catch {
  /* default */
}

land.status = "landed";
land.note = String(note).trim();
land.git = "git push origin main";
land.recorded_at = new Date().toISOString();
land.finished_at = land.recorded_at;
writeLand(epicPath, land);
writeLandHandoff(epicPath, { git: land.git, note: land.note });

const clock = clockApi.finishLandClock(config, epicPath);
const journey = updateJourneyLanded(primary, epicKey, {
  mergeCommit: land.merge_commit,
  note: land.note,
});
const htmlPath = appendLandToHtml(epicPath, epicKey, land, land.note);
const snap = snapshotEpicToDocs(epicPath, primary);

let pushed = { skipped: true };
if (!flag(argv, "--no-push")) {
  // Journey + board snapshot belong on main after land.
  const porcelain = git(["status", "--porcelain"], primary);
  if (porcelain.trim()) {
    git(["add", "--", ".docs/journey", ".docs/board"], primary);
    const staged = git(["diff", "--cached", "--name-only"], primary);
    if (staged.trim()) {
      git(
        [
          "commit",
          "-m",
          `Land ${epicKey}: journey + board snapshot after merge`,
        ],
        primary
      );
    }
  }
  pushed = pushMain(primary);
  if (!pushed.ok) {
    console.error(
      JSON.stringify(
        { ok: false, epicKey, land, clock, journey, htmlPath, snap, pushed },
        null,
        2
      )
    );
    process.exit(1);
  }
}

console.log(
  JSON.stringify(
    {
      ok: true,
      epicKey,
      landStatus: "landed",
      merge_commit: land.merge_commit,
      clock,
      journey,
      htmlPath,
      snap,
      pushed,
      next: "node .ai/scripts/worktree-land-next.mjs",
    },
    null,
    2
  )
);
