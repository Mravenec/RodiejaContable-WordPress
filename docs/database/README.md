# Database docs (mirror only)

**Runtime source of truth:** plugin `dbDelta()` on activation  
(`web/wp-content/plugins/harness-plugin/…/Repositories/*Repository.php`).

`canonical_sql/` is a human/agent review mirror for DDEV MariaDB — not a separate “database app” layer.

```bash
ddev wp db query "SHOW TABLES LIKE '%harness%'"
ddev mysql < docs/database/canonical_sql/01_harness_items.sql
```
