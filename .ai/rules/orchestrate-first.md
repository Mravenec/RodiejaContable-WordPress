# Orchestrate first (large briefs)

When the human brings a **large** product brief (many features, full product manual, multi-domain doc):

1. **Do not** start coding schema/API/UI yet.
2. Orchestrator **segments** the brief into Epics (journey + board) and creates **all** Epics with their tickets (`blocked_by` chains, roles) **before** any `claim` on implementation issues.
3. Multi-agent work starts only after the board map exists so database / backend / frontend do not collide.
4. Smaller single-feature requests still use one Epic + tickets — no need to invent a giant plan.

## Cross-epic waiting

`blocked_by` may reference issue IDs in **another** epic (example: Epic 2 DB waits on Epic 1 schema ticket). The board resolves dependencies across the whole board: `next` / `claim` stay blocked until each dependency reaches `unlock_column` (default: `done`).

Within an epic, keep the usual DB → BE → FE chain. Across epics, link only the real gates (shared schema, GATE_HTTP, etc.) — do not over-block.

Neutral: never bake a product domain into the harness; only the process stays fixed.

Details: `.ai/skills/journey-epic/SKILL.md` · root `AGENTS.md`
