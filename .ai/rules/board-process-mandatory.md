---
description: Always use .linear + worktrees + .docs like the Spring harness — WordPress stack
alwaysApply: true
---

# Board process mandatory (Rodieja Contable · WordPress)

Same **discipline** as `HarnessMariaDB-SpringBootJOOQ-ReactJS`. This is a **product** repo: the board is expected to have epics; every change rides `.linear` + `.docs` + worktrees.

## Never do this

- Feature / fix / UI / meta work **only on `main`** with no epic/ticket.
- Skip `claim` → `worktree-add` → checklist/evidence → `testing` → `worktree-finish`.
- Leave `.docs/journey/{n}-{slug}.md` as “Planned” stubs after work lands (or omit the journey file for a board epic).
- Skip `.docs/board/{n}-{slug}/` snapshots.
- Fake LANDED without `worktree-land-finish` → `origin/main`.

## Always do this

1. **Orchestrate** (large briefs): all epics + tickets before implementation claims.
2. Fill **journey** (why / acceptance / agent notes / verify) as the epic progresses.
3. `claim` → **required** `node .ai/scripts/worktree-add.mjs <ID>`.
4. `doing`: code + `checklist` + `evidence` (compact).
5. `move testing` → layer-audit + HTTP/caps gates → `worktree-finish`.
6. All tickets `done` → `worktree-integrate` → summarize + handoff → `worktree-push`.
7. Land onto `main` in epic order → **LANDED** = on GitHub `origin/main`.

## WordPress good practices (every ticket)

- Native-first domain map in `AGENTS.md` (CPT/meta/tax/Options/Users — not Spring tables).
- Plugin: Contracts → Repositories → Services → Controllers + Security → GATE_HTTP.
- Constructor DI · `layer-audit.mjs` must stay green (no service without repository unless allowlisted).
- Sanitize / escape / caps / nonces / prepared SQL / real REST `permission_callback`.

Parity: Spring harness **process**; Rodieja **WordPress native stack**.
