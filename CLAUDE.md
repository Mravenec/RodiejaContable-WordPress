# CLAUDE / Cursor agents

Follow **[AGENTS.md](./AGENTS.md)**.

This host is a **unified WordPress project** (`web/` docroot). Do **not** recreate Spring-style `0.database` / `1.backend` / `2.frontend` folders.

**Native-first:** prefer core WP APIs. Custom tables/packages only after a short proposal and **human OK** (`.ai/rules/native-first.md`). DI/SOLID live **inside** `web/wp-content/plugins/<module>/` when a plugin is justified — not as a reason to skip CPT/Options/REST.

Tool adapters must stay in **harmony** (`.ai/rules/tool-adapters-harmony.md`). Prefer `.ai/` first; mirrors: `.claude/` · `.cursor/` · `AGENTS.md` · `CLAUDE.md`.

| Tool | Path | Source of truth |
|---|---|---|
| Shared | `.ai/rules/` · `.ai/skills/` · `.ai/scripts/` | Canonical |
| Cursor | `.cursor/rules/` | Mirror |
| Claude Code | `.claude/skills/` · `.claude/settings.json` | Mirror |
| Entry | `AGENTS.md` · `CLAUDE.md` | Must agree |

- Board: `.linear/` — **0 epics on day zero**
- Flow: `next` → `claim` → checklist+evidence in `doing` → `move testing` → flags → `worktree-finish` → (all done) → integrate → `summarize` + `handoff` → `worktree-push`
- Stack harness: `mariadb-wordpress-php-plugins`
- Layout: `web/wp-content/plugins/<module_name>/` + `mu-plugins/` + optional `docs/`
- Plugin order: Contracts → Repositories (`$wpdb`) → Services → Controllers + Security → GATE_HTTP
- **Native-first** — core APIs first; custom only with human OK; no Laravel/Doctrine; no segmented multi-app folders
- Layer audit: `node .ai/scripts/layer-audit.mjs`
- Worktrees: after **every** `claim`, `worktree-add`
- **git push:** denied in `.claude/settings.json`
