# Epic 42 — public-home-ux

**Status:** In progress · Board: `.linear/epics/42-public-home-ux/`

## Title

Public home: a distinctive workshop landing

## Why

The public home is a kicker, a headline, a paragraph, and a numbered list on a beige page. It reads as a document, not as the front door of a workshop ledger. A visitor should see the shape of a working day: a vehicle in the yard, a part off the shelf, money recorded, an Audatex InPart order still open. Login stays in the header. There is still no form on the page.

## What we will do

- [x] Native-first: Home and About remain WordPress pages. Markup is seeded post content. Presentation is `assets/css/front.css`. No page builder, no new theme, no custom table, no web font.
- [x] Bump `PublicSiteSeed` to `wl_public_site_seeded_v4` so the live site replaces v3.
- [x] Give the home a real landing rhythm: hero, a day-in-the-shop sequence, four modules with distinct visual weight, and a short close. About uses the same system and less copy.
- [x] Keep system fonts, the existing navy header, and WCAG-readable ink on paper. Stack on small screens. Two columns for the modules from about 720px.

## Acceptance

- Given a hard refresh of `/`, when the page loads, then the visitor sees a workshop landing rather than one generic card.
- Given the page body, when it is read, then there is no form and no Sign in button. Login stays in the header.
- Given `/about/`, when it loads, then it uses the same composition.
- English copy. Admin chrome is unchanged.

## Agent notes

- `PublicSiteSeed::OPTION_FLAG` is `wl_public_site_seeded_v4`. `maybe_run` deletes the v1, v2, and v3 flags after a successful run. The next front request reseeds. No second seed path; `Plugin::boot` still calls `maybe_run`.
- Home sections: `wl-hero`, `wl-day` (four `wl-day__step` items), `wl-modules` (four `wl-module` articles), `wl-front-note`. About uses the same hero, `wl-audience`, and the same note. No form and no Sign in button.
- CSS stays in `assets/css/front.css`, scoped to `body.workshop-ledger-front`. Day sequence is a vertical rail, then a 4-column row from 720px. Modules are a 2x2 grid from 720px. Navy header and paper/brass tokens stay. No Google fonts, no new stylesheet, no JavaScript.
- Visible copy is escaped with `esc_html__`. `blogname` stays Workshop Ledger.
- DDEV serves the primary checkout. This ticket does not copy plugin files onto that checkout and does not curl it as proof.

## Areas touched

| Area | Touched? | Notes |
|---|---|---|
| Plugin schema / dbDelta | No | |
| `web/wp-content/plugins/workshop-ledger/` | Yes | `PublicSiteSeed.php`, `assets/css/front.css` |
| `web/wp-content/mu-plugins/` | No | |
| `docs/diagrams/plugin/` | No | |

## How to verify

Hard-refresh `https://workshop-ledger.ddev.site/` and `/about/` after the seed flag changes. Hero, day sequence, and four modules are visible. No form in the body.

## Outcome

APP-42-1 is implemented on its ticket branch. The epic is not on `origin/main` yet. Land is a later step.
