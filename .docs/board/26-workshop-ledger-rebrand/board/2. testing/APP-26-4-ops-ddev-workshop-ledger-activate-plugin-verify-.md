---
id: APP-26-4
title: Ops · DDEV workshop-ledger + activate plugin + verify login/health
role: plugin
epic: 26
estimate: 2
http_ok: true
tests_ok: true
blocked_by:
  - APP-26-3
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T15:19:59.272Z"
---
## Objetivo

Ops · DDEV workshop-ledger + activate plugin + verify login/health

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### ddev-config

```yaml
ddev name workshop-ledger; composer_root workshop-ledger; login docs admin@workshop.com
```

## History

- 2026-11-10T15:20:11.318Z checklist item 2 marked
- 2026-11-10T15:20:11.239Z moved doing → testing
- 2026-11-10T15:20:11.006Z checklist item 1 marked
- 2026-11-10T15:19:59.273Z claimed by plugin-agent-1 → doing
- 2026-11-10T15:16:22.192Z created → backlog
