# Skill — canonical SQL mirror (WordPress)

Runtime schema = plugin `dbDelta`. Optional review files live under `docs/database/canonical_sql/`.

Day zero: one new numbered SQL file + one `SOURCE` line in `00_run_all.sql`. Refresh DBML twin. Verify with `ddev wp db query`.

Do not create a Spring-style `0.database/` application layer.
