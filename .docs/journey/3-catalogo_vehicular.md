# Epic 3 — catalogo_vehicular

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/3-catalogo_vehicular/` · Snapshot: `.docs/board/3-catalogo_vehicular/`

## Title

Vehicle catalog: brand→model→gen→vehicle admin

## Why

Read-Only catalog (brand→model→generation→vehicle) must be editable in wp-admin with native meta boxes, columns, and filters.

## What we did

- [x] Meta boxes + list columns for Brand/Model/Generation/Vehicle
- [x] Admin WP_Query filters by vehicle status
- [x] Journey map Spring Vehicles → CPT

## Acceptance

- Given catalog CPTs
- When admin filters/edits lists
- Expect native meta and filters without a React UI

## Agent notes (constraints / edge cases)

Visual tree arrives in epic 17; this epic is CRUD/lists.

## Board issues (= tickets)

- APP-3-1 meta boxes + columns
- APP-3-2 status filters
- APP-3-3 Spring→CPT map

## How to verify

- `wp-admin Vehicles / Brands / Models / Generations`
- `status filter`

```bash
node .linear/scripts/board.mjs progress --epic 3
```

## Outcome

Editable native catalog LANDED. Tree = epic 17.
