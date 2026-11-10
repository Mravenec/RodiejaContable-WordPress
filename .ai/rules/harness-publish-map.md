---
description: Publish WordPress harness process fixes to .ai / .claude / .cursor together
alwaysApply: true
---

# Harness publish map (WordPress host)

This repo is the **WordPress** day-0 harness: `HarnessMariaDB-WordPressPHP-CustomPlugins`.

## Trees that matter here

| Tree | Owns |
|---|---|
| This host | `.ai/` · vendored `.linear/` · `.claude/` · `.cursor/` · `AGENTS.md` · `CLAUDE.md` |
| Shared board engine (optional upstream) | `CascadeProjects/linear` — only when changing board CLI/UI itself |
| Spring twin (separate product) | `HarnessMariaDB-SpringBootJOOQ-ReactJS` — **do not** merge JOOQ/React rules into WP tickets |

## What goes where

| Change type | Update |
|---|---|
| Board UI / `scripts/*.mjs` process | This host `.linear/` (+ upstream `linear` if you maintain it) |
| Layer audit / worktree / handoff | This host `.ai/scripts/` |
| Agent rules / skills | This host `.ai/` then `.claude` + `.cursor` mirrors (harmony) |

Intra-repo mirrors stay under `.ai/rules/tool-adapters-harmony.md`.
