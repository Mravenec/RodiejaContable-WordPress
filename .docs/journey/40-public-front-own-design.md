# Epic 40 — public-front-own-design

**Status:** Landed on main · Board: `.linear/epics/40-public-front-own-design/`

## Why

The public home is readable and on-brand, but it is one generic card on the Ant surface. Visitors should understand the product before they look for Login: a stronger opening, a short story, and modules that feel like Workshop Ledger rather than a default admin skin.

## What we will do

- [x] Native-first: keep Home/About as pages. Design lives in plugin CSS + seeded HTML. No new theme, no custom tables.
- [ ] Ticket: seed v3 markup (hero, story, numbered modules) and a public-only composition in `front.css`
- [ ] Admin chrome stays on the existing tokens. This pass is the public front only.

## Acceptance

- Home explains the product before any login prompt
- No body form and no Sign in button in the page
- Layout is a landing (hero + story + modules), not a single generic card
- English copy

## Verify

1. Hard-refresh https://workshop-ledger.ddev.site/
2. Hero, short story, and four modules are visible before the header Login note
3. /about/ uses the same composition


## Land on main

- Merge commit: `e11f7a23a3455391251f0ebd35461d163d2a8601`
- Branch: `feature/40-public-front-own-design` → `main`

### Supervisor / Manager (land)

Public home now opens with a hero and a short story, then four numbered modules. No form on the page. Login stays in the header. Admin chrome is unchanged.

