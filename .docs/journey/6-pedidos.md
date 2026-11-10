# Epic 6 — pedidos

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/6-pedidos/` · Snapshot: `.docs/board/6-pedidos/`

## Title

Orders native: Audatex-shaped meta + items + REST

## Why

Audatex/INS-shaped orders need WP modeling (meta + child items + REST CRUD with real permission_callback).

## What we did

- [x] Audatex-shaped order meta
- [x] Child CPT rodieja_pedido_item + admin
- [x] REST CRUD with permission_callback + seed

## Acceptance

- Given an order with items
- When admin/authenticated REST runs
- Expect cap-gated CRUD; anonymous denied

## Agent notes (constraints / edge cases)

Live Audatex API out of scope (epic 14 = local import).

## Board issues (= tickets)

- APP-6-1 Audatex meta
- APP-6-2 item CPT
- APP-6-3 REST CRUD

## How to verify

- `edit.php?post_type=rodieja_pedido`
- `REST orders 401 anon`

```bash
node .linear/scripts/board.mjs progress --epic 6
```

## Outcome

Native orders LANDED.
