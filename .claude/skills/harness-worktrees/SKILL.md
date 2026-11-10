---
name: harness-worktrees
description: Git worktrees nested under {n}-{slug} — agents commit, merge, then worktree-push feature/{n}-{slug} and drop _epic. Use after claim or when isolating ticket folders.
---

Source: follow `.ai/skills/worktrees/SKILL.md` and `.ai/rules/worktrees.md` (mirror — do not duplicate content here).

After **every** `claim <ISSUE_ID>`: `node .ai/scripts/worktree-add.mjs <ISSUE_ID>` (**required**). Ticket done = `worktree-finish` (layer audit first). When every ticket is `done`: `worktree-integrate`, `handoff`, then `node .ai/scripts/worktree-push.mjs <n>`. Never recurse-delete a worktree `.linear/epics` junction. Stop DDEV only if a ticket folder is holding mounts you must prune — prefer leaving `ddev` running on the primary checkout.
