---
name: landed-means-origin-main
description: Board LANDED badge means GitHub origin/main; use when finishing epics, writing land.json, or explaining land status.
---

# Skill — LANDED means `origin/main`

## Goal

Never show or claim **LANDED** unless GitHub `origin/main` contains the epic.

## Do

1. After every epic handoff: **run** `node .ai/scripts/worktree-push.mjs <n>`.
2. When all features are on origin: `worktree-origin-ready` → land-main → land-smoke → **`worktree-land-finish`** (pushes `main`).
3. If push fails (auth / non-fast-forward): leave status `smoke-main` / `on-origin`; fix; re-run. Do not edit `land.json` by hand.

## Do not

- Write `"status":"landed"` into `land.json` after a local-only merge
- Tell the human “landed” when `git rev-parse origin/main` lacks the feature
- Pull the **harness** clone expecting product commits — land on the **product** repo’s `origin/main`

Canonical rule: `.ai/rules/landed-means-origin-main.md` · land pipeline: `.ai/skills/epic-land-main/SKILL.md`
