---
id: APP-39-1
title: "Plugin: Login redirect_to + login_redirect → workshop-ledger hub"
role: plugin
epic: 39
estimate: 1
blocked_by: []
blocks: []
agent: plugin-agent-1
claimed_at: "2026-11-10T17:07:00.194Z"
---
## Objetivo

Plugin: Login redirect_to + login_redirect → workshop-ledger hub

## Checklist

Mark in order, one `checklist <id> n` command per item, immediately after that step is finished.

- [x] Implement
- [x] Verify locally (in testing — after `move testing`)

## Evidence

Layer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file. After each checklist item: `evidence <id> --title …`.

### login redirect native

```php
// Header/Footer Login → app hub
wp_login_url( admin_url( 'admin.php?page=workshop-ledger' ) );

// LoginChrome::login_redirect — operators → Open app
add_filter( 'login_redirect', [ $this, 'login_redirect' ], 10, 3 );
```

### verify login target

```text
HeaderAuthLinks Login → admin.php?page=workshop-ledger via wp_login_url
LoginChrome registers login_redirect for manage_workshop_ledger / manage_options
```

## History

- 2026-11-10T17:07:35.523Z checklist item 2 marked
- 2026-11-10T17:07:29.170Z moved doing → testing
- 2026-11-10T17:07:29.079Z checklist item 1 marked
- 2026-11-10T17:07:00.194Z claimed by plugin-agent-1 → doing
- 2026-11-10T17:06:57.855Z created → backlog
