# Epic 25 — admin-ui-readonly-shell

**Status:** Landed on main · Board: `.linear/epics/25-admin-ui-readonly-shell/`

## Title

Admin UI: Read-Only menu + shell parity (native)

## Why

Read-Only sider shows only product modules (Dashboard → Vehicles → Inventory → Finance → Audatex InPart → Reports → Settings). WP still shows core menus and bare stub pages (INS/Import raw `<pre>` JSON). Operators feel the gap.

## What we will do

- [x] **Native-first:** `remove_menu_page` + admin PHP/CSS shells only — no SPA, no Elementor, Read-Only untouched
- [ ] Hide non-product WP chrome for Rodieja managers; keep Media + Users (native roles assignment)
- [ ] Rename top menu to **Audatex InPart** (Read-Only label)
- [ ] Product shells on INS/Import + Settings stubs (Tools, Integrations, Roles, CR cantons) — English

## Acceptance

- Given a Rodieja manager on wp-admin
- When viewing the left menu
- Expect product modules first; Appearance/Plugins/WP Tools/WP Settings/Dashboard/Pages hidden; Media + Users remain
- When opening INS/Import and Settings → Roles
- Expect page-header + shell panels (no bare gray JSON dump); English UI strings

## How to verify

1. Hard-refresh admin — menu matches Read-Only IA as closely as native WP allows
2. `admin.php?page=rodieja-inpart-import` — shelled import, formatted sample
3. Settings → Roles — shelled docs; Users still assigns roles
4. `node .ai/scripts/layer-audit.mjs` green

## Notes

Live Audatex API sync stays out of scope (native local file import remains). Never edit Read-Only.


## Land on main

- Merge commit: `ca799e0d270c8fd660947c90f494dfe167d2783a`
- Branch: `feature/25-admin-ui-readonly-shell` → `main`

### Supervisor / Manager (land)

Epic 25: Read-Only menu + shell parity on native WP. Hid Appearance/Plugins/WP Tools/Settings/Dashboard/Pages; kept Media+Users; renamed Audatex InPart; shelled INS/Import and Settings stubs with page-headers (English). Hard-refresh admin.

