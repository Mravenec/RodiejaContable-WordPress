# Skill — stack layers (unified WordPress)

This harness is **one WordPress site**, not a multi-app monorepo.

```
web/                                      # docroot
  wp-content/plugins/<module_name>/       # DI · SOLID · layers
  wp-content/mu-plugins/                  # hardening
docs/database · docs/diagrams             # optional twins
```

Inside the plugin only:

```
Contracts → Repositories ($wpdb) → Services → Controllers
+ Security on every entrypoint → GATE_HTTP
```

Do **not** recreate `0.database/`, `1.plugin/`, `1.backend/`, `2.frontend/`.

Canonical: `.ai/rules/layered-stack.md` · `.ai/specs/wordpress-native-layers.md` · `AGENTS.md`
