# Epic 22 — admin-menu-collapse

**Status:** In progress · Board: `.linear/epics/22-admin-menu-collapse/`

## Title

Admin menu: collapse current section on re-click (native JS)

## Why

Users expect a second click on Inventory / Finance / Reports to collapse the open submenu. Core WP keeps the current section open; gap filled with a thin admin script using WP’s own `#adminmenu` markup (human OK).

## Native-first

- Gap: core has whole-sidebar Collapse Menu only — not per-section toggle.
- Approach: `admin_enqueue_scripts` + small vanilla JS toggling a class on `li.wp-has-current-submenu`. No SPA/React.
- Human OK: “Make it collapse” / “Can Collapse and still native?”

## What we will do

- [x] Record native gap + OK
- [ ] Enqueue `admin-menu-collapse.js` + CSS hide when collapsed
- [ ] Verify toggle on current section; other modules unchanged
- [ ] Land origin/main

## Acceptance

- Given open Inventory submenu, when clicking Inventory again → submenu collapses
- Click again → expands
- Non-current modules still hover-flyout / navigate as WP default
- layer-audit green · English · Read-Only untouched
