---
description: Windows git worktrees — agents commit, merge, push feature/{n}-{slug} after summarize, then drop _epic
alwaysApply: true
---

# Git worktrees (Windows-first)

Several agents on **one** checkout steal the branch from each other. Isolate **code** in a worktree per **unblocked ticket**, named after the epic they belong to. Keep **one** private.board and UI. When every ticket in that epic is in `done`, **agents** merge those branches into **one epic branch**, smoke the combined tree, write the living HTML (git line + Supervisor / Manager smoke note), **then** `worktree-push` (`feature/{n}-{slug}` on origin, drop `_epic`).

This is a thin local helper (files + CLI). It is **not** a cloud orchestra and **not** a second product stack.

Canonical skill: `.ai/skills/worktrees/SKILL.md`  
Spec: `.ai/specs/worktree-auto-integrate.md`  
Scripts:

```text
node .ai/scripts/worktree-add.mjs <ISSUE_ID>
node .ai/scripts/worktree-commit.mjs <ISSUE_ID>
node .ai/scripts/worktree-finish.mjs <ISSUE_ID> --agent NAME
node .ai/scripts/worktree-integrate.mjs <n|{n}-{slug}>
node .ai/scripts/worktree-push.mjs <n|{n}-{slug}>
node .ai/scripts/worktree-origin-ready.mjs
node .ai/scripts/worktree-after-push.mjs <n|{n}-{slug}>
node .ai/scripts/worktree-rm.mjs <ISSUE_ID>
```

## After `claim`, before coding (required)

```text
next → claim <ISSUE_ID> → worktree-add <ISSUE_ID> → implement inside .worktrees/{n}-{slug}/{ISSUE_ID}
```

**One board ticket = one worktree folder** while that ticket is in `doing` / `testing` / `done`. If the board shows three cards (`APP-1-1`, `APP-1-2`, `APP-1-3`), `.worktrees/{n}-{slug}/` must have those three folders until integrate. After integrate, only `_epic` remains. After `worktree-push`, `_epic` is gone and the branch lives on origin.

| Piece | Value |
|---|---|
| Epic key | `{n}-{slug}` — same as journey, board folder, UI row |
| Ticket branch | `ticket/{n}-{slug}/{ISSUE_ID}` |
| Ticket folder | `.worktrees/{n}-{slug}/{ISSUE_ID}` under the **primary** git checkout |
| Epic branch | `feature/{n}-{slug}` — created at **integrate** |
| Epic folder | `.worktrees/{n}-{slug}/_epic` |
| Board | **Primary** `.linear/epics` + `.linear/agents` only. Worktrees may junction those folders (convenience). Unlink the junction **before** `git worktree remove`. |
| UI | `http://127.0.0.1:47382` |

### Why ticket refs are `ticket/` not `feature/{n}-{slug}/…`

Git cannot store both `feature/1-my-feature` (a file) and `feature/1-my-feature/APP-1-1` (would need a directory). Tickets use `ticket/`. The epic ships as `feature/{n}-{slug}` after integrate.

