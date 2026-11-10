---
description: Board LANDED means origin/main on GitHub — never local-only
alwaysApply: true
---

# LANDED = on `origin/main` (mandatory)

The board UI badge **LANDED** is a **GitHub fact**, not a local convenience label.

## Definition

| Claim | Required evidence |
|---|---|
| `land.json` `"status": "landed"` | `git push origin main` **succeeded** via `worktree-land-finish.mjs` |
| Feature is “on origin” | `worktree-push.mjs` published `origin/feature/{n}-{slug}` |
| Ready to land | `worktree-origin-ready.mjs` → `originReady: true` |
| Board row **%** after LANDED | **100%** — every ticket in **`summarize`** with checklists complete (`worktree-land-finish` land hygiene) |

## Forbidden (this is how false LANDED badges appear)

- Hand-writing `.linear/epics/*/summaries/land.json` with `"status":"landed"` after a **local** merge only
- Merging `feature/*` into local `main` and calling it landed without push
- Checking out plugin files onto local `main` and skipping `worktree-push` + land-main pipeline
- Setting LANDED while `origin/main` still lacks the epic merge (non-fast-forward / auth failure = **not** landed)
- Leaving tickets in `done` / `backlog` so the UI shows **LANDED + 83%** (or 0%) — promote to `summarize` before snap
- Restoring a LANDED snapshot that still parks tickets in `done` without running land hygiene (`board.mjs restore` auto-hygiene + `board.mjs land-hygiene all`)

## Correct pipeline (do not shortcut)

```text
tickets done
  → worktree-integrate
  → summarize + handoff
  → worktree-push          # origin/feature/{n}-{slug}
  → (all epics) worktree-origin-ready
  → worktree-land-next / land-main / land-smoke
  → worktree-land-finish   # ONLY this may set status=landed AND push origin main
```

## Board / API

`readLandStatus` must **not** trust a hand-written `landed` file if `origin/main` does not contain the epic. Prefer inferring `landed` from `origin/feature/{n}-{slug}` ⊆ `origin/main`.

## Related

- `.ai/rules/epic-land-main.md`
- `.ai/rules/no-git-push.md` (push `main` only via land-finish)
- `.ai/specs/epic-land-main.md`
