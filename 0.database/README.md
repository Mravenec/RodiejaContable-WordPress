# 0.database — MariaDB (DDEV)

WordPress core tables are created by `ddev wp core install`.

Custom plugin tables:

1. **Runtime:** `dbDelta()` on plugin activation (`ItemRepository::install_schema`).
2. **Canonical mirror:** `canonical_sql/` for review / dbdiagram / agents.

```bash
ddev mysql < 0.database/canonical_sql/01_harness_items.sql
```

Never paste secrets into SQL files. Always use `$wpdb->prefix` in PHP.
