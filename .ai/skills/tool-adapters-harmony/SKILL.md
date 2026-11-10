# Skill — tool adapters harmony

Keep `.ai/` (canonical), `.cursor/`, `.claude/`, `AGENTS.md`, and `CLAUDE.md` aligned in the **same** change when process docs change.

- Prefer edit `.ai/` first, then mirrors.
- Claude skills under `.claude/skills/harness-*/SKILL.md` should stay short pointers to `.ai/skills/…`.
- A new or tightened stack rule also extends `.ai/scripts/layer-audit.mjs` and gets a migration ticket for old violations.

Rule: `.ai/rules/tool-adapters-harmony.md`
