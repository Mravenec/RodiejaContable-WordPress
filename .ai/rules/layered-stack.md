---
description: Unified WordPress tree — architecture inside the plugin
alwaysApply: true
---

# Layered stack (unified WordPress)

**Project shape:** `web/` docroot + `wp-content/plugins/<module>/` + `mu-plugins/` + optional `docs/`.

**Not:** Spring-style `0.database` / `1.backend` / `2.frontend` sibling apps.

**Inside the plugin:**

```
dbDelta
  → Contracts
  → Repositories ($wpdb prepared)
  → Services
  → Controllers (REST / Admin)
  → Security
  → GATE_HTTP
```

See `.ai/skills/stack-layers/SKILL.md`.
