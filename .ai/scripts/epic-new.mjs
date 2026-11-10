#!/usr/bin/env node
/**
 * Create an Epic = journey (full narrative) + .linear board (issues = tickets).
 * Two pillars only. Lives under .ai/ (agent control plane).
 *
 *   node .ai/scripts/epic-new.mjs <n> <slug> --title "…"
 */
import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";
import { spawnSync } from "child_process";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, "../..");
const LINEAR = path.join(ROOT, ".linear");

function flag(argv, name) {
  const i = argv.indexOf(name);
  if (i === -1) return null;
  return argv[i + 1] ?? true;
}

const argv = process.argv.slice(2);
const n = argv[0];
const slug = argv[1];
const title = flag(argv, "--title") || `${n} — ${slug}`;

if (!n || !slug) {
  console.error('Usage: node .ai/scripts/epic-new.mjs <n> <slug> --title "…"');
  process.exit(1);
}

const board = spawnSync(
  process.execPath,
  [
    path.join(LINEAR, "scripts", "board.mjs"),
    "epic",
    "create",
    String(n),
    slug,
    "--title",
    title,
  ],
  { cwd: LINEAR, encoding: "utf8" }
);
if (board.stdout) process.stdout.write(board.stdout);
if (board.stderr) process.stderr.write(board.stderr);
if (board.status !== 0) process.exit(board.status ?? 1);

const journeyDir = path.join(ROOT, ".docs", "journey");
fs.mkdirSync(journeyDir, { recursive: true });
const journeyPath = path.join(journeyDir, `${n}-${slug}.md`);
if (!fs.existsSync(journeyPath)) {
  const body = `# Epic ${n} — ${slug}

**Status:** Planned · Board: \`.linear/epics/${n}-${slug}/\`

## Title

${title}

## Why

(Describe the problem / opportunity in plain language. Put **all** narrative depth here.)

## What we will do

- [ ] Clarify acceptance criteria
- [ ] Split into **board issues** under \`.linear\` (those are the only tickets)
- [ ] Implement via multi-agent board (\`doing\` only)
- [ ] Move tickets to \`testing\`, then verify (harness flags)
- [ ] Park tickets in \`done\` until every issue in the epic is done
- [ ] Then \`summarize\` (wrap-up column) and \`handoff\` (git command + Supervisor / Manager into the living HTML)

## Acceptance

- Given …
- When …
- Expect …

## Agent notes (constraints / edge cases)

(Anything agents need that is not a checklist step: constraints, links, non-goals, risks. Keep it here — there is no \`.ai/tickets\` folder.)

## Layers touched

| Layer | Touched? | Notes |
|---|---|---|
| \`0.database\` | | SQL; DBML twin in \`3.diagrams/0.database/dbdiagram/\` |
| \`0.security\` | | MU-plugin / hardening verify |
| \`1.plugin\` | | Native WP plugin layers; PlantUML in \`3.diagrams/1.plugin/\` |
| \`3.diagrams\` | | Visuals only — do not duplicate under 0/1 |

## Board issues (= tickets)

Work lives only on the board. Create issues:

\`\`\`bash
node .linear/scripts/board.mjs issue create ${n} --title "DB · …" --role database
node .linear/scripts/board.mjs issue create ${n} --title "Plugin · …" --role plugin --blocked-by APP-${n}-1
node .linear/scripts/board.mjs issue create ${n} --title "Security · …" --role security --blocked-by APP-${n}-2
\`\`\`

Each issue Markdown holds its own sequential checklist. Do not copy checklists into this journey.

## How to verify

\`\`\`bash
node .linear/scripts/board.mjs progress --epic ${n}
# column + checklist %; 100% when all issues are in summarize with checklists complete
\`\`\`

## Outcome

(Filled when epic reaches 100%.)
`;
  fs.writeFileSync(journeyPath, body, "utf8");
  console.log(`Created journey ${path.relative(ROOT, journeyPath)}`);
} else {
  console.log(`Journey already exists: ${path.relative(ROOT, journeyPath)}`);
}

console.log(
  "\nEpic ready. Journey = full narrative; .linear issues = tickets; living HTML from first claim/move; summarize + handoff = wrap-up."
);
console.log(
  "Snapshot (tracked): .docs/board/" +
    n +
    "-" +
    slug +
    "/  — commit journeys + that folder on the working branch."
);
