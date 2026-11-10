---
description: Publish harness + board engine changes to zeroday template and CascadeProjects/linear
alwaysApply: true
---

# Harness publish map (mandatory)

Day-0 users clone the **neutral** harness. Process fixes learned in a product repo must land in the shared trees — not only in the product checkout.

## Trees

| Tree | Path (typical) | Owns |
|---|---|---|
| Board engine | `CascadeProjects/linear` | `.linear` board CLI, UI (`scripts/`, `ui/`) |
| Neutral harness | `zeroday/HarnessMariaDB-SpringBootJOOQ-ReactJS` | Day-0 template: `.ai/` + vendored `.linear/` |
| Product host | e.g. `eh8s_harness` | Live project; must stay in lockstep when process changes |

## What goes where

| Change type | Update first | Also copy / mirror |
|---|---|---|
| Board UI / `scripts/api.mjs` / board libs | `CascadeProjects/linear` | Host `.linear/` **and** zeroday `.linear/` |
| Layer audit (`layer-audit.mjs`, `lib/layer-audit.mjs`, `services_without_repository` key) | Host `.ai/` | Zeroday `.ai/` (same content; zeroday keeps `module_name: null`, so the audit skips, and pre-lists `JwtService` because the JWT spec's `IJwtService` reads no table) |
| Land / worktree / handoff scripts | Host `.ai/scripts/` (or edit zeroday `.ai` in the same task) | Zeroday `.ai/scripts/` (same content) |
| Agent rules / skills (process) | Host `.ai/rules/` · `.ai/skills/` | Zeroday `.ai/…` + Cursor/Claude mirrors (tool-adapters harmony) |

## Non-negotiables (lessons)

1. **Land badge honesty** — Board UI land status must match `worktree-land-ready` (file `land.json` **or** infer when `origin/feature/{n}-{slug}` is on `origin/main`). Never leave day-0 users with “No updates” when the epic is already on `main`.
2. **Clean git after land** — Local `ticket/{n}-{slug}/*` branches that are already on `origin/main` (or the epic feature tip) must be deleted by integrate / push / `worktree-land-ready`. `git branch` should not show stale tickets after a finished wave.
3. **Same task** — Do not finish a process fix in only one of the three trees.

Intra-repo mirrors (`.ai` ↔ `.cursor` ↔ `.claude`) stay under `.ai/rules/tool-adapters-harmony.md`.
