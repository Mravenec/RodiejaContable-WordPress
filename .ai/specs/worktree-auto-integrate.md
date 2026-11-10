# Spec — automatic ticket commit and epic integrate

Neutral harness. Ordinary users never merge, restore, or commit ticket folders. They read the living HTML (recorded git line + Supervisor / Manager smoke note). Agents publish `feature/{n}-{slug}` via `worktree-push`.

## Failures this spec encodes

These happened when agents **proposed** git for a human who was already inside a worktree:

1. Nested path: `cd .worktrees/...` from inside a ticket folder → `.worktrees/.../.worktrees/...` does not exist.
2. Merge aborted: uncommitted `.ai/project.yaml` in the target tree. Human ran `git restore` then merge.
3. `worktree-add` start point only looked at **same-epic** parked tickets. Cross-epic `blocked_by` started from `main` / `HEAD` and missed identity SQL.
4. `move done` **before** a local commit unlocked dependents; later trees lacked files.
5. Parallel FE + next-epic DB while SQL/JOOQ are serial and backend tree still dirty.
6. Frontend `build_ok` while the browser Health page shows `Network Error` (no CORS / no Vite proxy).
7. `__pycache__` appearing in `git status`.
8. **Windows junction wipe:** `worktree-add` shares the primary `.linear/epics` + `.linear/agents` with `mklink /J`. On Windows a junction looks like a normal directory (`lstat.isSymbolicLink()` is false). `fs.rmSync({ recursive })` and `git worktree remove` then **follow the junction and delete the target** — the only live board. Cards, %, claims, and the living HTML vanish. `.linear/epics/*/` is gitignored, so git cannot restore them. **Required:** unlink the junction (`rmdir` without `/S`, or `unlink`) **before** removing the worktree. Never recurse-delete `.linear/epics` or `.linear/agents` from a ticket folder. The board source of truth is the **primary** checkout. A tracked copy lives under `.docs/board/{n}-{slug}/` (`.ai/rules/board-persist.md`).
9. **Windows locked leftover folders:** `git worktree remove` can unregister the worktree while Spring (`mvn spring-boot:run`) or Vite still has that folder as cwd. Windows then returns `Permission denied` / `Invalid argument`. Integrate used to print `integrated: true` with warnings and leave `APP-*` shells (no `.git`). After `worktree-push` dropped `_epic`, those folders stayed. `git worktree remove` then says `is not a working tree` and the old script stopped there. **Required:** if the path is not in `git worktree list`, skip git remove; stop processes whose command line / executable path contains that **ticket** folder (never the board UI under `.linear/ui`); unlink junctions; `rmSync` the orphan (retry). If any `APP-*` folder remains, **exit 1** (`integrated: false`). `worktree-push` sweeps leftovers again before removing the empty `{n}-{slug}` directory. Re-run integrate or `worktree-after-push` after the lock clears.
10. **Snapshots stranded on dirty `main`:** persist docs are written on the primary checkout; ticket trees never saw them. **Required:** finish / integrate / push copy `.docs/board` + journeys into the worktree and commit on `feature/{n}-{slug}`. Stop before merging `main` (`.ai/specs/epic-on-origin.md`).
11. **Leftover folder committed the primary onto `main`:** integrate found a ticket folder under `.worktrees/{n}-{slug}/` that was no longer a registered worktree (a plain directory). Git walked up to the primary checkout, so `git add -A` + commit ran on local `main` and staged an untracked nested clone (`zeroday/…`) as a **gitlink**. "Merge main into feature" then carried it into the epic branch. **Required:** every commit helper (`commitTicketDir` — used by `worktree-commit`, `worktree-finish`, `worktree-integrate`, `worktree-push`) refuses **before `git add`** unless the folder is its own git worktree toplevel, is not the primary checkout, and is on a `ticket/` or `feature/` branch. After `git add -A` it refuses (and resets the index; files stay on disk) when a new gitlink appears that `.gitmodules` does not declare. Fix: move real work into the ticket worktree, delete the leftover folder, gitignore nested clones, re-run.

## Scripts (host)

