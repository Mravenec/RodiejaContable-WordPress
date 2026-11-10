---
description: private.board — filesystem only, column + checklist epic %, no cloud issue tracker
alwaysApply: true
---

# private.board + journey Epic

1. Board of record: `.linear/` via `node .linear/scripts/board.mjs` (UI brand **private.board**).
2. Epic narrative: `.docs/journey/{n}-{slug}.md` — created with `node .ai/scripts/epic-new.mjs` (full story there; board issues = tickets; no `.ai/tickets/`).
3. Never call a cloud issue-tracker API/MCP.
4. Flow: `next` → `claim` → do one checklist item → **immediately mark it** → **`evidence <ID>`** (compact code/command/result for the living HTML) → repeat in `doing` → **`move testing`** (verify + flags). If verification fails: `fail <ID> --reason "…"` (testing → doing, claim kept) or `move doing --reason "…"`, fix, then `move testing` again. When flags pass: **`worktree-finish`** (commit then park in `done`). The living HTML at `epics/{n}-{slug}/summaries/{n}-{slug}-beginner.html` refreshes on every move and evidence pulse. When **every** ticket is in `done`, agents **`worktree-integrate`** (prunes ticket folders; **fails** if leftovers remain), then **`summarize`** and **`handoff`** (git line + Supervisor / Manager smoke note into that HTML), then **`worktree-push`** (publishes `feature/{n}-{slug}`, drops `_epic` and leftover ticket folders). Work-then-mark: `.ai/rules/board-checklist-order.md`. Living evidence: `.ai/rules/living-summary.md`.
5. **Epic % is column + checklist:** each ticket contributes `(checklist_done + column.order) / (checklist_total + maxOrder)` (maxOrder is summarize, usually 4). Marking a checklist item and moving columns both raise that ticket’s share. Epic % averages those weights. **100%** only when every ticket is in `summarize` with its checklist complete. Dependents unlock at `done`. Never fake %.
6. Large briefs: create **all** epics + tickets before implementation (`.ai/rules/orchestrate-first.md`).
7. Harness: `mariadb-spring-jooq-react` in `.linear/config.yaml` (board **flags** only).
8. Read root `AGENTS.md` for stack, modules, and `3.diagrams`. **Do not** put those paths in `.linear/` or in the board engine.
9. After `claim`, **required** `node .ai/scripts/worktree-add.mjs <ISSUE_ID>` (`.worktrees/{n}-{slug}/{ISSUE_ID}` on `ticket/{n}-{slug}/{ISSUE_ID}`). One board ticket = one folder. The live board is the **primary** `.linear/epics` (worktrees may junction it). Unlink those junctions before prune. Tracked snapshot: `.docs/board/{n}-{slug}/` (`.ai/rules/board-persist.md`). Epic rows on the UI show **`{n}-{slug}`**. `worktree-add` starts from committed parked same-epic tickets **or** done cross-epic `blocked_by`. Ticket → `done` is `worktree-finish` (commit then park). When every ticket is in `done`, agents run `worktree-integrate` (apply, unlink junctions, prune ticket folders), smoke, **then** summarize + `handoff`, then `worktree-push` (`.ai/rules/worktrees.md`).
10. **Active time:** claim starts the clock; `handoff` freezes it. Gaps longer than `time.idle_ms` (default 3 min) between board pulses are not counted (Run / Approve wait). `ping` during long commands. `clock pause` / `resume` when waiting on purpose. Living HTML shows hours and minutes per ticket and for the epic. UI **Hours worked** (sidebar) sums Active + Elapsed across every epic for a selected month; CLI `hours [--month YYYY-MM]`.

Skill: `.ai/skills/private-board/SKILL.md` · Journey rule: `.ai/rules/journey-epic.md`
