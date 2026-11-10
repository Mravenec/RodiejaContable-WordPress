# Epic 2 — native_domain

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/2-native_domain/` · Snapshot: `.docs/board/2-native_domain/`

## Title

Native domain: seed + CPT admin smoke

## Why

Materialize the Rodieja domain as native CPT/meta and seed demo data for admin/REST smoke — no Spring tables.

## What we did

- [x] Demo seed (vehicles, orders, finance) via native services
- [x] WP-CLI + REST count smoke
- [x] Land status confirmed on board

## Acceptance

- Given plugin active
- When seed runs
- Expect demo CPTs in wp-admin and coherent REST counts

## Agent notes (constraints / edge cases)

WP APIs only (wp_insert_post/meta). Prefer idempotent seed.

## Board issues (= tickets)

- APP-2-1 demo seed
- APP-2-2 WP-CLI + REST smoke + land

## How to verify

- `seed CLI if present`
- `REST stats`
- `wp-admin CPT lists`

```bash
node .linear/scripts/board.mjs progress --epic 2
```

## Outcome

Domain seeded; LANDED. Catalog admin = epic 3.
