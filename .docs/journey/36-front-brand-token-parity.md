# Epic 36 — front-brand-token-parity

**Status:** Landed on main · Board: `.linear/epics/36-front-brand-token-parity/`

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


## Land on main

- Merge commit: `4896fc1bddee7381ca9453518769b3cd25534289`
- Branch: `feature/36-front-brand-token-parity` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 36 front-brand-token-parity

Public front now shares Read-Only Ant --wl-* tokens (navy header band, #1890ff actions, #f0f2f5 canvas). Native CSS on Twenty Twenty-Five; Read-Only untouched. Database remains WP posts/meta.

You can confirm it doing the following:
1. Open https://workshop-ledger.ddev.site/ — body has workshop-ledger-front; header navy→accent; page surface #f0f2f5.
2. Primary buttons / content links use #1890ff.
3. Layer audit on main is green.

