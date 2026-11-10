---
description: Agents publish feature/* via worktree-push and main via land-finish only; never force/HEAD/ticket
alwaysApply: true
---

# Safe git push (mandatory)

Agents **do** push — but **only** through harness scripts:

| When | Script | What gets pushed |
|---|---|---|
| After epic `handoff` | `node .ai/scripts/worktree-push.mjs <n>` | `origin/feature/{n}-{slug}` |
| After land smoke OK | `node .ai/scripts/worktree-land-finish.mjs <n> …` | `origin/main` (never `--force`) |

Raw `git push` in the agent shell is **forbidden**. Do not ask the human to push for you when these scripts apply.

## Forbidden

| Command | Notes |
|---|---|
| `git push --force` / `git push -f` | Never |
| `git push origin HEAD` | Never — always name the branch inside a script |
| `git push` of `ticket/{n}-{slug}/*` | Ticket branches stay local |
| Hand `git push origin main` outside land-finish | Use `worktree-land-finish` so LANDED is honest |
| Writing `land.json` `"landed"` without a successful `origin/main` push | `.ai/rules/landed-means-origin-main.md` |

## Allowed for agents (required)

- `git status`, `git diff`, `git log`, `git branch`, `git fetch` — inspection
- `git add` / `git commit` — **via** worktree-commit / worktree-finish / integrate merges
- `git merge` — **via** `worktree-integrate` / `worktree-land-main`
- After `handoff`: **run** `worktree-push.mjs <n>`
- After land smoke: **run** `worktree-land-finish.mjs <n>` (pushes `main`)

Do **not** wait for the human to type push when auth works. If push fails (auth / non-fast-forward), **do not** mark LANDED — fix auth or integrate remote history, then re-run the script.

## Handoff record

`handoff` stores the feature push line in living HTML; the agent then runs `worktree-push`.  
`land-finish` stores `git push origin main` and runs it; only then is status `landed`.

Full handoff: `.ai/rules/git-user-handoff.md` · land: `.ai/rules/epic-land-main.md` · LANDED law: `.ai/rules/landed-means-origin-main.md`
