# Epic 9 — historiales_auditoria

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/9-historiales_auditoria/` · Snapshot: `.docs/board/9-historiales_auditoria/`

## Title

Historiales + auditoría nativa (revisions/CPT log)

## Why

Cambios de dominio necesitan historial auditable nativo (CPT evento + hooks), no un log Spring aparte.

## What we did

- [x] CPT `rodieja_evento_historial` + hooks `save_post`
- [x] Admin list filtrable por CPT origen
- [x] Solo caps `manage_rodieja` en historial

## Acceptance

- Given un save en CPT dominio
- When se revisa Historial
- Expect evento listado y acceso cap-gated

## Agent notes (constraints / edge cases)

Preferir hooks WP; sin tabla audit custom salvo OK humano.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Historial | sí |  |
| Security caps | sí |  |

## Board issues (= tickets)

- APP-9-1 CPT + hooks
- APP-9-2 admin list
- APP-9-3 caps

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `menú Config/Historial`
- `usuario sin cap → forbidden`

```bash
node .linear/scripts/board.mjs progress --epic 9
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Historial nativo LANDED.
