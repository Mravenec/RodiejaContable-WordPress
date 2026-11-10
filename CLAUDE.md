# CLAUDE / Cursor agents — Rodieja Contable (WordPress)

Follow **[AGENTS.md](./AGENTS.md)**.

**Process = Spring harness discipline** (`.linear` + worktrees + `.docs` + land) on the WordPress stack. See `.ai/rules/board-process-mandatory.md`.

**Never** ship product changes only on `main` without a ticket + worktree. Keep journeys and `.docs/board` snapshots current.

**Native-first** + plugin layers: Contracts → Repositories → Services → Controllers + Security. `layer-audit.mjs` must stay green.

Harmony: `.ai/` canonical · `.cursor/` · `.claude/` · `AGENTS.md` · `CLAUDE.md`.

- Flow: `claim` → `worktree-add` → checklist+evidence → testing → finish → integrate → summarize/handoff → push → land
- **LANDED** = GitHub `origin/main`
- **git push:** use harness scripts only
