# Skill — dbdiagram.io schema twin

Use when plugin schema / `docs/database/canonical_sql/` changes.

## Goal

Keep **`docs/diagrams/database/schema.dbml`** paste-ready for [dbdiagram.io](https://dbdiagram.io).

Runtime truth remains plugin `dbDelta()`. The SQL + DBML under `docs/` are review twins.

## Same checklist item

1. Update plugin repository schema / optional `docs/database/canonical_sql/NN_*.sql`.
2. Refresh `docs/diagrams/database/schema.dbml`.
3. `ddev wp db query` / DESCRIBE to verify when in testing.

Rule: `.ai/rules/dbdiagram.md`
