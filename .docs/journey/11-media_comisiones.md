# Epic 11 — media_comisiones

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/11-media_comisiones/` · Snapshot: `.docs/board/11-media_comisiones/`

## Title

Media Library + pagos/comisiones meta en pedidos

## Why

Vehículos/repuestos necesitan media WP; pedidos necesitan meta de comisión/pago visible en admin.

## What we did

- [x] Featured image / gallery en vehículo y repuesto
- [x] Meta comisión/pago en pedido + columnas admin

## Acceptance

- Given un vehículo con imagen
- When se edita en admin
- Expect Media Library nativa; pedidos muestran comisión

## Agent notes (constraints / edge cases)

Usar Media Library core; no CDN custom.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin media + meta pedidos | sí |  |

## Board issues (= tickets)

- APP-11-1 featured/gallery
- APP-11-2 comisión/pago meta

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `editor vehículo media`
- `lista pedidos columnas`

```bash
node .linear/scripts/board.mjs progress --epic 11
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Media + comisiones LANDED.
