---
id: APP-43-1
title: "Plugin: native title, description, canonical, Open Graph, and JSON-LD"
role: plugin
epic: 43
estimate: 1
blocked_by:
  - APP-42-1
blocks: []
agent: plugin-agent-43
claimed_at: "2026-11-11T03:37:44.464Z"
tests_ok: true
---
## Objetivo

Plugin: native title, description, canonical, Open Graph, and JSON-LD

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Native-first: document title, wp_head, and the core sitemap. No SEO plugin and no custom table
- [x] Add PublicSeo for title, description, canonical, robots, Open Graph, and JSON-LD
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Native title, canonical, sitemap

```php
document_title_parts filter; rel_canonical() on wp_head (one link on singular); wp_robots on wp_head; wp_sitemaps_get_server() keeps /wp-sitemap.xml. No second canonical. No Yoast. No custom table.
```

Ran:

```bash
rg -n "function rel_canonical|document_title_parts|function wp_sitemaps_get_server|wp_head', 'rel_canonical|wp_head', 'wp_robots" web/wp-includes
```

Result:

```text
rel_canonical link-template.php:4115; document_title_parts general-template.php:1520; wp_sitemaps_get_server sitemaps.php:22; default-filters wp_head wp_robots:358 and rel_canonical:363
```

### PublicSeo registered on wp_head

Path: `web/wp-content/plugins/workshop-ledger/src/Front/PublicSeo.php`

```php
add_filter( 'document_title_parts', ... ); add_action( 'wp_head', array( $this, 'render_head' ), 1 ); container set PublicSeo; Plugin::boot register() next to HeaderAuthLinks. No rel=canonical echo. No sitemap disable.
```

Ran:

```bash
rg -n "PublicSeo" web/wp-content/plugins/workshop-ledger/src/Front/PublicSeo.php web/wp-content/plugins/workshop-ledger/src/Core/Plugin.php web/wp-content/plugins/workshop-ledger/src/Providers/ServiceProvider.php
```

Result:

```text
PublicSeo.php class; ServiceProvider set(); Plugin.php register() beside HeaderAuthLinks
```

### php -l and layer-audit

```text
php -l PublicSeo.php Plugin.php ServiceProvider.php: no syntax errors. layer-audit ok true, violations empty. Did not curl DDEV (primary checkout).
```

Ran:

```bash
php -l PublicSeo.php Plugin.php ServiceProvider.php; node .ai/scripts/layer-audit.mjs --root .worktrees/43-public-front-seo/APP-43-1
```

Result:

```text
No syntax errors detected in the three files. layer-audit ok true, violations [].
```

## History

- 2026-11-11T03:40:51.826Z moved testing → done
- 2026-11-11T03:40:36.791Z checklist item 3 marked
- 2026-11-11T03:40:10.778Z moved doing → testing
- 2026-11-11T03:40:07.912Z checklist item 2 marked
- 2026-11-11T03:38:30.076Z checklist item 1 marked
- 2026-11-11T03:37:44.465Z claimed by plugin-agent-43 → doing
- 2026-11-11T03:14:10.806Z created → backlog
