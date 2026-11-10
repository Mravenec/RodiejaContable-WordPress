---
id: APP-3-3
title: Orchestrator · journey map Spring Vehiculos→CPT
role: orchestrator
epic: 3
estimate: 1
blocked_by:
  - APP-3-2
blocks: []
agent: orchestrator-1
claimed_at: "2026-11-10T05:40:59.516Z"
summary_html: summaries/3-catalogo-vehicular-beginner.html
---
## Objetivo

Orchestrator · journey map Spring Vehiculos→CPT

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Spring→CPT map

Path: `.docs/journey/3-catalogo_vehicular.md`

```markdown
# Epic 3 — Catálogo vehicular (nativo WP)

## Spring → WordPress map

| Spring | Native WP |
|---|---|
| MarcasController | CPT `rodieja_marca` |
| ModelosController | CPT `rodieja_modelo` + meta `rodieja_marca_id` |
| GeneracionesController | CPT `rodieja_generacion` + meta años/modelo |
| VehiculosController | CPT `rodieja_vehiculo` + meta + tax `rodieja_estado_vehiculo` |

## Admin UX delivered

- Meta boxes (nonce + caps) for modelo/generación/vehículo
- List columns with edit links across the chain
- `restrict_manage_posts` + `tax_query` filter by estado

## Acceptance

- No custom tables; DI in plugin; layer-audit green
```

## History

- 2026-11-10T07:21:57.891Z epic summary HTML → summaries/3-catalogo-vehicular-beginner.html
- 2026-11-10T07:21:57.883Z moved done → summarize
- 2026-11-10T05:41:11.561Z moved testing → done
- 2026-11-10T05:41:11.487Z checklist item 2 marked
- 2026-11-10T05:41:11.415Z moved doing → testing
- 2026-11-10T05:41:11.264Z checklist item 1 marked
- 2026-11-10T05:40:59.516Z claimed by orchestrator-1 → doing
- 2026-11-10T05:35:44.004Z created → backlog
