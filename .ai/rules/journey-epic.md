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

Board progress % is **column + checklist** (backlog→doing→testing→done→summarize, plus each marked checklist item). Tickets stay in **`done`** until every issue in the epic is in `done`; only then move them to summarize. The beginner HTML is a **living work log** from the first claim/move (`summaries/{n}-{slug}-beginner.html` — all tickets, including testing → doing). Wrap-up records git + Supervisor / Manager via `handoff`. Mark journey **Status: Done** when the epic board is complete through summarize (or the human accepts an earlier narrative close).

Large briefs: create all epics + tickets before coding — `.ai/rules/orchestrate-first.md`.

Skill: `.ai/skills/journey-epic/SKILL.md` · Board rule: `.ai/rules/private-board.md`
