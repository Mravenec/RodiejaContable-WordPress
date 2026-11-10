# Epic 13 — vistas_ejecutivas

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/13-vistas_ejecutivas/` · Snapshot: `.docs/board/13-vistas_ejecutivas/`

## Title

Vistas ejecutivas WP_Query (inventario, ventas, rentabilidad)

## Why

Dirección necesita vistas inventario/ventas/rentabilidad y un dashboard ejecutivo con counts/montos nativos.

## What we did

- [x] Admin pages vistas inventario/ventas/rentabilidad
- [x] Dashboard ejecutivo counts + montos (WP_Query/meta)

## Acceptance

- Given datos seed
- When abre vistas ejecutivas
- Expect números coherentes sin BI externo

## Agent notes (constraints / edge cases)

Solo WP_Query/meta aggregations en PHP.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin report pages + ExecutiveDashboard | sí |  |

## Board issues (= tickets)

- APP-13-1 vistas admin
- APP-13-2 dashboard ejecutivo

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `menú Reportes / vistas`
- `Inicio dashboard counts`

```bash
node .linear/scripts/board.mjs progress --epic 13
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Vistas ejecutivas LANDED.
