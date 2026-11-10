# Epic 5 — finanzas

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/5-finanzas/` · Snapshot: `.docs/board/5-finanzas/`

## Title

Finanzas: transacciones + dashboard WP_Query

## Why

Ingresos/egresos deben vivir como CPT/meta con dashboard de montos y REST mutaciones solo para caps correctas (contador/admin).

## What we did

- [x] Transacciones ingreso/egreso + tax + vínculos
- [x] Dashboard montos WP_Query/meta
- [x] Caps contador en REST mutaciones (no `__return_true`)

## Acceptance

- Given usuario contador autenticado
- When consulta dashboard/REST permitido
- Expect montos coherentes; anónimo 401/403

## Agent notes (constraints / edge cases)

Security ticket obligatorio en mutaciones.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Finanzas | sí |  |
| Security REST | sí |  |

## Board issues (= tickets)

- APP-5-1 transacciones
- APP-5-2 dashboard montos
- APP-5-3 caps REST

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `menú Finanzas`
- `REST mutación anon vs contador`
- `layer-audit`

```bash
node .linear/scripts/board.mjs progress --epic 5
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Finanzas nativas + caps LANDED.
