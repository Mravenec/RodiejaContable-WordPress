# `.ai/scripts/`

Harness orchestration (agent control plane). Not product app code.

| Script | Purpose |
|---|---|
| `epic-new.mjs` | Create **journey + `.linear` epic** (two pillars only) |
| `worktree-add.mjs` | **Required** after `claim`. Starts from committed parked same-epic ticket or done `blocked_by` (any epic). Fails if a parked dep is dirty. |
| `layer-audit.mjs` | Stack rules in code: no DTO classes/packages, no `Map` params in `I*Service` / `I*Repository`, no `Map` returns from `I*Repository`, every service uses an `I*Repository` (allowlist `services_without_repository` in `.ai/project.yaml`), every repository uses JOOQ, interface injection, no JPA, `DSLContext` only in repositories, `.http` under `src/test/java/.../http/`, no FE `dto/`, pages/hooks never call HTTP directly (pages call hooks, hooks call services). `--root <tree>`; skips when `module_name` is null. |
| `worktree-commit.mjs` | Local-commit one ticket folder (never push). Like finish / integrate / push, refuses a folder that is not its own worktree, the primary, a non `ticket/`/`feature/` branch, or new undeclared gitlinks. |
| `worktree-finish.mjs` | Layer audit, then commit, then `move done` (unlocks dependents). Refuses on audit violations. |
| `worktree-rm.mjs` | Manual escape hatch for one leftover ticket folder |
| `worktree-integrate.mjs` | After every ticket is in `done`: **apply** commits + merges into `feature/{n}-{slug}` / `_epic`, then run the **layer audit** on `_epic` (refuses before persist + prune), then **remove ticket folders**. `--print-only` is debug. |
| `worktree-push.mjs` | After `handoff`: persist docs on `_epic`, publish `feature/{n}-{slug}`, then drop `_epic`. |
| `worktree-origin-ready.mjs` | Every live epic has `origin/feature/{n}-{slug}`? **Does not merge `main`.** |
| `worktree-land-next.mjs` | After origin-ready: next epic to land onto `main` |
| `worktree-land-main.mjs` | Merge `origin/feature/{n}-{slug}` into `main` (serial); start land clock. Unlinks only colliding untracked persist files; refuses (never deletes) other colliding paths and undeclared gitlinks |
| `worktree-land-smoke.mjs` | Cumulative smoke after merge (`--pass` / `--fail`); refuses a pass when the layer audit fails on `main` |
| `worktree-land-finish.mjs` | Land HTML + journey + freeze land clock + push `main` |
| `worktree-land-ready.mjs` | Every epic landed on `main`? |
| `worktree-after-push.mjs` | Escape hatch if origin already has the branch and `_epic` was left. |

```bash
node .ai/scripts/epic-new.mjs <n> <slug> --title "…"
node .ai/scripts/worktree-add.mjs APP-1-2
node .ai/scripts/worktree-finish.mjs APP-1-2 --agent backend-agent-1
node .ai/scripts/worktree-integrate.mjs 1
node .ai/scripts/worktree-push.mjs 1
node .ai/scripts/worktree-origin-ready.mjs
node .ai/scripts/worktree-land-next.mjs
node .ai/scripts/worktree-land-main.mjs 1
node .ai/scripts/worktree-land-smoke.mjs 1 --pass
node .ai/scripts/worktree-land-finish.mjs 1 --note-file path/to/land-note.txt
node .ai/scripts/worktree-land-ready.mjs
node .ai/scripts/worktree-after-push.mjs 1
node .ai/scripts/worktree-rm.mjs APP-1-2
```

- Narrative → `.docs/journey/` (see that README for what to write)
- Tickets → `.linear/…/board/` issue files
- Board CLI → `.linear/scripts/`
