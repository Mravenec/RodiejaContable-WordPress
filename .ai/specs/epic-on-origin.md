# Spec — snapshots ride `feature/{n}-{slug}`; stop before `main`

Neutral harness. After every epic is published, origin has `feature/{n}-{slug}` and the live board stays on the primary disk. Agents **do not** merge into `main` yet.

## Failures this spec encodes

10. **Snapshots stranded on dirty `main`:** board pulses write `.docs/board/{n}-{slug}/` and journeys live at `.docs/journey/{n}-{slug}.md` on the **primary** checkout. Ticket / `_epic` worktrees have a separate working tree, so `git add -A` inside a ticket folder never saw those files. They stayed untracked on `main`. Git then refuses merge/pull. **Required:** `worktree-finish`, `worktree-integrate`, and `worktree-push` copy persist docs into the worktree and commit them on the ticket / epic branch. Never commit live `.linear/epics/*/` (gitignored). Never invent `feature/board`. Do not merge those branches into `main` in this step.

## What is in git vs on disk

| Copy | Path | In git? |
|---|---|---|
| Live board (UI, claims, %) | `.linear/epics/{n}-{slug}/` | **No** — `epics/*/` is gitignored so `main` stays day zero |
| Recovery snapshot | `.docs/board/{n}-{slug}/` | **Yes** — on `ticket/` then `feature/{n}-{slug}` |
| Story | `.docs/journey/{n}-{slug}.md` | **Yes** — same branches |

Worktrees share **one** live board (junctions). They do not each own a Linear.

## Automatic path (this spec)

```text
epic-new / board pulses     → snapshot on primary .docs/board (still untracked on main)
worktree-finish / commit    → copy persist docs into the ticket tree, commit there
worktree-integrate          → merge tickets, copy persist docs into _epic, commit
handoff                     → living HTML + snapshot on primary
worktree-push               → copy persist docs into _epic, commit if needed, push feature/{n}-{slug}, drop _epic
worktree-origin-ready       → every board epic has origin/feature/{n}-{slug} → STOP
```

When **every** planned epic has been `worktree-push`ed:

- Live board: still `.linear/epics/` on the primary checkout
- Product + snapshots + journeys: on `origin/feature/{n}-{slug}` (one branch per epic)
- `main`: still day zero in git history
- Primary `git status` may still show untracked `.docs/board` / `.docs/journey` while you are checked out on `main`. That is expected. **Do not** `git add .` on `main` to “fix” it.

## Next gate (separate spec)

Merging `feature/1-…` then `feature/2-…` onto `main` (in epic order) is **`.ai/specs/epic-land-main.md`**. Do not improvise merges in this gate.

`git checkout -b feature/board` is a rescue for dirt already stuck on `main`, not the designed path.

## Scripts

| Script | When |
|---|---|
| `worktree-finish` / `worktree-commit` | Copies `.docs/board/*` + `.docs/journey/*.md` from primary into the ticket folder, then commits |
| `worktree-integrate` | Same into `_epic` after ticket merges |
| `worktree-push` | Same into `_epic` after handoff, then publishes `feature/{n}-{slug}` |
| `worktree-origin-ready.mjs` | Lists each epic vs origin; **does not merge** |

Rule: `.ai/rules/epic-on-origin.md` · skill: `.ai/skills/epic-on-origin/SKILL.md` · persist: `.ai/rules/board-persist.md`
