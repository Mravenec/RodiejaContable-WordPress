# Epic 15 — geo_ubicaciones

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/15-geo_ubicaciones/` · Snapshot: `.docs/board/15-geo_ubicaciones/`

## Title

Geo nativo: meta ubicación/cantón en vehículo+empleado

## Why

Ubicación CR (provincia/cantón/lat/lng) debe ser meta nativa + Options de cantones, no microservicio geo.

## What we did

- [x] Meta geo + campos admin en vehículo/empleado
- [x] Options lista cantones CR

## Acceptance

- Given vehículo/empleado
- When se guarda cantón/coords
- Expect meta persistida y lista cantones desde Options

## Agent notes (constraints / edge cases)

Lista cantones en Options API; editable sin deploy.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin meta geo + CantonesSettings | sí |  |

## Board issues (= tickets)

- APP-15-1 meta geo admin
- APP-15-2 Options cantones

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `metabox geo`
- `settings cantones`

```bash
node .linear/scripts/board.mjs progress --epic 15
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Geo nativo LANDED.
