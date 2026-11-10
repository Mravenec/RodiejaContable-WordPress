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

## LANDED = GitHub `origin/main`

Board **LANDED** requires `worktree-push` (feature) then land-main → `worktree-land-finish` (successful `git push origin main`). Local merge alone is not landed. See `.ai/rules/landed-means-origin-main.md`.
