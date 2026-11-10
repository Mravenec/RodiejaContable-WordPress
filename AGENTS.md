# Agent Guide — MariaDB · WordPress · Custom Plugins

Neutral harness. Day zero = WordPress + empty-domain plugin scaffold + **private.board** + worktrees. **Native WordPress only.**

Board of record: **`.linear/`** (private.board — files only, no cloud API).

## Stack

| Layer | Path | Tech |
|---|---|---|
| Database | `0.database/` + DDEV MariaDB | `dbDelta` / `$wpdb` / canonical SQL |
| Hardening | `0.security/mu-plugins/` | Always-on MU-plugin |
| Plugin | `1.plugin/<module_name>/` | PHP 8.2+ OOP · Composer PSR-4 · constructor DI |
| WordPress | `web/` | Core via `ddev wp` (do not commit full core) |
| Diagrams | `3.diagrams/` | dbdiagram · PlantUML |
| Board | `.linear/` | private.board CLI + optional UI |
| Worktrees | `.worktrees/` | one ticket = one folder (via `.ai/scripts/worktree-*.mjs`) |

## Roles

| Role | Scope | Testing gate |
|---|---|---|
| `database` | `0.database/` | Schema + DBML · `tests_ok` |
| `plugin` | `1.plugin/` | Layers → REST/admin smoke · `http_ok` / `tests_ok` |
| `security` | `0.security/` + plugin Security | Hardening verify · `tests_ok` |
| `orchestrator` | `.linear` + docs | create epic/issues · `next` · hygiene — no product features |

## Non-negotiable: native only

**Allowed:** hooks/filters, REST API, `$wpdb`, `dbDelta`, Options API, Capabilities, Nonces, sanitize/escape APIs, WP-CLI, Composer autoload, PHPUnit, WPCS.

**Forbidden:** Laravel/Illuminate, Symfony as app framework, Doctrine/Eloquent, React/Vite SPA as primary UI on day zero, eval/unserialize of user input, raw SQL concatenation, REST `permission_callback` => `__return_true` on write routes.

## Layer order (never invert)

```
GATE_SQL / dbDelta
  → Contracts
  → Repositories ($wpdb prepared)
  → Services
  → Controllers (REST / Admin)
  → Security on every entrypoint
  → ddev wp / curl REST smoke   ← GATE_HTTP
```

## Board + worktrees

```bash
# Empty board
node .linear/scripts/board.mjs epics

# New epic = journey + board
node .ai/scripts/epic-new.mjs 1 my-feature --title "My first feature"

node .linear/scripts/board.mjs next --role plugin --epic 1
node .linear/scripts/board.mjs claim APP-1-1 --agent plugin-agent-1

# REQUIRED after every claim — isolate code
node .ai/scripts/worktree-add.mjs APP-1-1

# implement in doing → move testing → verify flags → worktree-finish
node .linear/scripts/board.mjs move APP-1-1 testing --agent plugin-agent-1
node .ai/scripts/worktree-finish.mjs APP-1-1 --agent plugin-agent-1

# when every ticket in the epic is done:
node .ai/scripts/worktree-integrate.mjs 1
node .linear/scripts/board.mjs move APP-1-1 summarize --agent plugin-agent-1
node .linear/scripts/board.mjs handoff 1 --git "…" --note-file summaries/handoff-note.txt
node .ai/scripts/worktree-push.mjs 1
```

Optional UI: `node .linear/scripts/api.mjs` + `npm run ui --prefix .linear/ui` → http://127.0.0.1:47382

## Security checklist (every ticket)

1. Validate + sanitize input at the edge.
2. Escape all output for context.
3. Capability check **and** nonce (admin/AJAX) before state changes.
4. REST: real `permission_callback`; schema `args` with sanitize/validate.
5. SQL: `$wpdb->prepare` / CRUD helpers only; use `$wpdb->prefix`.
6. `node .ai/scripts/layer-audit.mjs` must stay green.

## Commands

```bash
ddev start
ddev composer install
ddev wp plugin activate harness-plugin
curl -s https://harness-wp.ddev.site/wp-json/harness/v1/health
node .ai/scripts/layer-audit.mjs
```

Module folder: `harness-plugin` (`.ai/project.yaml`). Rename only with an explicit human decision.
