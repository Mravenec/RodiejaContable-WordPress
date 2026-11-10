# Skill — board persist

Use when creating epics, marking the board, integrating worktrees, or recovering a wiped UI.

## Source of truth

| Copy | Path | Git |
|---|---|---|
| Live board | `.linear/epics/{n}-{slug}/` on the **primary** checkout | ignored (`epics/*/`) |
| Recovery snapshot | `.docs/board/{n}-{slug}/` | **tracked** |
| Story | `.docs/journey/{n}-{slug}.md` | tracked |

Junctions in `.worktrees/.../.linear/epics` point at the live board. They are optional. Never the only copy you keep.

## Commands

```bash
node .linear/scripts/board.mjs snapshot          # all epics
node .linear/scripts/board.mjs snapshot 1
node .linear/scripts/board.mjs restore 1-my-slug
```

Board pulses (create, claim, checklist, move, handoff) already snapshot after the living HTML refresh.

## After a wipe

1. Do **not** recreate empty epics if `.docs/board/` or git has the snapshot.
2. `git checkout -- .docs/board` if needed, then `board.mjs restore <key>`.
3. Unlink-before-remove is already required so this should not happen again.

## Do not

- Recurse-delete a worktree `.linear/epics` junction
- Treat `.docs/board` as a second ticket pack to edit by hand
- Leave orchestration uncommitted on the epic branch (journeys + `.docs/board` are copied into the ticket / `_epic` tree by finish / integrate / push — `.ai/rules/epic-on-origin.md`)

Rule: `.ai/rules/board-persist.md`
