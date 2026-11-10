# Skill — journey = Epic

Two pillars only:

| Artifact | Role |
|---|---|
| `.docs/journey/{n}-{slug}.md` | Full Epic story (why / acceptance / agent notes / verify / outcome) |
| `.linear/epics/{n}-{slug}/` | Live board — **issues are the tickets** |

```bash
node .ai/scripts/epic-new.mjs <n> <slug> --title "Short title"
```

Put all narrative depth in the journey (see `.docs/journey/README.md`). Put work steps only on board issue checklists. No `.ai/tickets/` folder.

**Never leave stubs:** after tickets move / land, Status must be real (In progress → Done), Why/Acceptance/Outcome filled — not “Planned” template text. Every board epic needs a matching journey file.

Keep journey Status in sync; tickets park in **`done`** until the whole epic is done, then **`summarize`**. Epic % is column + checklist and reaches **100%** when all issues are in **`summarize`** with checklists complete. The beginner HTML is a living work log (`summaries/{n}-{slug}-beginner.html`) from the first claim/move; wrap-up uses `handoff` for git + Supervisor / Manager. The board UI row must show **`{n}-{slug}`** (same as this journey file and `.linear/epics/{n}-{slug}/`).

## Large product briefs

If the human hands a **large** brief (many features / full manual): **create every Epic + every ticket first** (`blocked_by`, roles), then start claims. Do not code until the board map exists. Single-feature asks stay one Epic. See `.ai/rules/orchestrate-first.md`.

Cross-epic `blocked_by` is enforced by the board (`next` / `claim`).

Details: `.ai/rules/journey-epic.md` · board progress: `.ai/rules/private-board.md` · journey docs: `.docs/journey/README.md`
