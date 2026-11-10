---
description: Keep .ai / .cursor / .claude / AGENTS.md / CLAUDE.md in harmony — update all related surfaces together
alwaysApply: true
---

# Tool adapters — keep in harmony (mandatory)

These surfaces describe the **same** agent process. They do **not** auto-sync. Whoever changes one must update the others in the **same** task when the change is process/docs related.

## Surfaces

| Path | Role |
|---|---|
| `.ai/rules/` · `.ai/skills/` · `.ai/scripts/` | **Canonical** — put the full rule/skill text here |
| `.cursor/rules/` | Cursor mirror (often same text as `.ai/rules/*.md` as `.mdc`) |
| `.claude/skills/` · `.claude/settings.json` | Claude Code mirror (prefer short pointer to `.ai/…`) |
| `AGENTS.md` · `CLAUDE.md` | Entry pointers — must agree on board/process flow |

## Bidirectional duty

- Prefer editing **`.ai/` first**, then mirrors.
- If the human asks to change **`.claude`**, **`.cursor`**, `AGENTS.md`, or `CLAUDE.md` only: still update **`.ai/`** (canonical) and **every other affected mirror** in the same change.
- Never leave one tool behind with a different process (board flow, epic %, gates, handoff, language, layers, worktrees).

## When a change is required elsewhere

Update siblings if the edit affects: board columns/flow, epic %, checklist order, **living HTML evidence**, **progressive disclosure / one-job pages**, journey model, stack layers, **JOOQ POJOs / no DTOs**, **layer audit** (`.ai/scripts/layer-audit.mjs` rules, allowlist, hooks), HTTP/JWT/dbdiagram/`3.diagrams` gates, git handoff, English-only, orchestrate-first, module naming, or worktrees.

Cosmetic-only edits inside one mirror (typo in a Claude stub that already points at `.ai`) may stay local — prefer fixing the canonical `.ai` text instead.

## Typical map

| Canonical | Cursor | Claude |
|---|---|---|
| `.ai/rules/private-board.md` | `.cursor/rules/private-board.mdc` | `.claude/skills/harness-private-board/SKILL.md` |
| `.ai/rules/journey-epic.md` | `.cursor/rules/journey-epic.mdc` | `.claude/skills/harness-journey-epic/SKILL.md` |
| `.ai/rules/diagrams.md` | `.cursor/rules/diagrams.mdc` | `.claude/skills/harness-diagrams/SKILL.md` |
| `.ai/rules/dbdiagram.md` | `.cursor/rules/dbdiagram.mdc` | `.claude/skills/harness-dbdiagram/SKILL.md` |
| `.ai/rules/database-ddl-files.md` · `.ai/skills/database-ddl-files/` | `.cursor/rules/database-ddl-files.mdc` | `.claude/skills/harness-database-ddl-files/SKILL.md` |
| `.ai/rules/database-multi-schema.md` | `.cursor/rules/database-multi-schema.mdc` | `.claude/skills/harness-database-multi-schema/SKILL.md` |
| `.ai/rules/layered-stack.md` | `.cursor/rules/layered-stack.mdc` | `.claude/skills/harness-layered-stack/SKILL.md` |
| `.ai/rules/spring-initializr.md` | `.cursor/rules/spring-initializr.mdc` | `.claude/skills/harness-spring-initializr/SKILL.md` |
| `.ai/rules/backend-http-gate.md` | `.cursor/rules/backend-http-gate.mdc` | `.claude/skills/harness-backend-http-gate/SKILL.md` |
| `.ai/rules/multi-agent.md` | `.cursor/rules/multi-agent.mdc` | (roles live in `AGENTS.md`) |
| `.ai/rules/worktrees.md` | `.cursor/rules/worktrees.mdc` | `.claude/skills/harness-worktrees/SKILL.md` |
| `.ai/rules/board-persist.md` | `.cursor/rules/board-persist.mdc` | `.claude/skills/harness-board-persist/SKILL.md` |
| `.ai/rules/epic-on-origin.md` | `.cursor/rules/epic-on-origin.mdc` | `.claude/skills/harness-epic-on-origin/SKILL.md` |
| `.ai/rules/epic-land-main.md` | `.cursor/rules/epic-land-main.mdc` | `.claude/skills/harness-epic-land-main/SKILL.md` |
| `.ai/rules/frontend-api-reach.md` | `.cursor/rules/frontend-api-reach.mdc` | `.claude/skills/harness-frontend-api-reach/SKILL.md` |
| `.ai/rules/jwt-auth.md` | `.cursor/rules/jwt-auth.mdc` | `.claude/skills/harness-jwt-auth/SKILL.md` |
| `.ai/rules/backend-jooq.md` · `.ai/rules/jooq-pojos-no-dto.md` | `.cursor/rules/backend-jooq.mdc` · `.cursor/rules/jooq-pojos-no-dto.mdc` | `.claude/skills/harness-jooq-pojos-no-dto/SKILL.md` |
| `.ai/rules/living-summary.md` | `.cursor/rules/living-summary.mdc` | `.claude/skills/harness-living-summary/SKILL.md` |
| `.ai/rules/progressive-disclosure.md` | `.cursor/rules/progressive-disclosure.mdc` | `.claude/skills/harness-progressive-disclosure/SKILL.md` |
| `.ai/skills/<name>/SKILL.md` | (rule mirror if always-apply) | `.claude/skills/harness-<name>/SKILL.md` |
| `AGENTS.md` | (summary in rules) | `CLAUDE.md` must agree |

## Cross-repo publish (neutral harness + board engine)

Intra-repo harmony is not enough for day-0. When board UI/API or land/worktree process changes, also update:

| Target | What |
|---|---|
| `CascadeProjects/linear` | Board engine (`scripts/`, `ui/`) |
| `zeroday/HarnessMariaDB-SpringBootJOOQ-ReactJS` | `.ai/` scripts/rules **and** vendored `.linear/` |

Canonical: `.ai/rules/harness-publish-map.md`

Skill: `.ai/skills/tool-adapters-harmony/SKILL.md`
