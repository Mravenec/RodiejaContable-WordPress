# Agent Guide — WordPress (unified) · MariaDB · Custom Plugins

Neutral harness on a **realistic WordPress project tree** (like guilford/porton/tandon shape: one CMS docroot + harness control plane). **Not** a Spring-style `0.`/`1.`/`2.` multi-app split.

**Native WordPress first (day-zero law).** Use core APIs before inventing custom tables/packages. Architecture (DI, SOLID) lives **inside** the plugin **when a plugin is justified** — see `.ai/rules/native-first.md`.

Board: **`.linear/`** (private.board). Claude: **[CLAUDE.md](./CLAUDE.md)** · `.claude/skills/`.

## Project shape (day zero)

```
.git / .ddev / .ai / .claude / .cursor / .linear / .docs
web/                                 ← WordPress docroot
  wp-content/
    plugins/harness-plugin/          ← custom plugin (OOP DI SOLID)
    mu-plugins/harness-hardening.php ← always-on hardening
docs/database/ · docs/diagrams/      ← optional mirrors (not separate apps)
```

| Path | Role |
|---|---|
| `web/` | WordPress core + content (core mostly gitignored) |
| `web/wp-content/plugins/<module>/` | Product code — Contracts → Repos → Services → Controllers |
| `web/wp-content/mu-plugins/` | Hardening MU-plugin |
| `docs/` | SQL/DBML/PlantUML twins for humans/agents |
| `.linear/` · `.ai/` | Board + worktrees + layer audit |

## Roles

| Role | Touches | Gate |
|---|---|---|
| `database` | plugin `dbDelta` + optional `docs/database` | `tests_ok` |
| `plugin` | `web/wp-content/plugins/<module>/` | `http_ok` / `tests_ok` |
| `security` | MU-plugin + `src/Security/` | `tests_ok` |
| `orchestrator` | `.linear` / journey / worktrees | hygiene only |

## Native-first gate

1. **Default:** CPT, taxonomies, meta, Options/Settings, `WP_Query`, core REST, caps, nonces, hooks, WP-Cron, Media, WP-CLI.
2. **If native seems insufficient:** propose native gap + custom plan → **ask the human** → only then implement custom (`dbDelta`, bespoke repos, etc.).
3. **Never silent custom.** Record the human decision on the ticket.
4. **Always forbidden:** Laravel/Illuminate, Doctrine/Eloquent, Spring-style `0/1/2` apps, REST `__return_true` on mutations.

**Allowed tooling:** Composer PSR-4, PHPUnit, WPCS, `$wpdb->prepare` when native object APIs are not enough (after the gate above).

## Inside the plugin (layer order)

```
dbDelta / schema
  → Contracts
  → Repositories ($wpdb prepared)
  → Services
  → Controllers (REST / Admin)
  → Security on every entrypoint
  → GATE_HTTP (curl / ddev wp)
```

## Board + worktrees

```bash
node .linear/scripts/board.mjs epics
node .ai/scripts/epic-new.mjs 1 my-feature --title "…"
node .linear/scripts/board.mjs claim APP-1-1 --agent plugin-agent-1
node .ai/scripts/worktree-add.mjs APP-1-1   # required after every claim
node .ai/scripts/layer-audit.mjs
```

## Security (every ticket)

Sanitize early · escape late · validate always · caps + nonces · prepared SQL · real REST `permission_callback`.

Module: `harness-plugin` (`.ai/project.yaml` → `plugin_path`).
