# Epic 23 — cpt-list-figma-mobile

**Status:** In progress · Board: `.linear/epics/23-cpt-list-figma-mobile/`

## Title

All CPT lists: Figma/Read-Only density + mobile (native CSS)

## Why

Parts / Vehicles / Generations / every `rodieja_*` `edit.php` still feels classic WP. Users want Divi/Figma-grade density closer to Read-Only — still native list tables — including usable mobile admin.

## Native-first

- Keep `WP_List_Table` / `edit.php` — no React SPA, no Divi.
- Deepen `body.rodieja-cpt-list` CSS (+ shell) for all CPTs already tagged by `AdminAssets`.
- Mobile: responsive toolbar + horizontal table scroll; WP auto-fold for sidebar.
- Human ask: polish **all** list screens + mobile.

## What we will do

- [ ] Page-header / tabs / toolbar / table density for all CPT lists
- [ ] Mobile breakpoints for list chrome
- [ ] English leftovers in list filters (All vehicles / All statuses)
- [ ] Land origin/main

## Acceptance

- Every `rodieja_*` list looks like one Ant/Figma product table shell
- Narrow viewport: toolbar wraps, table scrolls, actions tappable
- English filter labels · layer-audit green · Read-Only untouched
