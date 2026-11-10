---
description: Keep .docs/journey Epic narrative in sync with board; column + checklist %
alwaysApply: true
---

# Journey update

Every meaningful board advance for Epic N should keep `.docs/journey/{n}-{slug}.md` accurate:

- Status Planned / In progress / Done
- What changed
- How to verify / Outcome
- Agent notes (constraints) when they change

The journey **is** the only Epic narrative. `.linear` holds the **tickets** (issue files). There is no `.ai/tickets/` folder. The board UI epic row must show the same key as the journey file and board folder: **`{n}-{slug}`**, then the human title.

## Living story — never leave stubs

A journey that still says **Planned**, “Describe the problem…”, or “Given …” after tickets have moved is **wrong**. Update Why / What we did / Acceptance / Outcome as work lands. At **summarize** (or land-finish):

- **Status: Done** (and LANDED on `origin/main` when finish pushes)
- Checklist items in “What we did” marked done
- **Outcome** filled with what shipped and how to see it in wp-admin
- Epic key present: `.docs/journey/{n}-{slug}.md` must exist for every board epic

Board progress % is **column + checklist** (backlog→doing→testing→done→summarize, plus each marked checklist item). Tickets stay in **`done`** until every issue in the epic is in `done`; only then move them to summarize. The beginner HTML is a **living work log** from the first claim/move (`summaries/{n}-{slug}-beginner.html` — all tickets, including testing → doing). Wrap-up records git + Supervisor / Manager via `handoff`.

Large briefs: create all epics + tickets before coding — `.ai/rules/orchestrate-first.md`.

Skill: `.ai/skills/journey-epic/SKILL.md` · Board rule: `.ai/rules/private-board.md`