Do **not** copy `.linear` per worktree. Do **not** commit `.worktrees/` (gitignored). Do **not** recurse-delete a worktree `.linear/epics` or `.linear/agents` path — that deletes the primary board on Windows (spec failure #8). Tracked recovery: `.docs/board/{n}-{slug}/` (`.ai/rules/board-persist.md`).

## When agents wait, commit, or merge

Collapse happens when a card is `done` while files are uncommitted, or a later tree starts from `main` instead of the dep branch.

| Situation | What agents do | What they do **not** do |
|---|---|---|
| Serial `blocked_by` (SQL → JOOQ → API → FE, including **cross-epic**) | Wait until the dep is **`done` and committed**. `worktree-add` starts from that ticket branch (newest parked same-epic **or** done `blocked_by`). | Claim early. Start from `HEAD`/`main` when a dep branch exists. Ask the human to `git merge` the dep. |
| Parked dep worktree is **dirty** | `worktree-add` **exits 1**. Owner of the dep runs `worktree-commit` / `worktree-finish`. Then retry add. | `git restore` on the human’s shell. Nested `cd` from inside another worktree. |
| Parallel independent tickets (plan left them unblocked, different files) | Each works on its own tree. **Do not** merge sibling trees mid-`doing`. | Merge FE into DB while both are in progress. Exceed 2–3 agents. Parallelize SQL + DBML + JOOQ. |
| Ticket verification passed | `worktree-finish` (**layer audit, commit, then** `move done`). Refuses before the commit when `layer-audit.mjs` finds violations. Board refuses dirty `move done`. | `move done` on an uncommitted tree (unlocks dependents with missing files). |
| Every ticket in **this epic** is `done` | `worktree-integrate` (apply): commit leftovers, create `_epic`, merge remaining ticket branches, run the **layer audit** on `_epic` (exit 1 before persist + prune on violations — fix in `_epic`, commit, re-run), **unlink board junctions**, stop java/vite still using ticket folders, then remove ticket folders + merged `ticket/` branches. **Exit 1** if any `APP-*` remains (spec failure #9). Primary `.linear/epics` stays. Only `_epic` remains for code when integrate succeeds. Agent resolves conflicts. Smoke. Then summarize + `handoff`, then `worktree-push`. | Print merge commands for the human. Ask the human to push. Summarize while a sibling is still in backlog/doing/testing. Keep APP-* folders after a “successful” integrate. Recurse-delete through a junction. |
| Living HTML has the git line + smoke note | `worktree-push`: publish `feature/{n}-{slug}`, then drop `_epic` and any leftover `APP-*` folders. Origin keeps the branch. | Leave `_epic` or leftover ticket folders after a successful push. Raw `git push origin HEAD`. Force push. |
| Conflict in `_epic` | Keep both tickets’ intent. `git add -A && git commit` in `_epic`. Re-run integrate. | Pick only HEAD. Ask the human to resolve git. |

`worktree-add` start point = newest **committed** parked ticket in this epic, else newest **committed** done `blocked_by` (other epics included), else `HEAD`.

### Commit guard (every commit helper)

`worktree-commit`, `worktree-finish`, `worktree-integrate` and `worktree-push` commit through one helper. It **refuses before `git add`** unless the folder is its own git worktree toplevel, is not the primary checkout, and is on a `ticket/` or `feature/` branch. A leftover plain folder under `.worktrees/{n}-{slug}/` would otherwise commit the **primary** onto `main` (spec failure #11). After `git add -A` it also refuses — and resets the index, files stay on disk — when a new **gitlink** (nested clone) appears that `.gitmodules` does not declare. Fix: move real work into the ticket worktree, delete the leftover folder, gitignore nested clones, re-run. Never work around the refusal with a raw `git add -A` on the primary.

After every epic is on origin, run `node .ai/scripts/worktree-origin-ready.mjs` and **stop** — do not merge `main` (`.ai/rules/epic-on-origin.md`).

## Why tickets **and** an epic branch

Ticket trees let 2–3 agents edit **independent** files. One tree for the whole epic (DB + API + UI) puts everyone on the same branch.

Keep ticket trees while work is in `doing` / `testing` / `done`. Integrate only when **every** ticket in that epic is `done`. After a successful integrate, ticket folders and merged `ticket/` branches are **removed**; only `_epic` stays until `worktree-push`.

SQL + DBML + JOOQ stay serial (`blocked_by`). A later ticket’s worktree starts from the latest **committed** done dep so backend sees database work — including when the dep lives in another epic.

## What the epic list in the UI means

Several epic rows (some at 0%) are **one backlog**. Not one worktree per row. Not a signal to run every database ticket in parallel.

## Keep serial (do not “speed up” with extra trees)

| Stay one (shared / serial) | May parallel (separate ticket worktree) |
|---|---|
| Board, claims, UI 47381 / 47382 | Ticket code that does **not** touch shared hotspots |
| `canonical_sql/` + `schema.dbml` + JOOQ codegen | After GATE_HTTP **and** backend `worktree-finish`: frontend vs an unrelated unblocked ticket |
| One MariaDB, one Spring `8080` | Folders `.worktrees/{n}-{slug}/{ISSUE_ID}` |
| Layer order: DB → JOOQ → API → `.http` → FE | Only tickets `blocked_by` already left unblocked |

## Mark the board from the worktree

After `worktree-add`, the worktree’s `.linear/epics` and `.linear/agents` **are** the primary board.

## Two handoff moments

Details: `.ai/rules/git-user-handoff.md`.

**Ticket → `done`:** agent runs `worktree-finish` (local commit, no push, no manager note).

**Epic → `summarize`:** agent runs integrate + smoke (ticket folders already pruned), writes the living HTML (`handoff` with the git line + Supervisor / Manager smoke note), then from the **primary** checkout:

```bash
node .ai/scripts/worktree-push.mjs 1
```

That publishes `feature/{n}-{slug}` and removes `_epic` plus leftover ticket folders. The branch stays on `origin`. The same git line stays in the HTML as the audit record.

## Remove

Integrate already drops ticket folders (and **fails** if Windows still holds one). `worktree-push` drops `_epic` and leftover `APP-*` after it publishes the epic branch. `worktree-after-push` is only if origin already has the branch and `_epic` (or leftover ticket folders) was left behind.

```bash
node .ai/scripts/worktree-rm.mjs APP-1-2
```

Manual escape hatch for one leftover ticket folder.

## Engine copy

Board engine: `scripts/worktree-*.mjs --repo <host-git-root>`. Worktrees live on the **host git repo**, never inside the engine folder.
