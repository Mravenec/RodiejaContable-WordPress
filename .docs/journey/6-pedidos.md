# Epic 6 — pedidos

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/6-pedidos/` · Snapshot: `.docs/board/6-pedidos/`

## Title

Pedidos nativos: meta Audatex-shape + items + REST

## Why

Pedidos del flujo Audatex/INS deben modelarse en WP (meta shape + ítems hijo + REST CRUD con permission_callback real).

## What we did

- [x] Meta pedidos Audatex-shape (cotización/WAN/VIN/…)
- [x] CPT hijo `rodieja_pedido_item` + admin
- [x] REST CRUD pedidos con permission_callback + seed

## Acceptance

- Given pedido con ítems
- When admin/REST autenticado
- Expect CRUD cap-gated; anónimo denegado

## Agent notes (constraints / edge cases)

Import INS live API queda fuera (epic 14 = import local).

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Pedidos + REST | sí |  |

## Board issues (= tickets)

- APP-6-1 meta Audatex-shape
- APP-6-2 CPT ítem
- APP-6-3 REST CRUD

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `edit.php?post_type=rodieja_pedido`
- `REST pedidos 401 anon`

```bash
node .linear/scripts/board.mjs progress --epic 6
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Pedidos nativos LANDED.
