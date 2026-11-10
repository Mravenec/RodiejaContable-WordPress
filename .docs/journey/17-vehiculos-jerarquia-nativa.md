# Epic 17 — vehiculos-jerarquia-nativa

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/17-vehiculos-jerarquia-nativa/` · Snapshot: `.docs/board/17-vehiculos-jerarquia-nativa/`

## Title

Vehicles: native hierarchy view (brand→model→gen→vehicle)

## Why

Read-Only shows Brand → Model → Generation → Vehicle. WP had only CPT lists; need the same hierarchy in native admin (no SPA).

## What we did

- [x] CatalogHierarchyRepository + interface (get_posts + meta)
- [x] CatalogHierarchyService
- [x] JerarquiaAdminPage + Vehicles → View by Generations
- [x] Hierarchy CSS
- [x] feature push + land-finish → origin/main

## Acceptance

- Given administrator in wp-admin
- When opening Vehicles → View by Generations
- Expect nested brand→model→generation→vehicle with Edit links
- Native WP APIs + plugin DI; layer-audit green

## Agent notes (constraints / edge cases)

UI label View by Generations; class/slug jerarquia. No custom tables. Do not edit Read-Only.

## Board issues (= tickets)

- APP-17-1 CatalogHierarchyService tree
- APP-17-2 JerarquiaAdminPage + menu + CSS

## How to verify

- `wp-admin → Vehicles → View by Generations`
- `layer-audit`
- `feature ancestor of origin/main`

```bash
node .linear/scripts/board.mjs progress --epic 17
```

## Outcome

Native hierarchy LANDED on origin/main.
