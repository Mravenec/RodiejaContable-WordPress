# Skill — git worktrees (Windows-first)

Use after **every** `claim`. One board ticket = one folder. Agents commit, merge, record the living HTML, then `worktree-push` (`feature/{n}-{slug}` + drop `_epic`).

## Goal

Isolated ticket folders under `{n}-{slug}`. One private.board. Start from **committed** deps (same epic or cross-epic `blocked_by`). Integrate when **every** ticket in the epic is `done`. Not a cloud conductor.

## Procedure

1. `next` → `claim <ISSUE_ID> --agent <name>`
2. `node .ai/scripts/worktree-add.mjs <ISSUE_ID>` (**required**). If it fails because a parked dep is dirty, commit that dep first (`worktree-commit` / `worktree-finish`), then retry. Do not ask the human to merge.
3. Implement inside `.worktrees/{n}-{slug}/{ISSUE_ID}`
4. Checklist / `move testing` / verify as usual
5. Ticket **`done`:** `node .ai/scripts/worktree-finish.mjs <ISSUE_ID> --agent <name>` (layer audit, commit **then** park). It refuses before the commit when `node .ai/scripts/layer-audit.mjs --root <ticket folder>` finds violations; fix them in the ticket folder and retry. No human commit fence. No manager note.
6. When **every** ticket in the epic is `done`: `node .ai/scripts/worktree-integrate.mjs <n>` (apply). Merges into `_epic`, runs the **layer audit** on `_epic` (exit 1 before persist + prune on violations: fix in `_epic`, commit, re-run), **unlinks board junctions**, stops java/vite still using ticket folders, then **removes** ticket folders. **Fails** if any `APP-*` remains. Primary `.linear/epics` stays. On conflict, resolve in `_epic`, commit, re-run. Smoke. Then summarize + `handoff` (git line + Supervisor / Manager smoke note in the living HTML).
7. From the primary checkout: `node .ai/scripts/worktree-push.mjs <n>` (publishes `feature/{n}-{slug}`, then drops `_epic` and leftover ticket folders). Never `HEAD`. Never `--force`. Never ticket branches.

## Commit guard

Every commit helper (`worktree-commit`, `worktree-finish`, `worktree-integrate`, `worktree-push`) refuses before `git add` unless the folder is its own git worktree, is not the primary checkout, and is on a `ticket/` or `feature/` branch. It also refuses new gitlinks (nested clones) that `.gitmodules` does not declare. On refusal: move real work into the ticket worktree, delete the leftover folder, gitignore nested clones, re-run (spec failure #11).

## Wait vs merge

- Serial `blocked_by`: wait for `worktree-finish` on the dep; `worktree-add` copies those files in.
- Parallel independent tickets: no mid-doing sibling merge.
- Epic merge: only at integrate.

## Do not

- Skip `worktree-add`
- `move done` on a dirty tree (CLI refuses)
- Start a cross-epic ticket from `main` when `blocked_by` has a committed branch
- Record `HEAD` in the HTML git line — always `git push -u origin feature/{n}-{slug}`
- Push ticket branches
- Leave APP-* folders after integrate
- Bypass a commit-guard refusal with a raw `git add -A` on the primary checkout
- Recurse-delete a worktree `.linear/epics` or `.linear/agents` junction (wipes the live board on Windows)
- Leave `_epic` after `worktree-push`
- Ask the human to push
- Parallelize SQL + DBML + JOOQ
- Set frontend `build_ok` while Health is Network Error

Details: `.ai/rules/worktrees.md` · spec: `.ai/specs/worktree-auto-integrate.md` · handoff: `.ai/rules/git-user-handoff.md`
