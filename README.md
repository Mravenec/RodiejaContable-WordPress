# RodiejaContable-WordPress

Product recreation of **Rodieja Contable** on a **native WordPress** harness (DDEV + MariaDB).

Not Spring/React. Domain lives as **CPT + post meta + taxonomies + roles + REST** inside `web/wp-content/plugins/rodieja-contable/`.

## Native-first map

| Legacy (Spring/SQL) | WordPress native |
|---|---|
| users / roles ADMIN·CONTADOR | WP Users + role `rodieja_contador` + caps |
| marcas / modelos / generaciones | CPT `rodieja_marca` · `rodieja_modelo` · `rodieja_generacion` |
| vehiculos | CPT `rodieja_vehiculo` + post meta |
| inventario_repuestos | CPT `rodieja_repuesto` + meta |
| transacciones_financieras | CPT `rodieja_transaccion` + taxonomy `rodieja_tipo_tx` |
| React SPA | WP Admin UI (meta boxes + menus) |
| Spring REST | Core REST (`show_in_rest`) + `rodieja/v1/health` |

Custom DB tables: **not used** for this landed MVP (native-first). Audatex/INS external sync: deferred (needs human OK + `wp_remote_*`).

## Local

```bash
ddev start
ddev wp plugin activate rodieja-contable
curl -sk https://rodieja-wp.ddev.site/wp-json/rodieja/v1/health
ddev launch
```

Board: `.linear/` · Agents: `AGENTS.md` · Harness twin: `HarnessMariaDB-WordPressPHP-CustomPlugins`.
