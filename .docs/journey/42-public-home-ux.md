# Epic 42 — public-home-ux

**Status:** In progress · Board: `.linear/epics/42-public-home-ux/`

## Title

Public home: a distinctive workshop landing

## Why

The public home is a kicker, a headline, a paragraph, and a numbered list on a beige page. It reads as a document, not as the front door of a workshop ledger. A visitor should see the shape of a working day: a vehicle in the yard, a part off the shelf, money recorded, an Audatex InPart order still open. Login stays in the header. There is still no form on the page.

## What we will do

- [x] Native-first: Home and About remain WordPress pages. Markup is seeded post content. Presentation is `assets/css/front.css`. No page builder, no new theme, no custom table, no web font.
- [ ] Bump `PublicSiteSeed` to `wl_public_site_seeded_v4` so the live site replaces v3.
- [ ] Give the home a real landing rhythm: hero, a day-in-the-shop sequence, four modules with distinct visual weight, and a short close. About uses the same system and less copy.
- [ ] Keep system fonts, the existing navy header, and WCAG-readable ink on paper. Stack on small screens. Two columns for the modules from about 720px.

## Acceptance

- Given a hard refresh of `/`, when the page loads, then the visitor sees a workshop landing rather than one generic card.
- Given the page body, when it is read, then there is no form and no Sign in button. Login stays in the header.
- Given `/about/`, when it loads, then it uses the same composition.
- English copy. Admin chrome is unchanged.

## Agent notes

- `PublicSiteSeed::OPTION_FLAG` is `wl_public_site_seeded_v3`. Change it to v4 and delete the v3 option inside `maybe_run` so the next request reseeds.
- `maybe_run` already runs from `Plugin::boot`. Do not add a second seed path.
- Scope CSS to `body.workshop-ledger-front`. Do not restyle `admin.css`.
- Escape every visible string with `esc_html__` / `esc_html`, matching the current seed.
- Do not add Google fonts or a JavaScript framework.

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

(Filled when the epic lands on origin/main.)
