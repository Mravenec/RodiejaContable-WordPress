# WordPress native layers (unified project)

Enforced by `node .ai/scripts/layer-audit.mjs` against  
`web/wp-content/plugins/<module_name>/` (see `plugin_path` in `.ai/project.yaml`).

## Project layout

One WordPress docroot. Custom architecture lives **only** in the plugin (and MU-plugins for hardening). `docs/` holds optional SQL/diagram mirrors.

## Required order (inside plugin)

```
Contracts → Repositories ($wpdb) → Services → Controllers (REST/Admin)
+ Security on every entrypoint
```

## Allowlist

`services_without_repository` in `.ai/project.yaml`:

- `HealthService` — read-only status; no persistence.

## Forbidden

- Laravel / Illuminate / Doctrine / Eloquent / Symfony Framework Bundle
- Concrete `*Repository` injection when an interface exists
- Mutating REST with `permission_callback` => `__return_true`
- Missing `ABSPATH` / `WP_UNINSTALL_PLUGIN` guards
- Recreating segmented `0.`/`1.`/`2.` app folders for “layers”
