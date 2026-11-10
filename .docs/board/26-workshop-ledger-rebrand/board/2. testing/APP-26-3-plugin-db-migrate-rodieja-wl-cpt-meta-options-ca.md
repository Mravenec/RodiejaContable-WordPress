---
id: APP-26-3
title: Plugin · DB migrate rodieja_* → wl_* CPT/meta/options/caps
role: plugin
epic: 26
estimate: 4
http_ok: true
tests_ok: true
blocked_by:
  - APP-26-2
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T15:18:43.875Z"
---
## Objetivo

Plugin · DB migrate rodieja_* → wl_* CPT/meta/options/caps

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### brand-migrate

```php
BrandMigrate CPT/meta/options/caps + active_plugins; gitignore workshop-ledger
```

## History

- 2026-11-10T15:19:51.558Z checklist item 2 marked
- 2026-11-10T15:19:51.482Z moved doing → testing
- 2026-11-10T15:19:51.276Z checklist item 1 marked
- 2026-11-10T15:18:43.876Z claimed by plugin-agent-1 → doing
- 2026-11-10T15:16:22.114Z created → backlog
