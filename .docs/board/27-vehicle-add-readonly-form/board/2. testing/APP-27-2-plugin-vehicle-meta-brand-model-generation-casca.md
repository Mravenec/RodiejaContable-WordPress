---
id: APP-27-2
title: "Plugin · vehicle meta: Brand→Model→Generation cascade + enums"
role: plugin
epic: 27
estimate: 4
http_ok: true
tests_ok: true
blocked_by:
  - APP-27-1
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T15:31:10.341Z"
---
## Objetivo

Plugin · vehicle meta: Brand→Model→Generation cascade + enums

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### vehicle-cascade

```php
CatalogMetaBoxes Brand→Model→Generation + enums + vehicle-cascade.js
```

## History

- 2026-11-10T15:32:18.173Z checklist item 2 marked
- 2026-11-10T15:32:18.069Z moved doing → testing
- 2026-11-10T15:32:17.859Z checklist item 1 marked
- 2026-11-10T15:31:10.341Z claimed by plugin-agent-1 → doing
- 2026-11-10T15:30:35.117Z created → backlog
