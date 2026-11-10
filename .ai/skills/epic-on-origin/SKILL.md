# Skill — epic branches on origin (stop before `main`)

Use after `worktree-push`, after orchestration, or when `git status` on `main` shows untracked `.docs/board` / journeys. Do **not** merge into `main` here.

## Goal

Every board epic is published as `origin/feature/{n}-{slug}` with snapshots + journeys on that branch. Live board stays in `.linear/epics`. `main` stays day zero until a later land step.

## Procedure

1. Finish tickets with `worktree-finish` (copies persist docs into the ticket tree and commits them).
2. When the epic is in `done`: `worktree-integrate` (merge, persist commit on `_epic`, prune `APP-*`).
3. `handoff`, then `worktree-push` (persist commit after handoff, publish `feature/{n}-{slug}`, drop `_epic`).
4. `node .ai/scripts/worktree-origin-ready.mjs`
5. If `originReady` is false: push the missing epic, then re-run.
6. If `originReady` is true: this gate is done. Continue with **land-on-main** (`.ai/skills/epic-land-main/SKILL.md`) — do not improvise `git merge` outside those scripts.

## Do not

- `git add .` on `main` for `.docs/board` or `.docs/journey`
- Commit the live `.linear/epics` tree
- Open `feature/board` except as an emergency park of non-persist dirt
- Merge epic branches into `main` outside `worktree-land-*` scripts

Details: `.ai/rules/epic-on-origin.md` · spec: `.ai/specs/epic-on-origin.md` · persist: `.ai/rules/board-persist.md` · next: `.ai/skills/epic-land-main/SKILL.md`
