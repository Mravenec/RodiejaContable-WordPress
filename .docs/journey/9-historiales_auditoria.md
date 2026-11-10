# Epic 9 — historiales_auditoria

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/9-historiales_auditoria/` · Snapshot: `.docs/board/9-historiales_auditoria/`

## Title

History + native audit (revisions/CPT log)

## Why

Domain changes need a native auditable history (event CPT + hooks), not a separate Spring log.

## What we did

- [x] CPT rodieja_evento_historial + save_post hooks
- [x] Admin list filterable by source CPT
- [x] manage_rodieja caps only on history

## Acceptance

- Given a domain CPT save
- When History is opened
- Expect a listed event and cap-gated access

## Agent notes (constraints / edge cases)

Prefer WP hooks; no custom audit table without human OK.

## Board issues (= tickets)

- APP-9-1 CPT + hooks
- APP-9-2 admin list
- APP-9-3 caps

## How to verify

- `Config/History`
- `forbidden without cap`

```bash
node .linear/scripts/board.mjs progress --epic 9
```

## Outcome

Native history LANDED.
