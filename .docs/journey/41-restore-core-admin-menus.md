# Epic 41 — restore-core-admin-menus

**Status:** In progress · Board: `.linear/epics/41-restore-core-admin-menus/`

## Title

Restore default WordPress admin menus under the product menu

## Why

Workshop Ledger hides Dashboard, Posts, Media is kept, Pages, Comments, Appearance, Plugins, Tools, and Settings so the product menu stays quiet. Operators who also administer WordPress cannot reach Plugins or a core update from the sidebar. The product menu should stay first. The default WordPress menus should sit under it, including Plugins.

## What we will do

- [x] Native-first: `admin_menu` / `menu_order` only. No new capabilities, no custom tables.
- [ ] Stop removing core menu pages in `AdminChrome`.
- [ ] Order product slugs first, then the core screens.
- [ ] Leave `workshop_accountant` without `activate_plugins` and `manage_options`. Core still hides those screens from that role.

## Acceptance

- Given an administrator, when they open wp-admin, then Workshop Ledger items are above Dashboard, Posts, Media, Pages, Comments, Appearance, Plugins, Users, Tools, and Settings.
- Given a workshop accountant, when they open wp-admin, then Plugins, Appearance, and Settings stay hidden because that role lacks those capabilities.
- English only. Admin product screens are otherwise unchanged.

## Agent notes

- File: `web/wp-content/plugins/workshop-ledger/src/Admin/AdminChrome.php`.
- Product slugs live on `AdminMenu`: `SLUG_INICIO`, `SLUG_VEHICULOS`, `SLUG_INVENTARIO`, `SLUG_FINANZAS`, `SLUG_INPART`, `SLUG_REPORTES`, `SLUG_CONFIG`.
- Do not grant plugin or theme capabilities in `RoleRegistrar`.
- Custom post types already use `show_in_menu => false`. Do not turn those menus back on.

## Areas touched

| Area | Touched? | Notes |
|---|---|---|
| Plugin schema / dbDelta | No | |
| `web/wp-content/plugins/workshop-ledger/` | Yes | `AdminChrome` only |
| `web/wp-content/mu-plugins/` | No | |
| `docs/diagrams/plugin/` | No | |

## How to verify

```bash
node .linear/scripts/board.mjs progress --epic 41
```

Administrator sidebar includes Plugins under the product block. Accountant still cannot `activate_plugins`.

## Outcome

(Filled when the epic lands on origin/main.)
