# Spec — English only

## Required

1. Agents produce English for all harness/product artifacts listed in `.ai/rules/english-only.md`.
2. Human language does not change agent language: Spanish input → English output and English file content.
3. Plugin user-visible strings ship as English in source (`__()` first argument).
4. New journeys/tickets are created in English; migrating work translates Spanish stubs.

## Forbidden

1. Spanish (or Spanglish) journeys, board titles, commit messages, or supervisor notes as the default.
2. Editing the **Read-Only** React app from this WordPress harness/product unless explicitly requested against that repo.
3. Leaving known Spanish UI labels in `web/wp-content/plugins/**` after an English-only migration ticket.

## Verify

- Spot-check `.docs/journey/*.md` and recent board `meta.yaml` / issue titles for non-English prose.
- Spot-check plugin Admin/PostTypes `__()` strings for Spanish vocabulary (Vehículos, Pedidos, etc. as labels).
- Confirm Read-Only tree was not modified.
