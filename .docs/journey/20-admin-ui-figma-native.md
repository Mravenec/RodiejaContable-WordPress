# Epic 20 — admin-ui-figma-native

**Status:** Landed on main · Board: `.linear/epics/20-admin-ui-figma-native/`

## Title

Admin UI: Figma-grade native shells (Read-Only fidelity)

## Why

Divi/Figma-level polish while staying WordPress-native. Read-Only is the visual source of truth; Contable screens need denser product shells without SPA/Divi.

## What we did

- [x] `admin-shell.css` design system (page header, hero chips, shell grid, table polish)
- [x] Enqueue shell after admin.css with filemtime cache-bust
- [x] Home / View by Generations / Reports / Settings shell markup
- [x] Plugin version 0.4.0 · land origin/main

## Acceptance

- Given Rodieja admin screens
- Expect Figma-like density closer to Read-Only
- No Divi, no React SPA, Read-Only untouched, layer-audit green

## Outcome

Figma-grade native Contable chrome on origin/main (epic 20).


## Land on main

- Merge commit: `957ceccd95730aabe1f5480e490aa72c4e47dd8f`
- Branch: `feature/20-admin-ui-figma-native` → `main`

### Supervisor / Manager (land)

Supervisor: Epic 20 Figma-grade native shells (admin-shell.css + Home / View by Generations / Reports / Settings). Plugin 0.4.0. layer-audit green. Health probe skipped (8080 down). Read-Only untouched. English-only.

