# Epic 14 — pedidos_ins_import

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/14-pedidos_ins_import/` · Snapshot: `.docs/board/14-pedidos_ins_import/`

## Title

INS/Audatex orders: local JSON/CSV import + Options (no live API)

## Why

Bring Audatex/INS orders without live sync: Options settings + local JSON/CSV import into CPT/items.

## What we did

- [x] Audatex/INS Options (no live API)
- [x] JSON/CSV import → orders + items
- [x] Journey map Spring sync → native import

## Acceptance

- Given valid JSON/CSV
- When imported in admin
- Expect CPT orders/items; settings in Options

## Agent notes (constraints / edge cases)

Explicit non-goal: live Audatex sync.

## Board issues (= tickets)

- APP-14-1 Options settings
- APP-14-2 JSON/CSV import
- APP-14-3 Spring→native map

## How to verify

- `Audatex/INS settings`
- `import page with fixture`

```bash
node .linear/scripts/board.mjs progress --epic 14
```

## Outcome

Local INS/Audatex import LANDED (no live API).
