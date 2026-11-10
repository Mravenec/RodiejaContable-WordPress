# Epic 10 — reportes_export

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/10-reportes_export/` · Snapshot: `.docs/board/10-reportes_export/`

## Title

Reportes nativos: CSV admin + wp_mail resúmenes

## Why

Export operativos (vehículos/repuestos/tx) y resumen mensual por correo deben ser nativos (admin-post + nonce + caps).

## What we did

- [x] Export CSV vía admin-post + nonce
- [x] Resumen mensual `wp_mail` cap-gated

## Acceptance

- Given admin con caps
- When exporta CSV o dispara resumen
- Expect archivo/correo; sin cap → denegado

## Agent notes (constraints / edge cases)

No cron externo; WP-Cron/`wp_mail` nativos.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Reportes | sí |  |

## Board issues (= tickets)

- APP-10-1 CSV export
- APP-10-2 wp_mail resumen

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `menú Reportes`
- `admin-post con nonce`

```bash
node .linear/scripts/board.mjs progress --epic 10
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Reportes/export LANDED.
