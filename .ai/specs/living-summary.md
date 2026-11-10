# Spec — living HTML must show the work (not a cloned glossary)

Neutral harness. The beginner HTML at `summaries/{n}-{slug}-beginner.html` is the page a novice **and** a senior open to understand an epic. If every epic summary only repeats board columns, JOOQ definitions, and a manager note, the page failed.

## Failures this spec encodes

17. **Identical summaries:** HTML is generated from a harness glossary + ticket titles + board history. Agents never paste the SQL, JOOQ, I* layers, `.http` status, Axios, hooks, or page markup. **Required:** each ticket has a `## Evidence` section with compact fenced windows (code, command, result). The HTML **What was built** section renders those fences. `html` / `html-preview` fences also render a compact iframe. If there are no fences, the HTML shows a warning, not a fake lesson.

18. **Whole files dumped:** Evidence that pastes 400-line files is unreadable. **Required:** CLI `evidence` truncates snippets (about 48 lines) and labels the path. Reviewers open the repo for the rest.

## Contract (agents)

Record **every layer this ticket actually touched**, in stack order. Skip a window only if that layer was not in the ticket. Do not skip JOOQ if you regenerated it. Do not skip tests if you ran them. Do not jump from SQL to controllers.

After **each** checklist item (same turn as `checklist <ID> n`):

```bash
node .linear/scripts/board.mjs evidence <ID> --title "…" --path "file" --lang sql --code-file snippet.sql --command "…" --result-file out.txt --agent NAME
```

| Role | Windows (compact, in this order when the ticket covers them) |
|---|---|
| database | SQL (`canonical_sql`) · command (`ensure_db` / `setup_db`) · **tests / DESCRIBE result** (`tests_ok`) · DBML twin |
| backend | **JOOQ codegen command + result** · **POJO / generated field** · **IRepository** · **Repository** · **IService** · **Service** · **IController** · **Controller** · unit test snippet if `mvn test` ran · **`.http` request** · **HTTP result** (status/body; 401 then 2xx if JWT) · if it failed: what you edited and why it then passed |
| frontend | **API service** (Axios / interceptor) · **hook** · **page** (job / primary action / next click / what is hidden) · compact **HTML preview** · Mermaid if the React tree changed |
| orchestrator | journey/board snippet that defined the work |

One epic often splits these across tickets (DB ticket, BE ticket, FE ticket). Each ticket still fills **its** windows so the living HTML walks the same chain a reviewer would: database → tests → JOOQ/POJOs → interface → repository → interface → service → interface → controller → `.http` → api service → hooks → pages.

Do not wait until `summarize` to dump evidence. The living HTML refreshes on `evidence` so the page grows with the work.

## Board

- Issue template includes `## Evidence`.
- Command: `board.mjs evidence`.
- Harness `summaryProfile().evidenceWindows` lists expected windows per role.
- Smoke: `_smoke-living-summary.mjs` records a SQL evidence fence and asserts it appears in the HTML.

## Do not

- Treat board history (claimed / moved testing) as a substitute for code windows
- Log secrets, JWT compact tokens, or `.env` values
- Skip evidence because the harness glossary already “explains JOOQ”

Rule: `.ai/rules/living-summary.md` · skill: `.ai/skills/living-summary/SKILL.md`
