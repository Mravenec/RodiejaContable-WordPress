# Harness adapters (this board)

Board engine is **neutral**. Flag policy for this host is a local `harness/*.mjs` plus `config.yaml`.

| File | Role |
|---|---|
| `neutral.mjs` | Base gates: implement in doing, verify in testing, park in done, summarize only when the whole epic is in done |
| `mariadb-wordpress-php-plugins.mjs` | Host **flag** adapter (`tests_ok` / `http_ok`) — stack narrative stays in host `AGENTS.md` / `.ai/` |

```yaml
harness: mariadb-wordpress-php-plugins
```

Roles: `database` · `plugin` · `security` · `orchestrator`
