# Git worktrees (host repo)

Worktrees isolate **ticket code** on the host git repo. They do **not** live in this engine folder. Names follow the epic key `{n}-{slug}` (same as journey + board folder + UI row).

**One board ticket = one folder** while that ticket is in `doing` / `testing` / `done`. After `worktree-integrate`, those APP-* folders are removed (integrate **fails** if Windows still locks one). After `worktree-push`, `_epic` and leftover ticket folders are removed too. The branch stays on origin.

Ticket branches are `ticket/{n}-{slug}/{ISSUE_ID}` because git cannot store both `feature/{n}-{slug}` (epic) and `feature/{n}-{slug}/{ISSUE_ID}` (ticket). The epic ships as `feature/{n}-{slug}` after integrate.

Host (from this bundled `.linear/`):

```bash
node ../../.ai/scripts/worktree-add.mjs APP-1-2
node ../../.ai/scripts/worktree-integrate.mjs 1
node ../../.ai/scripts/worktree-push.mjs 1
node ../../.ai/scripts/worktree-origin-ready.mjs
node ../../.ai/scripts/worktree-rm.mjs APP-1-2
```

Engine copy (when this folder is the standalone board):

```bash
node scripts/worktree-add.mjs APP-1-2 --repo C:\path\to\host-harness
node scripts/worktree-integrate.mjs 1 --repo C:\path\to\host-harness
node scripts/worktree-push.mjs 1 --repo C:\path\to\host-harness
node scripts/worktree-origin-ready.mjs --repo C:\path\to\host-harness
```

Creates `<host>/.worktrees/{n}-{slug}/{ISSUE_ID}` on branch `ticket/{n}-{slug}/{ISSUE_ID}` and junctions `epics` + `agents` (unlink before remove). Does **not** create `feature/{n}-{slug}` until integrate. Ticket → `done` is a local commit (no ticket push). After every ticket is in `done`: merge, unlink junctions, stop java/vite holding ticket folders, **prune ticket folders** (fail if leftovers remain), smoke, summarize + `handoff` (git line + smoke note in the living HTML), then `worktree-push` (also sweeps leftover `APP-*`, commits persist docs on the epic branch). When every epic is on origin: `worktree-origin-ready` — do not merge `main` yet. Snapshot: `.docs/board/{n}-{slug}/`.

Commit guard (commit / finish / integrate / push): refuses before `git add` unless the folder is its own git worktree, is not the primary checkout, and is on a `ticket/` or `feature/` branch — a leftover plain folder would otherwise commit the primary onto `main`. Also refuses new gitlinks (nested clones) that `.gitmodules` does not declare. Fix the folder and re-run; never bypass with `git add -A` on the primary.

See host `.ai/rules/worktrees.md`.
