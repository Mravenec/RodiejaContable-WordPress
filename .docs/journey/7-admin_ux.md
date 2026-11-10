# Epic 7 — admin_ux

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/7-admin_ux/` · Snapshot: `.docs/board/7-admin_ux/`

## Title

Admin UX: hub, summaries, employee↔WP user

## Why

Admin must feel like the Rodieja hub (menus/summaries) and link domain employees to WP users (contador).

## What we did

- [x] Rodieja hub with CPT links + seed
- [x] Native inventory/sales summary screens
- [x] Employee user_id meta ↔ WP user

## Acceptance

- Given Rodieja admin
- When Home/hub opens
- Expect CPT navigation and summaries without a SPA

## Agent notes (constraints / edge cases)

React-shaped chrome; PHP native implementation.

## Board issues (= tickets)

- APP-7-1 hub
- APP-7-2 summaries
- APP-7-3 employee↔user

## How to verify

- `Home / Vehicles / Inventory menus`
- `employee meta`

```bash
node .linear/scripts/board.mjs progress --epic 7
```

## Outcome

Admin hub LANDED. Hierarchy menu = epic 17.
