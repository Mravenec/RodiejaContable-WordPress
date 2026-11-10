# Epic 5 — finanzas

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/5-finanzas/` · Snapshot: `.docs/board/5-finanzas/`

## Title

Finance: transactions + WP_Query dashboard

## Why

Income/expense must live as CPT/meta with a totals dashboard and REST mutations gated by contador/admin caps.

## What we did

- [x] Income/expense transactions + tax + links
- [x] Totals dashboard via WP_Query/meta
- [x] Contador caps on REST mutations (no __return_true)

## Acceptance

- Given authenticated contador
- When dashboard/allowed REST is used
- Expect coherent totals; anonymous 401/403

## Agent notes (constraints / edge cases)

Security ticket required on mutations.

## Board issues (= tickets)

- APP-5-1 transactions
- APP-5-2 dashboard
- APP-5-3 REST caps

## How to verify

- `Finance menu`
- `REST anon vs contador`
- `layer-audit`

```bash
node .linear/scripts/board.mjs progress --epic 5
```

## Outcome

Native finance + caps LANDED.
