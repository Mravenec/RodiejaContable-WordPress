# Epic 13 — vistas_ejecutivas

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/13-vistas_ejecutivas/` · Snapshot: `.docs/board/13-vistas_ejecutivas/`

## Title

Executive views WP_Query (inventory, sales, profitability)

## Why

Leadership needs inventory/sales/profitability views and an executive dashboard with native counts/amounts.

## What we did

- [x] Admin pages for inventory/sales/profitability
- [x] Executive dashboard counts + amounts

## Acceptance

- Given seed data
- When executive views open
- Expect coherent numbers without external BI

## Agent notes (constraints / edge cases)

WP_Query/meta aggregations in PHP only.

## Board issues (= tickets)

- APP-13-1 admin views
- APP-13-2 executive dashboard

## How to verify

- `Reports views`
- `Home dashboard counts`

```bash
node .linear/scripts/board.mjs progress --epic 13
```

## Outcome

Executive views LANDED.
