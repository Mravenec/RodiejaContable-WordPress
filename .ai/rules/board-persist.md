---
description: Keep private.board recoverable — primary checkout is source of truth; tracked snapshot in .docs/board
alwaysApply: true
---

# Board persist (mandatory)

The live board is `.linear/epics/` on the **primary** git checkout. Ticket worktrees may junction that folder for convenience. They are **not** a second board.

`.linear/epics/*/` stays gitignored so `main` is day zero. A **tracked** recovery copy is written on every board pulse:

`.docs/board/{n}-{slug}/`

That folder is a snapshot of the epic (issues, columns, living HTML). It is **not** a third narrative (journey stays the story; board issues stay the tickets).

## Agents

- Mark the board with `<primary>/.linear/scripts/board.mjs` (or the worktree’s `.linear/scripts` — `epics` is the same junction).
- After `epic-new` / orchestration, `worktree-finish` / integrate / `worktree-push` commit journeys **and** `.docs/board/` on the ticket then epic branch (`.ai/rules/epic-on-origin.md`). Do not commit them on `main`.
- `worktree-finish` / integrate commits that include `.docs/board/` make a wipe recoverable.
- If the live board is empty but snapshots exist:

```bash
node .linear/scripts/board.mjs restore <n|{n}-{slug}>
```

- Do **not** `rmSync` / `rmdir /s` a path that is `.linear/epics` or `.linear/agents` inside a worktree. Unlink the junction first (`.ai/rules/worktrees.md` · spec failure #8).

Skill: `.ai/skills/board-persist/SKILL.md` · spec: `.ai/specs/worktree-auto-integrate.md`
