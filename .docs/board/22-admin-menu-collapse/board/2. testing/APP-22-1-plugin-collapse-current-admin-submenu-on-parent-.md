---
id: APP-22-1
title: "plugin: Collapse current admin submenu on parent re-click"
role: plugin
epic: 22
estimate: 1
http_ok: true
tests_ok: true
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T08:06:45.862Z"
---
## Objetivo

plugin: Collapse current admin submenu on parent re-click

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### collapse-js

```js
toggle rodieja-submenu-collapsed on current menu-top click
```

## History

- 2026-11-10T08:07:00.568Z checklist item 2 marked
- 2026-11-10T08:07:00.492Z moved doing → testing
- 2026-11-10T08:07:00.293Z checklist item 1 marked
- 2026-11-10T08:06:45.863Z claimed by plugin-agent-1 → doing
- 2026-11-10T08:06:45.741Z created → backlog
