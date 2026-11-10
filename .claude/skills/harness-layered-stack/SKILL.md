---
name: harness-layered-stack
description: Unified WordPress tree — architecture inside wp-content/plugins; no Spring 0/1/2 app folders. Use when writing or reviewing plugin/MU/docs.
---

Source: follow `.ai/skills/stack-layers/SKILL.md` and `.ai/rules/layered-stack.md` (mirror — do not duplicate content here).

Custom code: `web/wp-content/plugins/<module_name>/` from `.ai/project.yaml`.
Native only. `node .ai/scripts/layer-audit.mjs` must stay green.
After every claim: `node .ai/scripts/worktree-add.mjs <ISSUE_ID>`.