| Script | When | Effect |
|---|---|---|
| `worktree-add.mjs <ID>` | After every `claim` | Fails if a parked dep worktree is dirty. Starts from newest committed parked same-epic ticket **or** done `blocked_by` (any epic). |
| `worktree-commit.mjs <ID>` | Dirty parked tree blocking someone else | Local `git add -A` + `git commit`. Never push. Refuses a folder that is not its own worktree, the primary, a non `ticket/`/`feature/` branch, or new undeclared gitlinks (failure #11) — same guard in finish / integrate / push. |
| `worktree-finish.mjs <ID> --agent NAME` | Flags passed in `testing` | Commit if dirty, then `board move done`. |
| `worktree-integrate.mjs <n>` | Every ticket in the epic is `done` | Default **apply**: commit leftovers, create `feature/{n}-{slug}` + `_epic`, `git merge --no-ff` remaining ticket branches, then **remove ticket worktrees + merged `ticket/` branches**. Stops java/vite still using those folders. **Fails (exit 1)** if any `APP-*` folder remains. Only `_epic` remains when `integrated: true`. `--print-only` is debug, not the agent path. |
| `worktree-push.mjs <n>` | After `handoff` wrote the git line + smoke note | Pushes `feature/{n}-{slug}` (never `--force`, never `HEAD`), then removes `_epic`, leftover `APP-*` folders, the local epic branch, and the empty `.worktrees/{n}-{slug}/` directory. Copies persist docs into `_epic` and commits them first. |
| `worktree-origin-ready.mjs` | After epic pushes | Lists each live epic vs `origin/feature/{n}-{slug}`. **Does not merge `main`.** |
| `worktree-after-push.mjs <n>` | Origin already has the branch and `_epic` was left | Removes `_epic` and the local epic branch. Fails if origin does not have the branch yet. |

Engine copies take `--repo <host-git-root>`.

Board: `epic show` includes `blocked_by` + `column`. `move done` refuses a dirty ticket worktree (`--force` skips).

## When to merge (agents)

```text
blocked_by not done          → wait (cannot claim)
dep done but dirty           → wait; commit that dep; then worktree-add
serial layer needs files     → worktree-add already contains them (start point)
parallel independent tickets → no mid-doing merge
all epic tickets done        → worktree-integrate (merge, then prune APP-* folders)
conflict in _epic            → agent resolves, commit, re-run integrate
summarize + handoff          → living HTML gets the git line + Supervisor/Manager smoke note
then                         → worktree-push (publish feature/{n}-{slug}, drop _epic)
human                        → read the living HTML; confirm on origin if they want
```

## Human surface

- Branch they care about: `feature/{n}-{slug}` (already on origin after `worktree-push`)
- Page they care about: `epics/{n}-{slug}/summaries/{n}-{slug}-beginner.html`
- Git line stored in that HTML: `cd .worktrees/{n}-{slug}/_epic && git push -u origin feature/{n}-{slug}`
- Agent runs: `node .ai/scripts/worktree-push.mjs <n>` after `handoff`

The HTML must say ticket commits and merges already ran, and include the smoke walkthrough.

## Folder lifetime

Ticket folders exist only while that ticket is being built. After merge they are waste. After `worktree-push`, `_epic` is waste too — the commits live on origin.

| When | Keep | Remove |
|---|---|---|
| Ticket in `doing` / `testing` / `done` | `.worktrees/{n}-{slug}/{ISSUE_ID}` | — |
| After `worktree-integrate` | `_epic` + local `feature/{n}-{slug}` + primary `.linear/epics` + `.docs/board/` | APP-* folders + merged `ticket/` branches (junctions unlinked first) |
| After `worktree-push` | `origin/feature/{n}-{slug}` | `_epic`, leftover `APP-*` folders, local epic branch, empty `.worktrees/{n}-{slug}/` |

Do **not** leave APP-1-1…APP-1-N after integrate. Do **not** leave `_epic` or leftover ticket folders after a successful origin push. If Windows still holds a folder, integrate/push **fail** until a re-run deletes it (spec failure #9).

## Browser reach (not git, same collapse class)

Spring must allow Vite origins **or** Vite must proxy `/api` (and `/health`) to `http://127.0.0.1:8080`. Do not set `build_ok` if the UI shows `Health status: unavailable (Network Error)`.

See `.ai/rules/frontend-api-reach.md`.
