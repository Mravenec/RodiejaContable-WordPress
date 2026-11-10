# Multi-agent

| Role | Path |
|---|---|
| database | `0.database/` (+ `3.diagrams/0.database/` dbdiagram.io) |
| backend | `1.backend/` (+ `3.diagrams/1.backend/` PlantUML) |
| frontend | `2.frontend/` (+ `3.diagrams/2.frontend/` Mermaid) |
| orchestrator | `.linear` + `.docs/journey` |

`blocked_by` + checklist handoff. One active issue per chain unless the plan allows parallel work. Communicate in **English only**.

`blocked_by` works **across epics** (board-wide). Dependents stay blocked until deps reach `done` (or later).

**Worktrees:** after **every** `claim`, `worktree-add` so `.worktrees/{n}-{slug}/{ISSUE_ID}` exists. Start from the **committed** dep (same epic or cross-epic `blocked_by`). If `worktree-add` fails because a parked tree is dirty, that owner commits first — other agents **wait**. Parallel independent tickets do **not** merge mid-doing. Ticket `done` is `worktree-finish` (commit then park). When every ticket in the epic is `done`, **agents** run `worktree-integrate` (prunes ticket folders; fails if leftovers remain), `handoff`, then `worktree-push` (publishes `feature/{n}-{slug}`, drops `_epic` and leftover ticket folders). Cap **2–3** agents on **independent** files. SQL + DBML + JOOQ stay serial. JWT (filter + Auth CTRL) stays serial with those backend layers (`.ai/rules/jwt-auth.md`). See `.ai/rules/worktrees.md`.

**Large briefs:** orchestrator creates every Epic + ticket (`blocked_by`, roles) **before** any implementation claim so agents do not collide. Single features stay one Epic. See `.ai/rules/orchestrate-first.md`.

Diagrams are not a fourth coding role: each stack role updates its slice of `3.diagrams/` in the same checklist item (`.ai/rules/diagrams.md`).
