---
id: APP-27-1
title: Plugin · disable Gutenberg on domain CPTs; classic vehicle edit
role: plugin
epic: 27
estimate: 2
http_ok: true
tests_ok: true
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T15:30:35.198Z"
---
## Objetivo

Plugin · disable Gutenberg on domain CPTs; classic vehicle edit

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### classic-editor

```php
ClassicEditorForce + vehicle supports title/thumbnail only
```

## History

- 2026-11-10T15:31:07.275Z moved testing → done
- 2026-11-10T15:31:06.818Z checklist item 2 marked
- 2026-11-10T15:31:06.739Z moved doing → testing
- 2026-11-10T15:31:06.526Z checklist item 1 marked
- 2026-11-10T15:30:35.199Z claimed by plugin-agent-1 → doing
- 2026-11-10T15:30:35.043Z created → backlog
