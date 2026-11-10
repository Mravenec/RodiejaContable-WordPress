# Epic 18 — admin-ui-readonly-parity

**Status:** In progress · Board: `.linear/epics/18-admin-ui-readonly-parity/` · Snapshot: `.docs/board/18-admin-ui-readonly-parity/`

## Title

Admin UI: máxima fidelidad visual al Read-Only (native CSS/PHP)

## Why

Rodieja Contable Read-Only (React/Ant) define la IA y la cromática que el negocio reconoce. En WordPress ya hay menús y paleta parcial; hace falta **parecer lo más idéntico posible** sin clonar el SPA: chrome, listas CPT e Inicio/jerarquía con CSS/PHP nativos.

## What we will do

- [ ] Chrome CSS: menú lateral navy, canvas `#f0f2f5`, login, admin bar scoped
- [ ] List tables CPT Rodieja (Ant Table–like) + filtros/botones
- [ ] Pulir Inicio (hero/métricas/tiles) + Ver por Generaciones (árbol)
- [ ] Board process: claim → worktree → summarize → land `origin/main`

## Acceptance

- Given un admin Rodieja en wp-admin
- When navega Inicio, Vehículos (lista + jerarquía), Inventario, login
- Expect look & IA muy cercanos al Read-Only (navy/azul Ant, cards, tablas)
- Sin bundle React embebido; caps/nonces/DI intactos; layer-audit verde

## Agent notes (constraints / edge cases)

- **No SPA clone.** Solo `admin_enqueue_scripts` + CSS scoped + markup PHP existente.
- Fuente visual: paleta Read-Only ya en plugin (`#001529` / `#1890ff` / `#f0f2f5`); sin repo Read-Only en disco.
- CSS scoped a `body.rodieja-contable-admin` / pantallas `rodieja_*` para no romper core WP global.

## Areas touched (unified WordPress tree)

| Area | Touched? | Notes |
|---|---|---|
| `assets/css/admin.css` / `login.css` | sí | Fidelity |
| `AdminAssets.php` | sí | enqueue login + body |
| `AdminMenu.php` / `JerarquiaAdminPage.php` | menor | markup polish |
| Plugin schema / dbDelta | — | No |

## Board issues (= tickets)

- APP-18-1 Plugin · chrome CSS menú/canvas/login
- APP-18-2 Plugin · list tables CPT look Read-Only
- APP-18-3 Plugin · pulir Inicio + Ver por Generaciones

## How to verify

- wp-admin Inicio / Vehículos / Ver por Generaciones / edit.php CPT
- wp-login.php
- `node .ai/scripts/layer-audit.mjs`

## Outcome

(Filled at land.)
