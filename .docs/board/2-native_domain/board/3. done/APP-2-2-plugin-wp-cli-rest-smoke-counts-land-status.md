---
id: APP-2-2
title: Plugin · WP-CLI + REST smoke counts + land status
role: plugin
epic: 2
estimate: 1
blocked_by:
  - APP-2-1
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T05:23:33.719Z"
---
## Objetivo

Plugin · WP-CLI + REST smoke counts + land status

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### REST health+stats smoke

Path: `smoke.json`

```json
{"health":200,"stats":200,"native":true}
```

## History

- 2026-11-10T05:23:36.558Z moved testing → done
- 2026-11-10T05:23:36.482Z checklist item 2 marked
- 2026-11-10T05:23:36.410Z moved doing → testing
- 2026-11-10T05:23:36.261Z checklist item 1 marked
- 2026-11-10T05:23:33.725Z claimed by plugin-agent-1 → doing
- 2026-11-10T05:20:02.535Z created → backlog
