# WordPress native layers (layer audit)

Enforced by `node .ai/scripts/layer-audit.mjs` and by `worktree-finish` / `worktree-integrate` / `worktree-land-smoke`.

## Required order

```
Contracts → Repositories ($wpdb) → Services → Controllers (REST/Admin)
+ Security (caps, nonces, sanitize, escape) on every entrypoint
```

## Allowlist

`services_without_repository` in `.ai/project.yaml` — services that intentionally touch no table (e.g. `HealthService`). Every entry needs a one-line reason in this file or the ticket.

- `HealthService` — read-only status payload; no persistence.

## Forbidden

- Laravel / Illuminate / Doctrine / Eloquent / Symfony Framework Bundle as app deps
- Concrete `*Repository` injection when an interface exists (prefer DIP)
- Mutating REST routes with `permission_callback` => `__return_true`
- Missing `ABSPATH` / `WP_UNINSTALL_PLUGIN` guards on bootstrap files
