# Epic 26 — workshop-ledger-rebrand

**Status:** In progress · Board: `.linear/epics/26-workshop-ledger-rebrand/`

## Title

Rebrand to Workshop Ledger (eliminate Rodieja Contable)

## Why

Rodieja Contable was a paid-client brand. The product must become a neutral **Workshop Ledger** — accounting for vehicle workshops — while staying WordPress-native.

## Rename map (canonical)

| Surface | From | To |
|---|---|---|
| Product UI | Rodieja Contable | Workshop Ledger |
| Plugin folder | rodieja-contable | workshop-ledger |
| Namespace | Rodieja\Contable | WorkshopLedger |
| Text domain | rodieja-contable | workshop-ledger |
| Constants | RODIEJA_PLUGIN_* | WL_PLUGIN_* |
| CPT / meta / options | rodieja_* | wl_* |
| Cap | manage_rodieja_contable | manage_workshop_ledger |
| Role | rodieja_contador | workshop_accountant |
| REST | rodieja/v1 | workshop-ledger/v1 |
| CSS/JS body | rodieja-* | wl-* |
| DDEV | rodieja-wp | workshop-ledger |
| Login | (legacy admin) | admin@workshop.com |
| Repo folder (later) | RodiejaContable-WordPress | WorkshopLedger-WordPress |

## What we will do

- [x] Native-first: CPT/meta/Options/caps migrate in place — no custom tables, no SPA
- [ ] Docs + project.yaml + project-commands.txt
- [ ] Plugin rename + English UI brand
- [ ] One-time Activator migration for existing DB rows
- [ ] DDEV rename + health + login verify

## Acceptance

- Given DDEV Workshop Ledger
- When logging in as admin@workshop.com
- Expect Workshop Ledger admin chrome; no user-facing “Rodieja Contable”
- Health `workshop-ledger/v1/health` 200; layer-audit green
- Read-Only React repo untouched

## How to verify

1. `ddev start` → https://workshop-ledger.ddev.site
2. Login admin@workshop.com (password in project-commands.txt)
3. CPT lists still show data after migrate
4. `node .ai/scripts/layer-audit.mjs`

## Notes

Historical `.docs/journey` epics 1–25 may keep “Rodieja” in titles as history. New work uses Workshop Ledger. GitHub remote rename is human ops after land.
