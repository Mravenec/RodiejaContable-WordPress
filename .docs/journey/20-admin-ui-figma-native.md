# Epic 20 — admin-ui-figma-native

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/20-admin-ui-figma-native/`

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
