# Epic 2 — native_domain

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/2-native_domain/` · Snapshot: `.docs/board/2-native_domain/`

## Title

Native domain: seed + CPT admin smoke

## Why

Hay que materializar el dominio Rodieja como CPT/meta nativos y poder sembrar datos demo para humo admin/REST, sin tablas Spring.

## What we did

- [x] Seed demo (vehículos, pedidos, finanzas) vía servicios nativos
- [x] WP-CLI + REST smoke de counts
- [x] Confirmación land status en board

## Acceptance

- Given plugin activo
- When corre el seed
- Expect CPT demo visibles en wp-admin y counts REST coherentes

## Agent notes (constraints / edge cases)

Solo APIs WP (wp_insert_post/meta). Seed idempotente preferible.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin | sí | Seed + CPT registration smoke |
| MU-plugin | — |  |

## Board issues (= tickets)

- APP-2-1 ensure demo seed
- APP-2-2 WP-CLI + REST smoke + land

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `ddev wp rodieja… seed (si existe)`
- `REST stats/counts`
- `wp-admin CPT lists`

```bash
node .linear/scripts/board.mjs progress --epic 2
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Dominio sembrado y humo OK; LANDED. Catálogo admin en epic 3.
