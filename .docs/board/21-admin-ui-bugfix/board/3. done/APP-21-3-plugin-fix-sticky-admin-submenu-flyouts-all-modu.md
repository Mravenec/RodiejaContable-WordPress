---
id: APP-21-3
title: "plugin: Fix sticky admin submenu flyouts (all modules)"
role: plugin
epic: 21
estimate: 1
http_ok: true
tests_ok: true
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T08:00:53.124Z"
---
## Objetivo

plugin: Fix sticky admin submenu flyouts (all modules)

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### submenu-css

```css
remove left:160px; current submenu left:auto inline
```

## History

- 2026-11-10T08:01:21.944Z moved testing → done
- 2026-11-10T08:01:21.603Z checklist item 2 marked
- 2026-11-10T08:01:21.525Z moved doing → testing
- 2026-11-10T08:01:21.266Z checklist item 1 marked
- 2026-11-10T08:00:53.125Z claimed by plugin-agent-1 → doing
- 2026-11-10T08:00:47.944Z created → backlog
