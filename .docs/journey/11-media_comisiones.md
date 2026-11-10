# Epic 11 — media_comisiones

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/11-media_comisiones/` · Snapshot: `.docs/board/11-media_comisiones/`

## Title

Media Library + payment/commission meta on orders

## Why

Vehicles/parts need WP media; orders need commission/payment meta visible in admin.

## What we did

- [x] Featured image/gallery on vehicle and part
- [x] Commission/payment meta on order + admin columns

## Acceptance

- Given a vehicle with image
- When edited in admin
- Expect Media Library; orders show commission

## Agent notes (constraints / edge cases)

Core Media Library; no custom CDN.

## Board issues (= tickets)

- APP-11-1 featured/gallery
- APP-11-2 commission meta

## How to verify

- `vehicle editor media`
- `order list columns`

```bash
node .linear/scripts/board.mjs progress --epic 11
```

## Outcome

Media + commissions LANDED.
