---
name: harness-tool-adapters-harmony
description: Keep .ai / .cursor / .claude / AGENTS.md / CLAUDE.md in harmony when process docs change. Use when editing any agent adapter surface.
---

Source: follow `.ai/skills/tool-adapters-harmony/SKILL.md` and `.ai/rules/tool-adapters-harmony.md` (mirror — do not duplicate content here).

If asked to update only `.claude`, still update `.ai` (canonical) and `.cursor` / entry docs when the change is process-related. Surfaces do not auto-sync. A new or tightened stack rule also extends `.ai/scripts/layer-audit.mjs` and gets a migration ticket for old violations.
