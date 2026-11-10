# Skill — land epic branches onto `main`

Use when `worktree-origin-ready` is clean (`originReady: true`) and product work must land on `main` end-to-end. Billable: land clock. Human-facing: living HTML land section + journey update.

## Goal

Merge `origin/feature/{n}-{slug}` into `main` in epic order, resolve conflicts, cumulative-smoke, record Supervisor / Manager land note, push `main` (never `--force`), freeze land time.

## Procedure

1. `node .ai/scripts/worktree-origin-ready.mjs` — must be `originReady: true`.
2. Park non-persist dirty files on the primary `main` checkout (process WIP). Untracked `.docs/board` / journey **files** that the merge would overwrite are unlinked by `land-main` (they arrive via merge). Every other colliding dirty or untracked path makes `land-main` refuse and stay on disk; it never deletes folders. It also refuses a feature branch that adds an undeclared gitlink (nested clone): fix on the feature branch, push, re-run.
3. `node .ai/scripts/worktree-land-next.mjs` — lands the lowest epic not yet `landed`.
4. `node .ai/scripts/worktree-land-main.mjs <n>` — starts land clock, merges `--no-ff`. On conflicts: resolve on `main`, `git add`, `git commit`, set status `smoke-main` (or re-run land-main after fix).
5. `node .ai/scripts/worktree-land-smoke.mjs <n>` — follow journey verify for this epic **and** keep prior landed epics healthy (DDEV WP: health / protected REST / plugin smoke). Mark `--pass` when OK. The smoke also runs the layer audit on `main`; on violations it records `smoke_ok=false` and exits 1 (fix on `main`, commit, re-run).
6. Write a short Supervisor / Manager land note (English, first person, “You can confirm it doing the following:” + smoke steps).
7. `node .ai/scripts/worktree-land-finish.mjs <n> --note-file path/to/note.txt` — board land hygiene (tickets → **`summarize`**, epic **% = 100%**), HTML land section, journey Status, freeze clock, **`git push origin main`**. This is the **only** step that may set board **LANDED**. If hygiene fails (open tickets) or push fails, status stays `smoke-main` — fix and re-run. Never hand-edit `land.json` to `landed`.
8. Repeat from step 3 until `worktree-land-ready` is true.

## Board / HTML / time

| Surface | Update |
|---|---|
| `summaries/land.json` | `on-origin` → `landing` → `smoke-main` → `landed` |
| Living HTML | **Land on main** after build handoff |
| Journey | **Status: Landed on main** + land outcome |
| `summaries/time.json` → `land` | Active + elapsed for Hours worked |

## Do not

- Merge out of order
- Skip smoke or skip the human land summary
- `git push --force`
- Invent APP ticket columns for land
- Delete untracked non-persist folders to make a merge fit
- Call an epic **LANDED** after local merge only (must be on GitHub `origin/main`)
- Accept **LANDED + % &lt; 100%** (tickets stuck in `done`/`backlog`) — hygiene must promote to `summarize`

Details: `.ai/rules/epic-land-main.md` · `.ai/rules/landed-means-origin-main.md` · spec: `.ai/specs/epic-land-main.md`
