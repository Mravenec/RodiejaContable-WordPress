---
description: Optional SQL mirrors under docs/database — runtime is plugin dbDelta
alwaysApply: true
---

# Canonical SQL (WordPress unified)

**Runtime source of truth:** plugin activation `dbDelta()` inside  
`web/wp-content/plugins/<module>/`.

**Optional mirror:** `docs/database/canonical_sql/`

| Day-zero pattern | Rule |
|---|---|
| Flat numbered files | One new `NN_<slug>.sql` + one `SOURCE` in `00_run_all.sql` |
| Never | Paste DDL into `00_run_all.sql`; invent a separate `0.database` app |

Also update `docs/diagrams/database/schema.dbml` in the same checklist item.
