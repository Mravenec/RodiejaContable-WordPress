# Epic 18 — admin-ui-readonly-parity

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/18-admin-ui-readonly-parity/` · Snapshot: `.docs/board/18-admin-ui-readonly-parity/`

## Title

Admin UI: maximum visual fidelity to Read-Only (native CSS/PHP)

## Why

Read-Only (React/Ant) defines the IA and palette. WP needed to look as identical as possible without cloning the SPA: navy sider, #f0f2f5 canvas, Ant-like tables, Home and hierarchy.

## What we did

- [x] Chrome CSS: admin menu/bar navy, canvas, Ant login
- [x] CPT list tables: toolbar + hover (rodieja-cpt-list)
- [x] Home: hero + Indicators + Modules; hierarchy toolbar + tree
- [x] Board → feature push → land origin/main

## Acceptance

- Given Rodieja admin / login
- When opening Home, CPT lists, View by Generations, login
- Expect look very close to Read-Only
- No React SPA; caps/DI/layer-audit OK

## Agent notes (constraints / edge cases)

No SPA. CSS scoped to rodieja-contable-admin / rodieja-cpt-list. Never edit Read-Only.

## Board issues (= tickets)

- APP-18-1 chrome CSS
- APP-18-2 CPT lists
- APP-18-3 Home + hierarchy

## How to verify

- `Home / Vehicles / View by Generations`
- `wp-login.php`
- `layer-audit`

```bash
node .linear/scripts/board.mjs progress --epic 18
```

## Outcome

Native UI max-fidelity to Read-Only on origin/main.
