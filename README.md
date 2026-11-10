# Harness — MariaDB · WordPress · PHP Custom Plugins

Neutral multi-layer scaffold. **No product domain baked in** — agents build features here using **native WordPress APIs only** (no Laravel/Illuminate, no Doctrine, no SPA required).

Board of record: **`.linear/`** (**private.board** — files only, no cloud API). Worktrees: **`.ai/scripts/worktree-*.mjs`**.

## Day zero

| Path | Role |
|---|---|
| `0.database/` | Canonical SQL mirror + MariaDB via DDEV |
| `0.security/` | Always-on MU-plugin hardening (symlinked into WP) |
| `1.plugin/harness-plugin/` | OOP plugin: Contracts → Repositories → Services → Controllers + Security |
| `web/` | WordPress core (DDEV docroot; mostly gitignored) |
| `3.diagrams/` | dbdiagram + PlantUML |
| `.ddev/` | WordPress + MariaDB 11.8 + nginx/PHP hardening |
| `.linear/` | private.board + claims + living summaries |
| `.ai/scripts/` | `epic-new`, `worktree-*`, `layer-audit` |
| `.docs/journey/` | Epic narrative (one file = one Epic) |

```bash
# See the empty board
node .linear/scripts/board.mjs epics

# Start real work (journey + board together)
node .ai/scripts/epic-new.mjs 1 my-feature --title "My first feature"

# After claim — isolate code
node .ai/scripts/worktree-add.mjs APP-1-1

# Optional: local board UI
node .linear/scripts/api.mjs
npm run ui --prefix .linear/ui
# http://127.0.0.1:47382
```

## Quick start (WSL + DDEV)

```bash
ddev start
ddev wp core download --force
ddev wp core install \
  --url="https://harness-wp.ddev.site" \
  --title="Harness WP" \
  --admin_user="admin" \
  --admin_password="ChangeMe-Now-Please!" \
  --admin_email="admin@example.com" \
  --skip-email
ddev wp plugin activate harness-plugin
ddev launch
```

Health (public, no secrets):

```bash
curl -s https://harness-wp.ddev.site/wp-json/harness/v1/health
```

Protected items API requires an authenticated user with `manage_options` (Application Password or cookie + `X-WP-Nonce`).

## Native architecture (plugin)

```
1.plugin/harness-plugin/
├── harness-plugin.php
├── uninstall.php
├── composer.json          # PSR-4 only — no app frameworks
└── src/
    ├── Contracts/         # interfaces (DIP)
    ├── Repositories/      # $wpdb + prepare / insert / delete
    ├── Services/          # business rules
    ├── Controllers/       # REST + Admin
    ├── Security/          # caps, nonces, sanitize, escape, headers
    ├── Providers/         # DI wiring
    └── Core/              # Container, Plugin, Activator
```

## Security baseline (baked in)

| Threat | Mitigation |
|---|---|
| XSS | Sanitize early / escape late (`RequestSanitizer`, `OutputEscaper`) |
| SQL injection | `$wpdb->prepare`, `insert`, `delete` only |
| CSRF | Nonces on admin posts; REST cookie auth uses WP nonce |
| Privilege escalation | `permission_callback` + `CapabilityGate` (never `__return_true` on mutating routes) |
| File-based malware | nginx denies PHP under `uploads`; `DISALLOW_FILE_EDIT` |
| XML-RPC abuse | Disabled via plugin filter |
| User enumeration | MU-plugin blocks `?author=` on front |
| Fingerprinting | Generator removed; `expose_php=Off` |
| Direct PHP access | `ABSPATH` / `WP_UNINSTALL_PLUGIN` guards |

Read **[AGENTS.md](./AGENTS.md)** before writing code.
