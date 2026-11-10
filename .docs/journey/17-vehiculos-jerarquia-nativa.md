# Epic 17 — vehiculos-jerarquia-nativa

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/17-vehiculos-jerarquia-nativa/` · Snapshot: `.docs/board/17-vehiculos-jerarquia-nativa/`

## Title

Vehículos: vista jerárquica nativa (marca→modelo→gen→vehículo)

## Why

El Read-Only muestra el árbol Marca → Modelo → Generación → Vehículo. En WP solo había listas CPT; hace falta la misma jerarquía en admin nativo (sin SPA).

## What we did

- [x] CatalogHierarchyRepository + Interface (get_posts + meta)
- [x] CatalogHierarchyService (Contracts → Repo → Service)
- [x] JerarquiaAdminPage + menú Vehículos → Ver por Generaciones
- [x] CSS `.rodieja-hierarchy` en admin chrome
- [x] feature push + land-finish → origin/main

## Acceptance

- Given administrador en wp-admin
- When abre Vehículos → Ver por Generaciones
- Expect árbol anidado marca→modelo→generación→vehículo con links Editar
- Built with native WP APIs + plugin DI; layer-audit verde

## Agent notes (constraints / edge cases)

Parity: React VehiculosJerarquicos (estructura). Label UI = “Ver por Generaciones”; clase/slug = jerarquia. No custom tables.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Contracts/Repos/Services | sí | CatalogHierarchy* |
| Admin JerarquiaAdminPage + AdminMenu | sí |  |
| assets/css/admin.css | sí | hierarchy tree |

## Board issues (= tickets)

- APP-17-1 CatalogHierarchyService tree
- APP-17-2 JerarquiaAdminPage + menu + CSS

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `wp-admin → Vehículos → Ver por Generaciones`
- `node .ai/scripts/layer-audit.mjs`
- `git merge-base --is-ancestor origin/feature/17-vehiculos-jerarquia-nativa origin/main`

```bash
node .linear/scripts/board.mjs progress --epic 17
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Vista jerárquica nativa LANDED en origin/main. Board epic 17 completed 100%.
