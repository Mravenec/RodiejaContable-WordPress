# WorkshopLedger-WordPress

**Workshop Ledger** — accounting for vehicle workshops on a **native WordPress** harness (DDEV + MariaDB).

Not Spring/React. Domain lives as **CPT + post meta + taxonomies + roles + REST** inside `web/wp-content/plugins/workshop-ledger/`.

Rename map: [docs/RENAME-MAP.md](./docs/RENAME-MAP.md) (epic 26). Commands: [project-commands.txt](./project-commands.txt).

## Native-first map

| Domain | WordPress native |
|---|---|
| users / roles | WP Users + role `workshop_accountant` + caps |
| brands / models / generations | CPT `wl_marca` · `wl_modelo` · `wl_generacion` |
| vehicles | CPT `wl_vehiculo` + post meta |
| parts inventory | CPT `wl_repuesto` + meta |
| finance transactions | CPT `wl_transaccion` + taxonomy |
| WP Admin UI | Menus / list tables / metaboxes (no React SPA) |
| Health REST | `workshop-ledger/v1/health` |

Custom DB tables: **not used** for this MVP (native-first). Audatex/INS external sync: deferred (needs human OK).

## Local

```bash
ddev start
ddev wp plugin activate workshop-ledger
curl -sk https://workshop-ledger.ddev.site/wp-json/workshop-ledger/v1/health
ddev launch
```

### Admin login (local dev)

| Field | Value |
|---|---|
| User / email | `admin@workshop.com` |
| Password | `WorkshopLedger1!` |

```bash
ddev wp user update 1 --user_email=admin@workshop.com --user_pass='WorkshopLedger1!' --display_name='Workshop Admin' || \
ddev wp user create 'admin@workshop.com' 'admin@workshop.com' --role=administrator --user_pass='WorkshopLedger1!' --display_name='Workshop Admin'
```

Board: `.linear/` · Agents: `AGENTS.md` · Harness twin: `HarnessMariaDB-WordPressPHP-CustomPlugins`.
