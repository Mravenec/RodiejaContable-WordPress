---
description: Mark board checklist items in order as work happens — never at the end
alwaysApply: true
---

# Board checklist order (mandatory)

Work and board marks stay in lockstep. If the checklist is not updated as you go, the board is not the source of control.

## Which board

| Work lives in | Mark this board |
|---|---|
| This host project | `node .linear/scripts/board.mjs …` |
| The engine folder `Downloads/.linear` | `node scripts/board.mjs …` from that folder |

Never leave a ticket `[ ]` after you already did that step.

## Loop (one item at a time)

1. Claim the issue (`doing`).
2. Do **only** the first pending checklist item.
3. Immediately: `checklist <ID> <n> --agent <name>`
4. Immediately: `evidence <ID> --title "…" --path … --lang … --code-file … --command "…" --result-file … --agent <name>` (compact windows for the living HTML — `.ai/rules/living-summary.md`)
5. Only then start the next item.

Do **not** implement items 2+ while item 1 is still `[ ]`.
Do **not** write all layers and mark the board at the end.
Do **not** batch (`checklist <id> 1,2` or `all`) — the CLI refuses it.
Do **not** skip — the CLI returns `Out of order: mark item N before M`.

Verification items stay for the `testing` column (`move testing` first, then mark those).
