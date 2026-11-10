# Epic 10 — reportes_export

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/10-reportes_export/` · Snapshot: `.docs/board/10-reportes_export/`

## Title

Native reports: CSV admin + wp_mail digests

## Why

Operational exports (vehicles/parts/tx) and monthly email digests must be native (admin-post + nonce + caps).

## What we did

- [x] CSV export via admin-post + nonce
- [x] Monthly wp_mail digest cap-gated

## Acceptance

- Given admin with caps
- When exporting CSV or sending digest
- Expect file/mail; without cap → denied

## Agent notes (constraints / edge cases)

No external cron; WP-Cron/wp_mail.

## Board issues (= tickets)

- APP-10-1 CSV export
- APP-10-2 wp_mail digest

## How to verify

- `Reports menu`
- `admin-post with nonce`

```bash
node .linear/scripts/board.mjs progress --epic 10
```

## Outcome

Reports/export LANDED.
