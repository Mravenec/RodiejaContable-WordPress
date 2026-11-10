---
name: harness-epic-on-origin
description: Snapshots and journeys ride feature/{n}-{slug}. After all epics are on origin, continue with land-on-main.
---

Source: follow `.ai/skills/epic-on-origin/SKILL.md` and `.ai/rules/epic-on-origin.md`.

`worktree-finish` / integrate / `worktree-push` commit `.docs/board` + journeys on the epic branch. Then `node .ai/scripts/worktree-origin-ready.mjs`. When `originReady: true`, continue with **land-on-main** (`.ai/skills/epic-land-main/SKILL.md`). Do not commit `.linear/epics`.
