---
id: APP-1-3
title: Docs · journey + native map in AGENTS
role: orchestrator
epic: 1
estimate: 1
blocked_by:
  - APP-1-2
blocks: []
agent: orchestrator-1
claimed_at: "2026-11-10T05:18:42.588Z"
---
## Objetivo

Docs · journey + native map in AGENTS

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### AGENTS native map

Path: `AGENTS.md`

```markdown
# Agent Guide — RodiejaContable-WordPress

Product on the **unified WordPress harness**. **Native-first** (`.ai/rules/native-first.md`).

Board of record: **`.linear/`**. Journeys: **`.docs/journey/`**.

## Layout

```
web/                          ← WordPress docroot
  wp-content/plugins/rodieja-contable/
  wp-content/mu-plugins/
.linear/ .ai/ .claude/ .cursor/ .ddev/ .docs/
```

## Native domain map (Spring → WP)

| Spring / SQL | Native WordPress |
|---|---|
| users / roles | WP Users + roles `rodieja_contador` (+ caps) |
| marcas / modelos / generaciones | CPTs + post meta / post relationships via meta |
| vehiculos | CPT `rodieja_vehiculo` + meta |
| parte_vehiculo | Taxonomy `rodieja_parte` |
| inventario_repuestos | CPT `rodieja_repuesto` + meta |
| empleados | CPT `rodieja_empleado` + meta (or user meta when linked) |
| tipos_transacciones | Taxonomy `rodieja_tipo_tx` |
| transacciones_financieras | CPT `rodieja_transaccion` + meta |
| pedidos (Audatex/INS) | CPT `rodieja_pedido` + meta (later epic) |
| UI | WP Admin menus / list tables / metaboxes (no React SPA) |
| API | Core REST for CPTs + thin `rodieja/v1/health` |

**No custom tables** unless native APIs are proven insufficient and human OK is recorded.

## Board

```bash
node .linear/scripts/board.mjs epics
node .ai/scripts/epic-new.mjs <n> <slug> --title "…"
# claim → worktree-add → checklist+evidence → testing → worktree-finish → integrate → summarize → handoff
```

Roles: `database` · `plugin` · `security` · `orchestrator`
```

## History

- 2026-11-10T05:18:43.450Z checklist item 2 marked
- 2026-11-10T05:18:43.359Z moved doing → testing
- 2026-11-10T05:18:43.132Z checklist item 1 marked
- 2026-11-10T05:18:42.590Z claimed by orchestrator-1 → doing
- 2026-11-10T05:12:45.895Z created → backlog
