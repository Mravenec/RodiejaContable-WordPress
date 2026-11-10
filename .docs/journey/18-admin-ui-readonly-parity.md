# Epic 18 — admin-ui-readonly-parity

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/18-admin-ui-readonly-parity/` · Snapshot: `.docs/board/18-admin-ui-readonly-parity/`

## Title

Admin UI: máxima fidelidad visual al Read-Only (native CSS/PHP)

## Why

Rodieja Contable Read-Only (React/Ant) define la IA y cromática del negocio. En WP había que parecer **lo más idéntico posible** sin clonar el SPA: sider navy, canvas `#f0f2f5`, tablas Ant-like, Inicio y Ver por Generaciones.

## What we did

- [x] Chrome CSS: `#adminmenu` / admin bar navy, canvas surface, login Ant header
- [x] CPT list tables: toolbar + hover + filtros (`rodieja-cpt-list`)
- [x] Inicio: hero + Indicadores + Módulos; Jerarquía: toolbar + árbol
- [x] Board → feature push → land `origin/main`

## Acceptance

- Given admin Rodieja en wp-admin / login
- When abre Inicio, listas CPT, Ver por Generaciones, login
- Expect look muy cercano al Read-Only (Ant Pro palette/IA)
- Sin React SPA; caps/DI/`layer-audit` OK

## Agent notes

No SPA. CSS scoped a `body.rodieja-contable-admin` / `rodieja-cpt-list`. Paleta `#001529` / `#1890ff` / `#f0f2f5`.

## Areas touched

| Area | Touched? | Notes |
|---|---|---|
| `assets/css/admin.css` / `login.css` | sí | |
| `AdminAssets.php` | sí | body class list |
| `AdminMenu.php` / `JerarquiaAdminPage.php` | sí | markup |

## Board issues

- APP-18-1 chrome CSS
- APP-18-2 CPT lists
- APP-18-3 Inicio + jerarquía

## How to verify

- wp-admin Inicio / Vehículos / Ver por Generaciones
- wp-login.php
- `node .ai/scripts/layer-audit.mjs`

## Outcome

UI nativa con máxima fidelidad al Read-Only (chrome + listas + Inicio/jerarquía) en `origin/main`. Epic 18 completed 100%.
