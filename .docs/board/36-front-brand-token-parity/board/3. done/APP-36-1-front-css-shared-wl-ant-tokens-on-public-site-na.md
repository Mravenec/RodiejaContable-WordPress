---
id: APP-36-1
title: "Front CSS: shared --wl-* Ant tokens on public site (native)"
role: plugin
epic: 36
http_ok: true
tests_ok: true
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T16:35:46.906Z"
---
## Objetivo

Front CSS: shared --wl-* Ant tokens on public site (native)

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### front-tokens

```css
body.workshop-ledger-front { background: var(--wl-surface); } header { navy→accent gradient }
```

## History

- 2026-11-10T16:36:33.046Z moved testing → done
- 2026-11-10T16:36:32.558Z checklist item 2 marked
- 2026-11-10T16:36:32.469Z moved doing → testing
- 2026-11-10T16:36:32.189Z checklist item 1 marked
- 2026-11-10T16:35:46.908Z claimed by plugin-agent-1 → doing
- 2026-11-10T16:35:46.784Z created → backlog
