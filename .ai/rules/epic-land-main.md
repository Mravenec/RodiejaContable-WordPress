---
description: After origin-ready, land feature/{n}-{slug} onto main in order with land clock + HTML summary
alwaysApply: true
---

# Epic land on `main` (after origin-ready)

Canonical skill: `.ai/skills/epic-land-main/SKILL.md`  
Spec: `.ai/specs/epic-land-main.md`  
Prior gate: `.ai/rules/epic-on-origin.md`

## When

Only after `node .ai/scripts/worktree-origin-ready.mjs` prints `originReady: true`.

## Automatic (serial, epic order)

```bash
node .ai/scripts/worktree-land-next.mjs
node .ai/scripts/worktree-land-main.mjs <n>
# resolve conflicts on main if needed, then re-run land-main or continue
node .ai/scripts/worktree-land-smoke.mjs <n>
node .ai/scripts/worktree-land-finish.mjs <n> --note-file …
node .ai/scripts/worktree-land-ready.mjs
```

Each epic: **merge → conflicts → cumulative smoke → land handoff (human summary) → push `main`**. Track **land** active + elapsed (billable). Update living HTML **Land on main** section and journey **Status: Landed on main**.

`worktree-land-smoke` runs the layer audit (`.ai/scripts/layer-audit.mjs`) on `main` and refuses a pass (`smoke_ok=false`) on violations. Fix them on `main` as part of conflict resolution, commit, re-run the smoke.

`worktree-land-main` deletes nothing but untracked **persist files** (`.docs/board`, `.docs/journey`) that the merge would overwrite, listed one by one. Any other dirty or untracked path that collides with the feature merge makes it **refuse** and stay on disk (park or move it). It also refuses a feature branch that adds a **gitlink** (nested clone) not declared in `.gitmodules` — fix on the feature branch (`git rm --cached <path>`, gitignore the folder), push again, re-run (spec failures #13, #17, #18).

## Do not

- Land epic N while N−1 is not `landed`
- Record a land smoke pass while the layer audit fails on `main`
- Delete untracked non-persist folders to make a land fit, or land a branch with an undeclared gitlink
- Force-push `main`
- Commit untracked `.docs/board` / journeys onto `main` before the feature merge
- Add Review/QA ticket columns for land (epic lifecycle in `summaries/land.json` only)

Rule map: `.ai/rules/tool-adapters-harmony.md`
