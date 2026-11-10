# Spec — land `feature/{n}-{slug}` onto `main` (after origin-ready)

Neutral harness. After `worktree-origin-ready` reports `originReady: true`, agents land epic branches onto `main` **in epic order** (1, then 2, …). Each land is billable (separate clock) and recorded in the living HTML + journey.

## Failures this spec encodes

11. **Stopped forever at origin:** `epic-on-origin` correctly refuses to merge `main`, but left no next automation. **Required:** after `originReady`, agents run the land pipeline (next → merge → smoke → finish → push `main`).

19. **False LANDED badge:** agents wrote `summaries/land.json` with `"status":"landed"` after a **local** merge (or plugin checkout onto local `main`) without `worktree-push` / `worktree-land-finish` / successful `git push origin main`. The board UI then showed LANDED while GitHub `origin/main` was unchanged. **Required:** only `worktree-land-finish` sets `landed`, and only **after** push succeeds; `readLandStatus` must not trust a hand-written `landed` file unless `origin/feature/{n}-{slug}` is an ancestor of `origin/main` (`.ai/rules/landed-means-origin-main.md`).

12. **Out-of-order land:** merging epic 5 before 1–4 breaks schema/API/UI. **Required:** `worktree-land-next` always picks the lowest epic not yet `landed`. Refuse `worktree-land-main <n>` if a lower epic is not landed.

13. **Dirty primary blocks merge:** untracked `.docs/board` / journeys on `main` collide with the same paths arriving from `feature/*`. Feature branches may carry **all** orchestrated persist docs (not only that epic). **Required:** before merge, list untracked files **one by one** (`git status --untracked-files=all`) and unlink only the untracked **persist files** that appear in `git diff HEAD...origin/feature/{n}-{slug}`. Never recurse-delete a directory. Refuse if non-persist dirty or untracked paths overlap the merge.

17. **Land deleted untracked folders:** the persist filter in the clear step was dead code, and the overlap check compared `zeroday/` (porcelain keeps the trailing slash) as `zeroday//`. Every untracked path touching the feature diff was recursively deleted — the nested neutral clone `zeroday/`, and in a sandbox `tools/` and an entire collapsed `.docs/`. **Required:** normalize trailing slashes before overlap checks; delete nothing outside persist files (failure #13); refuse and leave the path on disk otherwise.

18. **Gitlink landed from a feature branch:** an accidental commit of a nested clone (spec `worktree-auto-integrate` failure #11) reached `feature/*`. **Required:** `worktree-land-main` refuses before merging when the feature diff adds a gitlink (mode `160000`) that the branch's `.gitmodules` does not declare. Fix on the feature branch (`git rm --cached <path>`, gitignore the folder), push again, re-run land-main.

14. **Land time not billable:** build clock freezes at summarize `handoff`. **Required:** a separate `land` clock in `summaries/time.json` (claim/start → finish), included in Hours worked.

15. **No human land summary:** summarize handoff describes the feature push only. **Required:** living HTML gains a **Land on main** section (merge commit, conflicts, cumulative smoke walkthrough). Journey status becomes `Landed on main`.

## States (epic lifecycle — not ticket columns)

```text
building → on-origin → landing → smoke-main → landed
```

Stored in `.linear/epics/{n}-{slug}/summaries/land.json` (live board; also snapped into `.docs/board/{n}-{slug}/`).

| status | Meaning |
|---|---|
| `on-origin` | `origin/feature/{n}-{slug}` exists; not merged to `main` |
| `landing` | Merge in progress or conflicts being resolved |
| `smoke-main` | Merge commit on `main`; cumulative smoke running |
| `landed` | Smoke OK, land handoff recorded, **`git push origin main` succeeded** (GitHub `origin/main` has the merge). Local-only merge ≠ landed. |

Ticket columns stay `backlog…summarize`. Do **not** invent Review/QA ticket columns for land.

## Automatic path

```text
worktree-origin-ready     → originReady: true
worktree-land-next        → lowest epic not landed
worktree-land-main <n>    → refuse undeclared gitlinks + colliding non-persist paths,
                            start land clock, unlink colliding untracked persist files,
                            fetch, merge origin/feature/{n}-{slug} into main (--no-ff)
                            (agent resolves conflicts, re-runs)
worktree-land-smoke <n>   → cumulative verify (journey + prior landed epics)
worktree-land-finish <n>  → land handoff into living HTML, freeze land clock,
                            update journey Status, snapshot board
git push origin main      → never --force (via land-finish or explicit push helper)
worktree-land-ready       → every epic landed → STOP
```

## Clock

`summaries/time.json` gains:

```json
"land": {
  "started_at": null,
  "finished_at": null,
  "open": null,
  "segments": [],
  "paused": false
}
```

Same `idle_ms` rules as build. Hours worked sums **build** (issues) + **land** for the month.

16. **Smoke without runtime:** `GET /health` may be down during land. **Required:** agent may `--pass` only after recording a Supervisor land note that includes the full cumulative smoke walkthrough. Prefer probing Health when Spring is up.

## Scripts

| Script | When |
|---|---|
| `worktree-land-next.mjs` | After origin-ready — print next epic to land |
| `worktree-land-main.mjs <n>` | Merge that epic into `main` |
| `worktree-land-smoke.mjs <n>` | Record / run cumulative smoke |
| `worktree-land-finish.mjs <n>` | Handoff + clock freeze + journey + push main |
| `worktree-land-ready.mjs` | All epics landed? |

## Do not

- Merge out of order
- `git push --force` to `main`
- `git add .` on `main` for persist docs before merge (they ride the feature branch)
- Skip smoke or skip the land summary in the living HTML
- Start land while `originReady` is false
- Delete untracked non-persist paths (or whole folders) to make a merge fit — park or move them instead
- Land a feature branch that carries an undeclared gitlink

Rule: `.ai/rules/epic-land-main.md` · skill: `.ai/skills/epic-land-main/SKILL.md` · prior gate: `.ai/specs/epic-on-origin.md`
