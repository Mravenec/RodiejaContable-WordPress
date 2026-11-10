---
description: Agents may publish only feature/{n}-{slug} via worktree-push after summarize; never force, HEAD, or ticket branches
alwaysApply: true
---

# Safe epic push (mandatory)

Agents **do** publish `feature/{n}-{slug}` after summarize + `handoff`. They do it **only** through `worktree-push.mjs`. Raw `git push` is refused except that script (named branch, no `--force`, no `HEAD`).

## Forbidden

| Command | Notes |
|---|---|
| `git push --force` / `git push -f` | Never |
| `git push origin HEAD` | Never — always name `feature/{n}-{slug}` |
| `git push` of `ticket/{n}-{slug}/*` | Ticket branches stay local |
| `git push` of `main` / other product branches | Out of scope |

## Allowed for agents (required)

- `git status`, `git diff`, `git log`, `git branch` — read-only inspection (`git status` is required at epic summarize)
- `git add` / `git commit` — **via** `worktree-commit.mjs` / `worktree-finish.mjs` / finishing a merge in `_epic`
- `git merge` — **via** `worktree-integrate.mjs` and while resolving conflicts in `_epic`
- After `handoff` has written the git line + Supervisor / Manager smoke note into the living HTML: `node .ai/scripts/worktree-push.mjs <n>` (push named epic branch, then drop `_epic`)

Do **not** wait for the human to type `git add` / `git commit` / `git merge` / `git push`.

## What gets recorded (not a human fence)

`handoff` stores this line in the living HTML so the push is auditable:

```bash
cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}
```

The agent then **runs** `worktree-push` (same branch). Ticket branches (`ticket/{n}-{slug}/*`) are **never** pushed.

## Rationale

The living HTML is the record (push line + manager smoke walkthrough). Publishing one epic branch after that record is agent work. Force / HEAD / ticket pushes are still unsafe.

Full handoff: `.ai/rules/git-user-handoff.md` · worktrees: `.ai/rules/worktrees.md`
