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
tests_ok: true
summary_html: summaries/42-public-home-ux-beginner.html
---
## Objetivo

Plugin: reseed and restyle the public home and about pages

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Native-first: Home and About stay WordPress pages. Design is seeded HTML plus front.css
- [x] Bump the public seed to v4 and restyle the landing so it no longer reads as one generic card
- [x] Verify locally (in testing — after `move testing`)

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

### Seed v4 and workshop landing

```text
OPTION_FLAG = wl_public_site_seeded_v4; delete_option v1 v2 v3. Home: article.wl-landing, header.wl-hero, section.wl-day (ol of wl-day__step), section.wl-modules (article.wl-module x4), p.wl-front-note. About: same hero plus section.wl-audience. No form.
```

Ran:

```bash
edit PublicSiteSeed.php and assets/css/front.css in the APP-42-1 worktree
```

Result:

```text
v3 flag cleared on the next run. Landing is hero, day rail, and four paper panels. CSS scoped to body.workshop-ledger-front, 720px row and 2x2 grid.
```

### Seed v4 syntax and layer audit

```text
OPTION_FLAG wl_public_site_seeded_v4; section.wl-day; article.wl-module. front.css: .wl-day inside @media (min-width: 720px).
```

Ran:

```bash
ddev exec php -l /dev/stdin < PublicSiteSeed.php; node .ai/scripts/layer-audit.mjs --root .worktrees/42-public-home-ux/APP-42-1
```

Result:

```text
No syntax errors detected in /dev/stdin. layer-audit ok true, violations []. Markers wl_public_site_seeded_v4, wl-day, wl-module, and the 720px query are present. Live DDEV was not curled.
```

## History

- 2026-11-11T03:34:27.701Z epic summary HTML → summaries/42-public-home-ux-beginner.html
- 2026-11-11T03:34:27.691Z moved done → summarize
- 2026-11-11T03:22:46.362Z moved testing → done
- 2026-11-11T03:20:41.064Z checklist item 3 marked
- 2026-11-11T03:18:37.891Z moved doing → testing
- 2026-11-11T03:18:36.145Z checklist item 2 marked
- 2026-11-11T03:16:52.973Z checklist item 1 marked
- 2026-11-11T03:15:51.484Z claimed by plugin-agent-42 → doing
- 2026-11-11T03:14:10.600Z created → backlog
