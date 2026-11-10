---
id: APP-25-1
title: Plugin · hide WP chrome menus + rename Audatex InPart
role: plugin
epic: 25
estimate: 2
http_ok: true
tests_ok: true
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T14:10:25.027Z"
---
## Objetivo

Plugin · hide WP chrome menus + rename Audatex InPart

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### menu-chrome

```php
remove_menu_page Appearance/Plugins/Tools/WP Settings/Dashboard/Pages; Audatex InPart label; Media+Users kept
```

## History

- 2026-11-10T14:11:00.955Z moved testing → done
- 2026-11-10T14:11:00.586Z checklist item 2 marked
- 2026-11-10T14:11:00.515Z moved doing → testing
- 2026-11-10T14:11:00.323Z checklist item 1 marked
- 2026-11-10T14:10:25.028Z claimed by plugin-agent-1 → doing
- 2026-11-10T14:10:20.430Z created → backlog
