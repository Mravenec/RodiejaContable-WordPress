# Epic 38 — public-front-professional-copy

**Status:** Landed on main · Board: `.linear/epics/38-public-front-professional-copy/`

## Why

Home/About still read like an internal rewrite note (Read-Only, CPT jargon) and duplicated Sign in CTAs looked broken. Public copy should sound like a WordPress-born product; header Login is enough.

## Acceptance

- No Read-Only / Spring mentions on public pages
- No Sign in / Open admin buttons in page body
- Modern entry layout using `--wl-*` tokens


## Land on main

- Merge commit: `25bb07001e0426afb91bab4a8284818c9f60d2f4`
- Branch: `feature/38-public-front-professional-copy` → `main`

### Supervisor / Manager (land)

Supervisor land note — Epic 38 public-front-professional-copy

Public Home/About now use professional Workshop Ledger product copy (no Read-Only mentions). Body CTAs removed; header Login is enough. Modern wl-front-panel layout on --wl-* tokens.

You can confirm it doing the following:
1. Hard-refresh https://workshop-ledger.ddev.site/ — lead headline + module cards, no Sign in button in body.
2. Open /about/ — product story without Read-Only/Spring wording.
3. Header still shows Login / Open app.

