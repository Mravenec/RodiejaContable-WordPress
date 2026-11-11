---
id: APP-42-1
title: "Plugin: reseed and restyle the public home and about pages"
role: plugin
epic: 42
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-42
claimed_at: "2026-11-11T03:15:51.481Z"
---
## Objetivo

Plugin: reseed and restyle the public home and about pages

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Native-first: Home and About stay WordPress pages. Design is seeded HTML plus front.css
- [ ] Bump the public seed to v4 and restyle the landing so it no longer reads as one generic card
- [ ] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Native pages and front.css

```text
upsert_page() uses wp_insert_post / wp_update_post as post_type page. show_on_front + page_on_front. CSS: AdminAssets enqueues assets/css/front.css on body.workshop-ledger-front. No page builder, theme, custom table, web font, or JS framework.
```

Ran:

```bash
rg -n "front.css|PublicSiteSeed::maybe_run|wp_insert_post" web/wp-content/plugins/workshop-ledger/src
```

Result:

```text
Home and About stay WordPress pages. Seeded post content. Existing front.css. Plugin::boot already calls maybe_run.
```

## History

- 2026-11-11T03:16:52.973Z checklist item 1 marked
- 2026-11-11T03:15:51.484Z claimed by plugin-agent-42 → doing
- 2026-11-11T03:14:10.600Z created → backlog
