---
name: harness-private-board
description: private.board filesystem board (.linear). Use when starting, claiming, checklisting, moving testing/done, recording handoff, or finishing with summarize (living HTML). Never a cloud issue tracker. Epic % is column + checklist.
---

Source: follow `.ai/skills/private-board/SKILL.md` and `.ai/rules/private-board.md` (mirror — do not duplicate content here).

Mandatory on this product: `.ai/rules/board-process-mandatory.md` (Spring-harness process, WordPress stack).

Board flow (must match `AGENTS.md` / `CLAUDE.md`):

    `next` → `claim` → do one checklist item → **immediately mark it** → **`evidence`** (compact code/command/result) → repeat in `doing` → **`move testing`** → verify + flags. If verification fails: `fail` (testing → doing, claim kept) or `move doing --reason`, fix, `move testing` again. Then **`worktree-finish`** (commit then park). Living HTML refreshes on every move and evidence pulse. When **every** ticket is in `done`, agents **`worktree-integrate`** (prunes ticket folders), then **`summarize`** + **`handoff`** (git line + smoke note), then **`worktree-push`**.

Epic % is **column + checklist** (each move and each marked checklist item raises %; 100% when all tickets are in `summarize` with checklists complete). Dependents unlock at `done`.

Work-then-mark is mandatory (`.ai/rules/board-checklist-order.md`). The CLI refuses skip/batch. If work is in `Downloads/.linear`, mark that board instead.

Large briefs: orchestrate all epics + tickets before implementation claims. Cross-epic `blocked_by` is board-enforced.

After **every** `claim`, **required**: `node .ai/scripts/worktree-add.mjs <ISSUE_ID>` → `.worktrees/{n}-{slug}/{ISSUE_ID}` on `ticket/{n}-{slug}/{ISSUE_ID}` (shared board — `.ai/rules/worktrees.md`). Ticket `done` is `worktree-finish`. When every ticket is in `done`, agents merge, `handoff`, then `worktree-push`.

Active time (hours/minutes) starts on `claim` and freezes on `handoff`. Gaps longer than 3 minutes are not counted (Run / Approve wait). `ping` during long commands; `clock pause` / `resume` when waiting on purpose. UI **Hours worked** (sidebar) sums Active + Elapsed for every epic in a selected month; CLI `hours [--month YYYY-MM]`.

`.linear` and the board engine are board-only. Stack and `3.diagrams` live in host `AGENTS.md` / `.ai/`. UI brand: **private.board**.
