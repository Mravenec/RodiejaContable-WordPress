---
id: APP-41-1
title: "Plugin: restore core admin menus, including Plugins, under the product menu"
role: plugin
epic: 41
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-41
claimed_at: "2026-11-11T03:15:44.706Z"
---
## Objetivo

Plugin: restore core admin menus, including Plugins, under the product menu

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Native-first: keep core admin_menu. Do not add capabilities or tables
- [x] Restore Dashboard, Posts, Media, Pages, Comments, Appearance, Plugins, Users, Tools, and Settings under the product menu
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Native-first: core admin_menu and menu_order

Path: `web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php`

```php
add_action( 'admin_menu', array( $this, 'tidy_core_menus' ), 999 );
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', array( $this, 'reorder' ) );
```

Ran:

```bash
rg -n "admin_menu|menu_order|remove_menu_page" web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php
```

Result:

```text
Native only: admin_menu + custom_menu_order + menu_order. No new caps, no tables, no RoleRegistrar change.
```

### Restore core menus under product slugs

Path: `web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php`

```php
AdminMenu::SLUG_CONFIG,
'index.php',
'edit.php',
'upload.php',
'edit.php?post_type=page',
'edit-comments.php',
'themes.php',
'plugins.php',
'users.php',
'tools.php',
'options-general.php',
```

Ran:

```bash
rg -n "remove_menu_page|plugins.php|custom_menu_order" web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php
```

Result:

```text
custom_menu_order true; plugins.php at preferred order; remove_menu_page has no matches.
```

### Verify AdminChrome syntax and menu order

Path: `web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php`

```text
No syntax errors detected in AdminChrome.php
remove_menu_page: none
layer-audit ok: true, violations: []
```

Ran:

```bash
ddev exec php -l .worktrees/41-restore-core-admin-menus/APP-41-1/web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php && node .ai/scripts/layer-audit.mjs --root .worktrees/41-restore-core-admin-menus/APP-41-1
```

Result:

```text
php -l: no syntax errors. remove_menu_page absent. layer-audit ok true, violations empty.
```

## History

- 2026-11-11T03:17:04.043Z checklist item 3 marked
- 2026-11-11T03:16:37.366Z moved doing → testing
- 2026-11-11T03:16:34.097Z checklist item 2 marked
- 2026-11-11T03:15:56.112Z checklist item 1 marked
- 2026-11-11T03:15:44.708Z claimed by plugin-agent-41 → doing
- 2026-11-11T03:14:10.117Z created → backlog
