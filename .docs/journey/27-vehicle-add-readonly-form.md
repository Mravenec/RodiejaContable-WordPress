# Epic 27 — vehicle-add-readonly-form

**Status:** Landed on main · Board: `.linear/epics/27-vehicle-add-readonly-form/`

## Title

Add Vehicle: classic form like Read-Only (native cascade)

## Why

`post-new.php?post_type=wl_vehiculo` opens the block editor — empty canvas, meta buried in sidebars. Read-Only uses a guided form: Brand → Model → Generation cascade, year, costs, drivetrain/transmission/fuel enums. Operators cannot work the same way.

## What we will do

- [x] **Native-first:** classic CPT editor + meta boxes + thin admin JS (no React SPA, no Elementor)
- [ ] Disable block editor for workshop domain CPTs
- [ ] Vehicle Catalog meta box: cascading selects + English enum fields
- [ ] Keep caps/nonces/sanitize; layer-audit green

## Acceptance

- Given Vehicles → Add Vehicle
- When the screen loads
- Expect classic title + Catalog meta box with Brand → Model → Generation cascade (not Gutenberg)
- Enums for drivetrain / transmission / fuel; English labels
- Saving creates/updates `wl_vehiculo` with meta via native `save_post`

## How to verify

1. Hard-refresh Add Vehicle — no block inserter as primary UI
2. Pick Brand → Model list filters → Generation filters → Year/costs
3. Publish; list shows vehicle; meta persisted
4. `node .ai/scripts/layer-audit.mjs`

## Notes

Read-Only multi-step wizard UX approximated in one classic form (native). Full step wizard SPA is out of scope.


## Land on main

- Merge commit: `87078e7560b3aff6017a6008ae2131a3c7b99ea5`
- Branch: `feature/27-vehicle-add-readonly-form` → `main`

### Supervisor / Manager (land)

Supervisor: Epic 27 LANDED. Add Vehicle uses classic editor + Brand→Model→Generation cascade (native meta). layer-audit green. Hard-refresh post-new.php?post_type=wl_vehiculo.

