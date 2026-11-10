---
id: APP-37-1
title: "Native WP: Home + About pages, static front, drop Sample Page filler"
role: plugin
epic: 37
http_ok: true
tests_ok: true
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T16:43:14.222Z"
---
## Objetivo

Native WP: Home + About pages, static front, drop Sample Page filler

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### public-seed

```php
PublicSiteSeed::maybe_run(); // Home+About, show_on_front=page, trash Sample/Hello World
```

## History

- 2026-11-10T16:44:04.544Z moved testing → done
- 2026-11-10T16:44:04.062Z checklist item 2 marked
- 2026-11-10T16:44:03.973Z moved doing → testing
- 2026-11-10T16:44:03.733Z checklist item 1 marked
- 2026-11-10T16:43:14.224Z claimed by plugin-agent-1 → doing
- 2026-11-10T16:43:14.093Z created → backlog
