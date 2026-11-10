# Epic 22 — admin-menu-collapse

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/22-admin-menu-collapse/`

## Title

Admin menu: collapse current section on re-click (native JS)

## Why

Second click on Inventory / Finance / Reports should collapse the open submenu. Core only collapses the whole sidebar; thin admin JS fills the gap (human OK).

## What we did

- [x] `assets/js/admin-menu-collapse.js` toggles `rodieja-submenu-collapsed`
- [x] CSS hides current `.wp-submenu` when collapsed
- [x] Enqueued via `AdminAssets` (filemtime)
- [x] Land origin/main

## Acceptance

- Re-click current top-level → collapse; again → expand
- Non-current modules unchanged
- layer-audit green · Read-Only untouched
