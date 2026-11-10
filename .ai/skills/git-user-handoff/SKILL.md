# Skill — end-of-task handoff

Agents **run** local commit, merge, `handoff` into the living HTML, then `worktree-push`. The human reads that HTML (git line + smoke note). Do not ask them to push.

## Ticket → `done`

1. Flags passed in `testing`
2. **Run** `node .ai/scripts/worktree-finish.mjs <ISSUE_ID> --agent NAME --message "<ISSUE_ID>: …"`
3. Optionally show `git status` from that ticket folder (already committed). **No** human commit fence. **No** Supervisor / Manager note.

## Epic → `summarize`

1. **Run** `node .ai/scripts/worktree-integrate.mjs <n>` (apply). Resolve conflicts in `_epic`; re-run.
2. Smoke the combined tree (API + UI reach — not Network Error).
3. `move summarize` + living HTML as a study guide.
4. Show `git status` from `.worktrees/{n}-{slug}/_epic`
5. Record this line in the HTML (audit, never `HEAD`):

```bash
cd .worktrees/1-my-feature/_epic && git push -u origin feature/1-my-feature
```

6. `Supervisor / Manager:` outside the fence; note in ` ```text ` for the **whole epic** (must include smoke steps)
7. `node .linear/scripts/board.mjs handoff <n> --git "cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}" --note-file path/to/note.txt`
8. **Run** `node .ai/scripts/worktree-push.mjs <n>` (publishes the named branch, then drops `_epic`)

## Inside the epic `text` fence

- First-person English for the **whole epic**
- `You can confirm it doing the following:`
- URLs, visible labels, expected outcomes (including Health **not** Network Error)

## Do not

- `git push --force` / `git push origin HEAD` / push ticket branches
- Ask the human to `git add` / `git commit` / `git merge` / `git restore` / `git push`
- Nest `.worktrees` paths
- Emit a manager note per ticket
- Leave `_epic` after a successful `worktree-push`

Rules: `.ai/rules/git-user-handoff.md` · `.ai/rules/no-git-push.md` · `.ai/rules/worktrees.md`
