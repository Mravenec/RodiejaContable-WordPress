---
id: APP-43-2
title: "Security: escape public SEO head output"
role: security
epic: 43
estimate: 1
blocked_by:
  - APP-43-1
blocks: []
agent: plugin-agent-43
claimed_at: "2026-11-11T03:41:11.514Z"
tests_ok: true
---
## Objetivo

Security: escape public SEO head output

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Escape every SEO attribute and strip the description to plain text
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### SEO head already escaped

Path: `web/wp-content/plugins/workshop-ledger/src/Front/PublicSeo.php`

```php
print_meta: esc_attr on name and property; esc_url for og:url. description() returns trim(wp_strip_all_tags()). JSON-LD body is wp_json_encode only (no post_content). No capability added. File unchanged.
```

Ran:

```bash
rg -n "echo |esc_attr|esc_url|wp_json_encode|wp_strip_all_tags|post_content" web/wp-content/plugins/workshop-ledger/src/Front/PublicSeo.php
```

Result:

```text
Attributes use esc_attr or esc_url. Description is wp_strip_all_tags. JSON uses wp_json_encode. No raw post HTML. No file edit.
```

### php -l and layer-audit

```text
php -l PublicSeo.php Plugin.php ServiceProvider.php: no syntax errors. layer-audit ok true, violations empty. PublicSeo.php unchanged.
```

Ran:

```bash
php -l PublicSeo.php Plugin.php ServiceProvider.php; node .ai/scripts/layer-audit.mjs --root .worktrees/43-public-front-seo/APP-43-2
```

Result:

```text
No syntax errors detected. layer-audit ok true, violations [].
```

## History

- 2026-11-11T03:41:54.551Z moved testing → done
- 2026-11-11T03:41:39.197Z checklist item 2 marked
- 2026-11-11T03:41:26.571Z moved doing → testing
- 2026-11-11T03:41:24.603Z checklist item 1 marked
- 2026-11-11T03:41:11.515Z claimed by plugin-agent-43 → doing
- 2026-11-11T03:14:10.986Z created → backlog
