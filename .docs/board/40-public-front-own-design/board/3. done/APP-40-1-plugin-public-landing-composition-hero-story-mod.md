---
http_ok: true
id: APP-40-1
title: "Plugin: public landing composition (hero, story, modules) in seed + front.css"
role: plugin
epic: 40
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T19:03:02.625Z"
---
## Objetivo

Plugin: public landing composition (hero, story, modules) in seed + front.css

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### public landing

```php
OPTION_FLAG wl_public_site_seeded_v3; home is article.wl-landing with hero, story, numbered modules; no body form.
```

### front smoke

```text
GET / contains wl-landing, wl-hero, wl-story, and "There is no form".
```

## History

- 2026-11-10T19:04:03.501Z moved testing → done
- 2026-11-10T19:04:02.816Z checklist item 2 marked
- 2026-11-10T19:04:02.626Z moved doing → testing
- 2026-11-10T19:04:02.528Z checklist item 1 marked
- 2026-11-10T19:03:02.626Z claimed by plugin-agent-1 → doing
- 2026-11-10T19:03:02.508Z created → backlog
