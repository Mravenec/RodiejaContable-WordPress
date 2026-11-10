# Epic 7 — admin_ux

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/7-admin_ux/` · Snapshot: `.docs/board/7-admin_ux/`

## Title

Admin UX: hub, resúmenes, empleado↔WP user

## Why

El admin debe sentirse como el hub Rodieja (menús/resúmenes) y enlazar empleados de dominio a usuarios WP (contador).

## What we did

- [x] Hub Rodieja con links CPT + seed
- [x] Pantallas resumen inventario/ventas nativas
- [x] Meta empleado `user_id` ↔ WP user

## Acceptance

- Given admin Rodieja
- When abre Inicio/hub
- Expect navegación a CPT y resúmenes sin SPA

## Agent notes (constraints / edge cases)

Chrome/menús React-shaped; implementación PHP nativa.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin AdminMenu/hub | sí |  |

## Board issues (= tickets)

- APP-7-1 hub
- APP-7-2 resúmenes
- APP-7-3 empleado↔user

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `wp-admin menú Inicio / Vehículos / Inventario`
- `perfil empleado meta`

```bash
node .linear/scripts/board.mjs progress --epic 7
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Admin UX hub LANDED. Menú jerárquico epic 17.
