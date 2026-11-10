# Epic 3 — catalogo_vehicular

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/3-catalogo_vehicular/` · Snapshot: `.docs/board/3-catalogo_vehicular/`

## Title

Catálogo vehicular: marca→modelo→gen→vehículo admin

## Why

El catálogo Read-Only (marca→modelo→generación→vehículo) debe editarse en wp-admin con meta boxes, columnas y filtros nativos.

## What we did

- [x] Meta boxes + columnas de lista para Marca/Modelo/Generación/Vehículo
- [x] Filtros admin WP_Query por estado_vehiculo
- [x] Journey map Spring Vehiculos → CPT nativo

## Acceptance

- Given CPT de catálogo
- When el admin filtra/edita en listas
- Expect meta y filtros nativos sin UI React

## Agent notes (constraints / edge cases)

Jerarquía visual tipo árbol llega en epic 17; aquí es CRUD/listas.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| Plugin Admin + PostTypes/meta | sí |  |
| Orchestrator docs | sí |  |

## Board issues (= tickets)

- APP-3-1 meta boxes + columns
- APP-3-2 filtros estado_vehiculo
- APP-3-3 map Spring→CPT

Checklists and evidence live on the board issue files / living HTML — not duplicated here.

## How to verify

- `wp-admin Vehículos / Marcas / Modelos / Generaciones`
- `filtro estado`

```bash
node .linear/scripts/board.mjs progress --epic 3
# expect 100% (all issues in summarize, checklists complete)
```

## Outcome

Catálogo editable nativo LANDED. Árbol “Ver por Generaciones” = epic 17.
