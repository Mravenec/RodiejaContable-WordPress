---
name: harness-board-persist
description: Keep the filesystem board recoverable — primary .linear/epics is source of truth; tracked snapshot in .docs/board. Use when integrating worktrees or restoring a wiped board UI.
---

Source: follow `.ai/skills/board-persist/SKILL.md` and `.ai/rules/board-persist.md` (mirror — do not duplicate content here).

Unlink `.linear/epics` + `.linear/agents` junctions **before** `git worktree remove`. Snapshot is automatic on board pulses. Finish / integrate / push commit `.docs/board` + journeys on the epic branch. Restore with `node .linear/scripts/board.mjs restore <n-{slug}>`.
