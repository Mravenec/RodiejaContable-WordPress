# Agent Guide — RodiejaContable-WordPress

Product on the **unified WordPress harness**. **Native-first** (`.ai/rules/native-first.md`).

**Process parity with** `HarnessMariaDB-SpringBootJOOQ-ReactJS`: always `.linear` + worktrees + `.docs/journey` + `.docs/board` + land. Stack is WordPress — see `.ai/rules/board-process-mandatory.md`.

Board of record: **`.linear/`**. Journeys: **`.docs/journey/`**. Snapshots: **`.docs/board/`**.

## Layout

```
web/                          ← WordPress docroot
  wp-content/plugins/rodieja-contable/
  wp-content/mu-plugins/
.linear/ .ai/ .claude/ .cursor/ .ddev/ .docs/
```

## Epic = journey + board (two pillars)

| Artifact | Role |
|---|---|
| `.docs/journey/{n}-{slug}.md` | Full epic story — keep updated (not “Planned” stubs after work) |
| `.linear/epics/{n}-{slug}/` | Tickets · backlog→…→summarize · % |
| `.docs/board/{n}-{slug}/` | Tracked snapshot after pulses / finish / push / land |

## Process (mandatory)

### Large briefs

1. Orchestrate all epics + tickets first.
2. Per ticket: `claim` → **`worktree-add`** → checklist+evidence → `testing` → `worktree-finish`.
3. All `done` → integrate → summarize + handoff → `worktree-push` → land to **`origin/main`** (LANDED).

### Single feature / UI polish / meta field

Still an epic (or ticket on an open epic) + worktree. **No drive-by commits on `main`.**

```bash
node .linear/scripts/board.mjs epics
node .ai/scripts/epic-new.mjs <n> <slug> --title "…"
node .linear/scripts/board.mjs claim APP-n-k --agent plugin-agent-1
node .ai/scripts/worktree-add.mjs APP-n-k
# … checklist + evidence …
node .ai/scripts/worktree-finish.mjs APP-n-k --agent plugin-agent-1
```

Roles: `database` · `plugin` · `security` · `orchestrator`

## Native domain map (Spring / SQL → WP)

| Spring / SQL | Native WordPress |
|---|---|
| users / roles | WP Users + roles/caps |
| marcas / modelos / generaciones | CPTs + post meta |
| vehiculos | CPT `rodieja_vehiculo` + meta |
| parte_vehiculo | Taxonomy `rodieja_parte` |
| inventario_repuestos | CPT `rodieja_repuesto` + meta |
| empleados | CPT `rodieja_empleado` + meta (± user link) |
| tipos_transacciones | Taxonomy `rodieja_tipo_tx` |
| transacciones_financieras | CPT `rodieja_transaccion` + meta |
| pedidos Audatex/INS | CPT `rodieja_pedido` (+ items) + meta |
| UI | WP Admin menus / list tables / metaboxes (no React SPA) |
| API | Core REST for CPTs + thin `rodieja/v1/*` |

**Domain parity goal:** same entities/fields as Read-Only, stored the WordPress way. **No custom tables** unless native APIs are insufficient and human OK is on the ticket.

## Layer order (never invert)

```
Contracts
  → Repositories (WP APIs / $wpdb prepared)
  → Services
  → Controllers (REST / Admin)
  → Security
  → GATE_HTTP
  → node .ai/scripts/layer-audit.mjs   # must be green
```

Constructor DI via `ServiceProvider` + `Container`. PHPUnit / WPCS when touching plugin code.

## Security (every ticket)

Sanitize early · escape late · validate always · caps + nonces · prepared SQL · real REST `permission_callback`.

Claude: **[CLAUDE.md](./CLAUDE.md)**.
