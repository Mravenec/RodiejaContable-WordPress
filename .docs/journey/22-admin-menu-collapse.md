# Epic 22 — admin-menu-collapse

**Status:** Landed on main · Board: `.linear/epics/22-admin-menu-collapse/`

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


## Land on main

- Merge commit: `e1b5ff3f8c304081473de3b5cbe30d9c2ebf50ac`
- Branch: `feature/22-admin-menu-collapse` → `main`

### Supervisor / Manager (land)

Supervisor: Epic 22 — current admin section collapses on parent re-click (native JS). layer-audit green. Read-Only untouched.

