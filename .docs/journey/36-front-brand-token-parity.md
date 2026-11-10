# Epic 36 — front-brand-token-parity

**Status:** In progress · Board: `.linear/epics/36-front-brand-token-parity/`

## Title

Public front shares Read-Only Ant brand tokens (native CSS)

## Why

Admin/login already use `--wl-*` Ant tokens (`#1890ff`, `#001529`, `#f0f2f5`). The public site at `https://workshop-ledger.ddev.site/` only had a thin auth-link style, so the homepage did not read as the same product brand.

## What we will do

- [x] Native-first: CSS variables + TT5-safe selectors only (no custom theme fork)
- [ ] Expand `front.css` with shared `--wl-*` tokens and brand surfaces (header/links/buttons/canvas)
- [ ] Cache-bust enqueue via `filemtime`
- [ ] Verify on DDEV front URL

## Acceptance

- Given the public front loads Workshop Ledger CSS
- When a visitor opens `/`
- Expect primary `#1890ff`, navy accents, surface `#f0f2f5`, and Ant-like links/buttons without SPA

## Agent notes

Read-Only untouched. Database stays WP posts/meta (not Spring schema).

## Outcome

(Filled at land.)
