# Epic 4 — inventario_repuestos

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/4-inventario_repuestos/` · Snapshot: `.docs/board/4-inventario_repuestos/`

## Title

Inventario repuestos: stock, partes, crítico

## Why

Inventario de repuestos necesita stock/precio, taxonomía de partes, vínculo a vehículo y alerta de stock crítico en admin.

## What we did

- [x] Meta UI stock/precio + tax `rodieja_parte`
- [x] Vínculo repuesto↔vehículo en admin
- [x] Página admin stock crítico vía WP_Query

## Acceptance

- Given repuestos con stock bajo
- When se abre stock crítico
- Expect listado filtrado nativo

## Agent notes (constraints / edge cases)

Sin tablas custom de inventario; meta + tax + query.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin | sí | Repuestos CPT/meta/tax + CriticalStockPage |

## Board issues (= tickets)

- APP-4-1 stock/precio + tax
- APP-4-2 vínculo vehículo
- APP-4-3 stock crítico

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `edit.php?post_type=rodieja_repuesto`
- `menú Inventario → Stock crítico`

```bash
node .linear/scripts/board.mjs progress --epic 4
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Inventario nativo + crítico LANDED.
