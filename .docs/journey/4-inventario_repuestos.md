# Epic 4 — inventario_repuestos

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/4-inventario_repuestos/` · Snapshot: `.docs/board/4-inventario_repuestos/`

## Title

Parts inventory: stock, parts taxonomy, critical

## Why

Parts inventory needs stock/price, parts taxonomy, vehicle link, and a critical-stock admin view.

## What we did

- [x] Stock/price meta UI + rodieja_parte taxonomy
- [x] Part↔vehicle link in admin
- [x] Critical stock admin page via WP_Query

## Acceptance

- Given parts with low stock
- When critical stock opens
- Expect a native filtered list

## Agent notes (constraints / edge cases)

No custom inventory tables; meta + tax + query.

## Board issues (= tickets)

- APP-4-1 stock/price + tax
- APP-4-2 vehicle link
- APP-4-3 critical stock

## How to verify

- `edit.php?post_type=rodieja_repuesto`
- `Inventory → Critical stock`

```bash
node .linear/scripts/board.mjs progress --epic 4
```

## Outcome

Native inventory + critical stock LANDED.
