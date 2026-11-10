# Diagrams (`3.diagrams/`)

All visual twins live under **`3.diagrams/`**, mirroring the stack folders. Do **not** keep a second copy next to SQL or app code.

| Path | Tool | Update when |
|---|---|---|
| `3.diagrams/0.database/dbdiagram/schema.dbml` | dbdiagram.io (DBML) | `0.database/canonical_sql/` changes — **same checklist item** |
| `3.diagrams/1.backend/*.puml` | PlantUML | Real `1.backend/<module_name>/` layers change — **same checklist item** |
| `3.diagrams/2.frontend/*.mmd` | Mermaid | Real `2.frontend/<module_name>/` tree changes — **same checklist item** |

## Do

- Mirror what exists in SQL / Java / React (tables, packages, `services → hooks → pages`). JWT/security classes belong in backend PlantUML when they exist (`.ai/rules/jwt-auth.md`). Frontend Mermaid must show the real route tree (small pages / drill-down), not a fake single dashboard (`.ai/rules/progressive-disclosure.md`).
- Keep one paste-ready `schema.dbml` for [dbdiagram.io](https://dbdiagram.io).
- Keep PlantUML and Mermaid source in git (`.puml` / `.mmd`). Optional PNG/SVG exports are local-only.

## Do not

- Duplicate DBML under `0.database/dbdiagram/` (that folder was **moved**).
- Invent tables, controllers, or pages that are not in the real tree.
- Put PlantUML under `1.backend/` or Mermaid under `2.frontend/`.
- Put diagram layout, dbdiagram.io, PlantUML, or Mermaid rules in `.linear/` or in the board engine. Those folders are the **board only**. Host docs: `AGENTS.md` · `.ai/`.

Skills: `.ai/skills/diagrams/SKILL.md` · `.ai/skills/dbdiagram/SKILL.md`  
Folder: `3.diagrams/README.md`
