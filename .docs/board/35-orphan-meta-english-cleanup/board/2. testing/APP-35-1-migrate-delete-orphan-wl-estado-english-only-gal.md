---
id: APP-35-1
title: Migrate/delete orphan wl_estado; English-only gallery/geo leftovers
role: plugin
epic: 35
http_ok: true
tests_ok: true
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T16:23:04.771Z"
---
## Objetivo

Migrate/delete orphan wl_estado; English-only gallery/geo leftovers

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### orphan-migrate

```php
OrphanEstadoMigrate::maybe_run(); // wl_estado → wl_estado_vehiculo then delete
```

## History

- 2026-11-10T16:23:59.632Z checklist item 2 marked
- 2026-11-10T16:23:59.554Z moved doing → testing
- 2026-11-10T16:23:59.356Z checklist item 1 marked
- 2026-11-10T16:23:04.772Z claimed by plugin-agent-1 → doing
- 2026-11-10T16:15:45.586Z created → backlog
