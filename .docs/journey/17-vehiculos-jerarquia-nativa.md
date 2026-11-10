# Epic 17 — vehiculos-jerarquia-nativa

**Status:** In progress · Board: `.linear/epics/17-vehiculos-jerarquia-nativa/`

## Title

Vehículos: vista jerárquica nativa (marca→modelo→gen→vehículo)

## Why

El Read-Only muestra un árbol Marca → Modelo → Generación → Vehículo. En WP solo había listas CPT; hace falta la misma jerarquía en admin nativo (sin SPA).

## What we will do

- [x] Native-first: `get_posts` + post meta (no custom tables)
- [ ] CatalogHierarchyService/Repository (Contracts)
- [ ] JerarquiaAdminPage + menú “Ver por Generaciones”
- [ ] CSS acorde al chrome Rodieja
- [ ] worktree → push → land

## Acceptance

- Given an administrator on wp-admin
- When they open Vehículos → Ver por Generaciones
- Expect a nested tree marca→modelo→generación→vehículo with edit links
- Built with native WP APIs + plugin DI layers

## Agent notes

Parity target: React `VehiculosJerarquicos` structure (not full finance tabs in v1).
