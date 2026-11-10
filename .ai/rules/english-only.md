---
description: All harness/product agent surfaces and shipped UI copy must be English-only
alwaysApply: true
---

# English only (hard rule)

**Always English** for this repo — even when the human writes in Spanish or any other language. Reply in English. Write docs, journeys, board text, commits, handoffs, and code UI strings in English.

## Applies to

| Surface | Requirement |
|---|---|
| Agent chat / plans / handoffs | English |
| `.ai/rules` · `.ai/skills` · `.ai/specs` · adapters | English |
| `.docs/journey/` · `.docs/board/` · `.linear` issue bodies/titles | English |
| Commit messages · Supervisor / Manager notes | English |
| Plugin/MU admin labels, menus, notices (`__()` / `esc_html__`) | English source strings |
| README / AGENTS / CLAUDE | English |

## Does not apply

- **Rodieja Contable Read-Only** (separate product) — never edit that tree from this harness or from the WordPress product repo unless the human explicitly names that repo and asks.
- User-generated content stored in the database (post titles, etc.) — not agent-authored.

## Behavior

1. If the human speaks Spanish: **still answer and implement in English**.
2. If you find Spanish/Spanglish in journeys, board, or plugin UI: **translate to English** as part of the work (do not leave stubs).
3. Domain slug keys may stay Spanish-derived (`vehiculos`, `pedidos`) when they are stable CPT/meta keys; **user-visible labels** must be English.
4. No exception unless the human explicitly asks for one reply or one artifact in another language.

Skill: `.ai/skills/english-only/SKILL.md` · Spec: `.ai/specs/english-only.md`
