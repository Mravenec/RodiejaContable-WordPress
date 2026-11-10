# Epic 15 — geo_ubicaciones

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/15-geo_ubicaciones/` · Snapshot: `.docs/board/15-geo_ubicaciones/`

## Title

Native geo: location/canton meta on vehicle+employee

## Why

Costa Rica location (province/canton/lat/lng) must be native meta + Options canton list — not a geo microservice.

## What we did

- [x] Geo meta + admin fields on vehicle/employee
- [x] Options list of CR cantons

## Acceptance

- Given vehicle/employee
- When saving canton/coords
- Expect persisted meta and Options-backed canton list

## Agent notes (constraints / edge cases)

Cantons editable via Options without deploy.

## Board issues (= tickets)

- APP-15-1 geo meta admin
- APP-15-2 cantons Options

## How to verify

- `geo metabox`
- `cantons settings`

```bash
node .linear/scripts/board.mjs progress --epic 15
```

## Outcome

Native geo LANDED.
