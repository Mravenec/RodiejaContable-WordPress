# Epic 37 — public-front-workshop-content

**Status:** Landed on main · Board: `.linear/epics/37-public-front-workshop-content/`

## Title

Public front pages/nav aligned to Workshop Ledger (native WP)

## Why

The public site still showed WordPress Sample Page filler (bike messenger / XYZ Doohickey). That is not Workshop Ledger. Operators need a branded home + about surface that matches the product and points into wp-admin / login.

## What we will do

- [x] Native-first: WP pages + reading settings + navigation (no custom SPA theme)
- [ ] Replace Sample Page with Workshop Ledger About
- [ ] Add a Home landing page; set as static front page
- [ ] Tagline + English copy; keep Privacy Policy draft or product-aligned stub
- [ ] Verify nav no longer shows random sample filler as primary story

## Acceptance

- Given a visitor opens `https://workshop-ledger.ddev.site/`
- When they read the header/nav and main content
- Expect Workshop Ledger product copy (home + about), not Sample Page lorem

## Agent notes

Read-Only untouched. Admin remains the working app; public front is a thin branded entry.

## Outcome

(Filled at land.)


## Land on main

- Merge commit: `a65be74e0ee15930c54a3325944b7c89ee54fb04`
- Branch: `feature/37-public-front-workshop-content` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 37 public-front-workshop-content

Seeded native Home + About pages, static front page, retired Sample Page and Hello world. Header auth link is “Open app”. Read-Only untouched.

You can confirm it doing the following:
1. Open https://workshop-ledger.ddev.site/ — Home product copy, not Blog/Hello world.
2. Nav shows Home / About (and Open app when logged in), not Sample Page filler.
3. Layer audit on main is green.

