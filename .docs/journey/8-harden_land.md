# Epic 8 — harden_land

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/8-harden_land/` · Snapshot: `.docs/board/8-harden_land/`

## Title

Harden + smoke DDEV + land all local

## Why

Cierre de seguridad y humo: MU audit, DISALLOW_FILE_EDIT, layer-audit, health/REST 401, y land.json coherente en el tablero.

## What we did

- [x] MU audit + DISALLOW_FILE_EDIT + layer-audit verde
- [x] Smoke DDEV health/stats/REST 401 anon
- [x] Summarize/handoff/land status de epics

## Acceptance

- Given entorno DDEV
- When corre smoke
- Expect health OK y REST mutaciones anónimas 401/403

## Agent notes (constraints / edge cases)

Corre después del bloque de dominio; LANDED no se finge a mano.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| MU-plugin | sí |  |
| Plugin smoke | sí |  |
| Board land | sí |  |

## Board issues (= tickets)

- APP-8-1 MU + layer-audit
- APP-8-2 smoke DDEV
- APP-8-3 summarize/land

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `node .ai/scripts/layer-audit.mjs`
- `curl health`
- `REST anon`

```bash
node .linear/scripts/board.mjs progress --epic 8
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Harden + smoke + land hygiene LANDED.
