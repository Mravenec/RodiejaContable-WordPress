# dbdiagram.io twin

When `0.database/canonical_sql/` changes, update **`3.diagrams/0.database/dbdiagram/schema.dbml`** in the **same** checklist item.

| Do | Do not |
|---|---|
| Mirror tables, PKs, FKs, important uniques | Invent tables missing from SQL |
| Keep one paste-ready `schema.dbml` | Split into many DBML files |
| Note enums / check constraints briefly if useful | Replace SQL as source of truth |
| Keep DBML only under `3.diagrams/0.database/` | Recreate `0.database/dbdiagram/` |

Engineers: copy all of `schema.dbml` → paste into https://dbdiagram.io.

Skill: `.ai/skills/dbdiagram/SKILL.md` · Folder: `3.diagrams/0.database/dbdiagram/` · Parent: `.ai/rules/diagrams.md`
