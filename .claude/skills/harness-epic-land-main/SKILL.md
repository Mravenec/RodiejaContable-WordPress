---
name: harness-epic-land-main
description: After all feature branches are on origin, land them onto main in epic order with land clock, cumulative smoke, and living HTML land summary.
---

Source: follow `.ai/skills/epic-land-main/SKILL.md` and `.ai/rules/epic-land-main.md`.

After `worktree-origin-ready` (`originReady: true`): `worktree-land-next` → `worktree-land-main <n>` → smoke → `worktree-land-finish` (human land summary in living HTML + journey Status) → push `main` (never `--force`). Land-main only unlinks colliding untracked persist files and refuses (never deletes) other colliding paths or undeclared gitlinks. Land smoke runs the layer audit on `main` and refuses a pass on violations. Track land active/elapsed for Hours worked. Serial epic order only.
