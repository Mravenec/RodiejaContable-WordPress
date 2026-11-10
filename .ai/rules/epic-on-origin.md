---
description: Snapshots and journeys commit on feature/{n}-{slug}; do not merge into main yet
alwaysApply: true
---

# Epic on origin (stop before `main`)

Canonical skill: `.ai/skills/epic-on-origin/SKILL.md`  
Spec: `.ai/specs/epic-on-origin.md`  
Persist: `.ai/rules/board-persist.md`

## Automatic

`worktree-finish`, `worktree-integrate`, and `worktree-push` copy `.docs/board/` + `.docs/journey/*.md` from the primary checkout into the ticket / `_epic` working tree and commit them there. Those files **ride** `feature/{n}-{slug}`. Live `.linear/epics/*/` stays gitignored.

After `worktree-push`, origin has that epic branch. The live board stays on the primary disk.

```bash
node .ai/scripts/worktree-origin-ready.mjs
```

When that prints `originReady: true`, this gate is done. The **next** automated step is land-on-main (`.ai/rules/epic-land-main.md`) — do not improvise merges outside those scripts.

## Do not

- `git add .` on `main` to pick up `.docs/board` / journeys (they belong on the epic branch)
- Commit `.linear/epics/*/`
- Create `feature/board` as the designed path
- Merge `feature/{n}-{slug}` into `main` in this step (use land-main scripts next)
- Treat untracked persist docs on a `main` checkout as a reason to commit them onto `main`

## Expected dirty `main` while work is in flight

The primary checkout stays on `main`. Persist files exist on disk (restore + board pulses) and also exist as commits on `feature/*`. `git status` on `main` can still list them as untracked. That does **not** mean they were lost. It **does** block `git merge` / `git pull` until you park other dirt and, later, land the epic branches.

Rule map: `.ai/rules/tool-adapters-harmony.md`
