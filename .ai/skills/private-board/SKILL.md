# Skill — private.board

Board of record: `.linear/` only (UI brand **private.board**). **No** cloud issue-tracker API/MCP.

**Rodieja product:** process is always mandatory (`.ai/rules/board-process-mandatory.md`) — Spring-harness discipline on the WordPress stack. Keep `.docs/journey` and `.docs/board` current; never ship only on `main`.

## Flow

1. `node .linear/scripts/board.mjs next --role <role> --epic <n>`
2. `claim <ID> --agent <name>`
3. Do one checklist item, then immediately `checklist <ID> <n>` (CLI refuses skip/batch). **Same turn:** `evidence <ID>` with compact code/command/result. Repeat until implementation items are `[x]` **in `doing`**
4. `move <ID> testing` **before** any verification
5. In **testing**: remaining checklist (prove `.http` / setup / build), harness flags
6. `node .ai/scripts/worktree-finish.mjs <ID> --agent <name>` — **commit then park**. Do not summarize yet. Do not ask the human to commit.
7. When **every** ticket in the epic is in `done`, `worktree-integrate` (agents merge, then prune APP-* folders), smoke, then **`move <ID> summarize` for every ticket** + `handoff` (git line + smoke note in the living HTML), then `worktree-push`. Do not leave tickets parked in `done` after handoff — epic **%** stays below 100% until **`summarize`**. After land, `worktree-land-finish` also enforces this hygiene so LANDED cannot ship with 83%.

Claim stays through `done` so `done` → `summarize` does not require `--force`.

## Roles

`orchestrator` · `database` · `backend` · `frontend`

## Flags before `done` (harness `mariadb-spring-jooq-react`)

Set these **in the testing column**, not in doing.

| Role | Required |
|---|---|
| database | `tests_ok: true` |
| backend | `http_ok` after `.http` 2xx; `tests_ok` when unit tests were needed (failed `.http` or logic check) |
| frontend | `build_ok: true` **and** the Vite Health (or equivalent) page is not Network Error (CORS / proxy) |

## Progress (column + checklist)

Epic % rises with **every** column move **and every marked checklist item**, averaged across tickets.

Each ticket’s share is `(checklist_done + column.order) / (checklist_total + maxOrder)` where `maxOrder` is summarize (usually 4).

| Column only (empty checklist) | Weight of that ticket |
|---|---|
| backlog | 0% |
| doing | 25% |
| testing | 50% |
| done | 75% |
| summarize | 100% |

With a checklist, marking an item raises % even before the next column move. **100%** only when every issue is in `summarize` with its checklist complete. Dependents unlock at `done`. Check: `node .linear/scripts/board.mjs progress --epic <n>`.

Rule: `.ai/rules/private-board.md`

## Summarize (living HTML + wrap-up column)

- The HTML at `epics/<n>-<slug>/summaries/<n>-<slug>-beginner.html` is a **living work log**. It refreshes on claim, checklist, **evidence**, fail, and every column move. Each ticket section **What was built** must show compact code/command/result windows (`evidence <ID>`), not only a glossary. It lists **all** tickets, any column.
- Board UI: **Open living summary** (`GET /api/epic/<id>/summary`). Sidebar **Hours worked** (`GET /api/hours?month=YYYY-MM`) sums Active + Elapsed across every epic for a selected month. Sidebar brand: **private.board**.
- If verification fails: `fail <ID> --agent NAME --reason "…"` (from testing → doing, **claim kept**) or `move <ID> doing --reason "…"`. Fix in doing, then `move testing` again.
- Tickets enter column `summarize` only when **all** epic tickets are in `done`.
- Wrap-up: record the same git command + Supervisor / Manager note the agent printed in chat:

```bash
node .linear/scripts/board.mjs handoff <n> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" --note-file path/to/note.txt
```

- Regenerate HTML: `node .linear/scripts/board.mjs summarize-epic <n>`

## Active work time

The board cannot see Cursor / Claude **Run / Approve** dialogs. It counts **active** hours and minutes from the first `claim` until `handoff` (git command + Supervisor / Manager note).

| Counts | Does not count |
|---|---|
| Gaps of at most `time.idle_ms` (default 3 min) between claim, ping, checklist, move, fail, `clock pulse` | Longer gaps (approval wait, away, overnight) |
| Parallel tickets: **effort** = sum; **active** = overlapping windows merged | Parked in `done` waiting for siblings |

```bash
node .linear/scripts/board.mjs ping <ID> --agent <name>          # keep the clock alive during a long command
node .linear/scripts/board.mjs clock pause <ID> --reason "waiting for approval"
node .linear/scripts/board.mjs clock resume <ID> --agent <name>
node .linear/scripts/board.mjs clock status <n>
node .linear/scripts/board.mjs hours [--month YYYY-MM]
```

Ping immediately **before** a long install/build/test (same turn as starting it) and again when it returns. If a permission card is already on screen, the idle gap will drop that wait automatically. Stored in `summaries/time.json`; shown on the living HTML and board UI.

## Large briefs

Orchestrator creates **all** epics + tickets before implementation claims (`.ai/rules/orchestrate-first.md`).

`blocked_by` is board-wide: an issue may wait on a ticket in another epic until that dep reaches `done` (or later).

After **every** `claim`, **required** worktree: `node .ai/scripts/worktree-add.mjs <ISSUE_ID>` — shared board, never one `.linear` per folder. Implement in `.worktrees/{n}-{slug}/{ISSUE_ID}` on `ticket/{n}-{slug}/{ISSUE_ID}`. Board cards and folders must match. When every ticket is in `done`, merge into `feature/{n}-{slug}`, smoke, then summarize. Ticket commit is local only; one Supervisor / Manager note and **one** push from `_epic` (`.ai/rules/worktrees.md`).

Full guide: root `AGENTS.md` · cheatsheet: `.linear/info/agent-cheatsheet.md`

The board engine and this repo’s `.linear/` are the **board**. Stack, `3.diagrams`, PlantUML, Mermaid, and dbdiagram.io live in host `AGENTS.md` / `.ai/` — never in `.linear` docs.
