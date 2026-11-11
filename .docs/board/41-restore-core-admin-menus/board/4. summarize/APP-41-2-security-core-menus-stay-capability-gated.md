---
tests_ok: true
id: APP-41-2
title: "Security: core menus stay capability-gated"
role: security
epic: 41
estimate: 1
blocked_by:
  - APP-41-1
blocks: []
agent: plugin-agent-41
claimed_at: "2026-11-11T03:19:23.996Z"
summary_html: summaries/41-restore-core-admin-menus-beginner.html
---
## Objetivo

Security: core menus stay capability-gated

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Confirm workshop_accountant still lacks activate_plugins and manage_options
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### Accountant role stays product caps only

Path: `web/wp-content/plugins/workshop-ledger/src/Roles/RoleRegistrar.php`

```php
$caps = array( self::CAP_MANAGE, 'read' );
// plus edit/read/delete/publish caps for wl_* types only
add_role( self::ROLE_CONTADOR, __( 'WorkshopLedger Accountant', 'workshop-ledger' ), $caps );
```

Ran:

```bash
rg -n "activate_plugins|manage_options|switch_themes|install_plugins" web/wp-content/plugins/workshop-ledger/src/Roles/RoleRegistrar.php
```

Result:

```text
No matches. workshop_accountant receives manage_workshop_ledger, read, and wl_* CPT caps only. RoleRegistrar unchanged.
```

### Verify caps and menu restore

Path: `web/wp-content/plugins/workshop-ledger/src/Roles/RoleRegistrar.php`

```text
php -l RoleRegistrar.php: no syntax errors
php -l AdminChrome.php: no syntax errors
RoleRegistrar forbidden caps: none
AdminChrome remove_menu_page: none
layer-audit ok: true
```

Ran:

```bash
ddev exec php -l RoleRegistrar.php && ddev exec php -l AdminChrome.php && rg activate_plugins RoleRegistrar.php && rg remove_menu_page AdminChrome.php && node .ai/scripts/layer-audit.mjs --root .worktrees/41-restore-core-admin-menus/APP-41-2
```

Result:

```text
Both PHP files lint clean. activate_plugins, manage_options, switch_themes, and install_plugins are absent. remove_menu_page is absent. layer-audit ok, violations empty.
```

## History

- 2026-11-11T03:32:02.438Z epic summary HTML → summaries/41-restore-core-admin-menus-beginner.html
- 2026-11-11T03:32:02.427Z moved done → summarize
- 2026-11-11T03:20:25.329Z moved testing → done
- 2026-11-11T03:20:10.820Z checklist item 2 marked
- 2026-11-11T03:19:56.012Z moved doing → testing
- 2026-11-11T03:19:54.398Z checklist item 1 marked
- 2026-11-11T03:19:23.999Z claimed by plugin-agent-41 → doing
- 2026-11-11T03:14:10.335Z created → backlog
