# Harness adapters

| File | Role |
|---|---|
| `neutral.mjs` | Base column gates |
| `mariadb-wordpress-php-plugins.mjs` | Unified WordPress host flags (`tests_ok` / `http_ok`) |

```yaml
harness: mariadb-wordpress-php-plugins
```

Roles: `database` · `plugin` · `security` · `orchestrator`

Host layout: one `web/` WordPress docroot — architecture inside `wp-content/plugins/<module>/`.
