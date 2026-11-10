# Epic 21 — admin-ui-bugfix

**Status:** In progress · Board: `.linear/epics/21-admin-ui-bugfix/`

## Title

Admin UI bugfix: English labels + Transactions list

## Why

Finance submenu still shows Spanglish (**Nueva Transaction**, **Mi Perfil**). Transactions list duplicates **Date**, leaves **Sentido** untranslated, and the CPT tablenav shell needs a cleaner bulk/filter row.

## What we will do

- [x] Native-first: menu labels, list columns, CSS only (no custom tables)
- [ ] English leftovers in AdminMenu / Cantones / Finance meta
- [ ] Transactions columns: one Date, Direction with Income/Expense, drop WP date duplicate
- [ ] CPT tablenav flex polish for bulkactions + filters
- [ ] Land on origin/main

## Acceptance

- Given Finance submenu → **Add Transaction** (not Nueva Transaction)
- Given Settings submenu → **My Profile**
- Given Transactions list → single Date column, Direction shows Income/Expense, bulk/filter row aligned
- layer-audit green · Read-Only untouched · English only

## How to verify

1. Open `edit.php?post_type=rodieja_transaccion` — labels + columns
2. Hover Finance / Settings menus — English only
3. `node .ai/scripts/layer-audit.mjs`
