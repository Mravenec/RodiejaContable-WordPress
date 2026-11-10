# Epic 21 — admin-ui-bugfix

**Status:** Landed on main · Board: `.linear/epics/21-admin-ui-bugfix/`

## Title

Admin UI bugfix: English labels + Transactions list + sticky menus

## Why

Finance showed Spanglish (**Nueva Transaction**). Transactions list duplicated Date / left Sentido raw. Custom admin CSS forced `left: 160px` on every `.wp-submenu`, so current modules stayed as stuck flyouts and blocked selection.

## What we did

- [x] Add Transaction / My Profile / New province
- [x] Transactions: Direction + single Date + tablenav flex
- [x] Restore native WP submenu behavior (inline current, hover flyouts only)
- [x] Land origin/main

## Acceptance

- Current section submenu expands **inline** under the parent (clickable)
- Other modules fly out only on hover / opensub, then close
- English menu labels · layer-audit green · Read-Only untouched

## How to verify

1. Open Reports — Overview/Sales/… appear under Reports in the sidebar (not a stuck right flyout)
2. Hover Vehicles — temporary flyout; leave — closes
3. Finance submenu: **Add Transaction**


## Land on main

- Merge commit: `0bdf9cdb6f60a2f2db7d9a26c1e6ac6519b04505`
- Branch: `feature/21-admin-ui-bugfix` → `main`

### Supervisor / Manager (land)

Supervisor: Epic 21 — English leftovers, Transactions columns, sticky submenu flyouts fixed (no left:160px). layer-audit green. Read-Only untouched.

