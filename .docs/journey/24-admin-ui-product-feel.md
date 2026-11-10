# Epic 24 — admin-ui-product-feel

**Status:** Landed on main · Board: `.linear/epics/24-admin-ui-product-feel/`

## Title

Admin UI: product-feel chrome (CPT headers + denser Ant shell)

## Why

Even after list CSS, screens still *feel* like classic WordPress. Need a stronger product shell: real page headers on every CPT list, denser Ant Pro table card, less WP chrome — still native list tables.

## Native-first

- Gap: `edit.php` title/toolbar is core markup; CSS alone is not enough for “product” feel.
- Approach: thin PHP chrome on `all_admin_notices` + much denser `admin-list.css` / shell. No SPA.
- Human: “Aun no siento las interfaces modernas”.

## What we will do

- [ ] `CptListChrome` page-header on all `rodieja_*` lists
- [ ] Stronger list CSS (surface, card stack, pills, hide classic title chrome)
- [ ] Land origin/main

## Acceptance

- CPT lists open with a Rodieja page-header (title + lede + Add CTA)
- Table sits in one elevated product card
- Feels closer to Read-Only / Figma without leaving WP native


## Land on main

- Merge commit: `2c1e6e4c2437e88a8e69bdf742c40f91458b86fa`
- Branch: `feature/24-admin-ui-product-feel` → `main`

### Supervisor / Manager (land)

Epic 24 lands CPT list product chrome: CptListChrome page-header on every Rodieja list screen, denser Ant-style admin-list.css (hide WP h1, pill tabs, card table). Hard-refresh Transactions / Vehicles / Inventory lists — product header + modern shell should replace stock WP chrome.

