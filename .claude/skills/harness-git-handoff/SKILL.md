---
name: harness-git-handoff
description: Agents commit, merge, record summarize HTML (git line + smoke note), then worktree-push feature/{n}-{slug} and drop _epic.
---

Source: follow `.ai/skills/git-user-handoff/SKILL.md` and `.ai/rules/git-user-handoff.md` (mirror — do not duplicate content here).

Ticket → `done`: run `worktree-finish` (no ticket push, no manager note).

Epic → `summarize`: run `worktree-integrate`, smoke, `handoff` (record `cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}` plus Supervisor / Manager smoke steps), then `node .ai/scripts/worktree-push.mjs <n>`.
