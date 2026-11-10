---
name: harness-plugin-http-gate
description: Plugin testing gate — activate plugin, health 200, protected REST 401/403 anonymous, authenticated 2xx. Use on plugin API work.
---

Source: follow `.ai/skills/plugin-http-gate/SKILL.md` and `.ai/rules/plugin-http-gate.md` (mirror — do not duplicate content here).

Prove GATE_HTTP with curl / `ddev wp` after activation. Never `__return_true` on mutating routes. Park with `worktree-finish`, not a dirty `move done`.
