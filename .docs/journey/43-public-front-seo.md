# Epic 43 — public-front-seo

**Status:** Planned · Blocked by APP-42-1 · Board: `.linear/epics/43-public-front-seo/`

## Title

Native SEO for the public site

## Why

The public site will be deployed later. Today the front relies on the theme title and the tagline, with no description, social cards, or structured data aimed at a workshop product. WordPress already ships the title tag, canonical links on singular views, robots, and `wp-sitemap.xml`. This epic fills the gaps with core hooks so a future public host is indexable without an SEO plugin.

## What we will do

- [x] Native-first: `document_title_parts`, `wp_head`, `wp_robots`, `get_canonical_url` / `rel_canonical`, Options API for a description default. Core sitemap stays. No Yoast, no Rank Math, no custom table.
- [ ] Add `WorkshopLedger\Front\PublicSeo`, registered like `HeaderAuthLinks` in `ServiceProvider` and `Plugin::boot`.
- [ ] Home and About get a specific title and a plain-text description. Other public views get the document title and a stripped excerpt when one exists.
- [ ] Emit escaped Open Graph and Twitter card tags, plus JSON-LD `WebSite` and `Organization`.
- [ ] Security pass: `esc_attr`, `esc_url`, `wp_strip_all_tags`, and a short description. No raw post HTML in the head.

## Acceptance

- Given `/`, when the HTML head is viewed, then it contains a unique title, a meta description, a canonical URL, `og:title`, `og:description`, `og:url`, and JSON-LD.
- Given `/wp-sitemap.xml`, when it is requested, then WordPress core still answers. This epic does not replace it.
- Given the head markup, when it is reviewed, then every attribute is escaped and the description is plain text.
- English strings. Blocked until the public home reseed (APP-42-1) is done.

## Agent notes

- New class only under `src/Front/`. Do not put it in `src/Services/` (layer audit expects a repository there).
- Description source: page excerpt when set, otherwise `blogdescription`, otherwise a fixed English product sentence.
- Use `wp_json_encode` for JSON-LD and print it inside a `application/ld+json` script. Escape `<` as `\u003c` via the encode flags WordPress already uses.
- Do not remove `SecurityHeaders`.
- Wait until APP-42-1 is in `done` before claiming APP-43-1.

## Areas touched

| Area | Touched? | Notes |
|---|---|---|
| Plugin schema / dbDelta | No | |
| `web/wp-content/plugins/workshop-ledger/` | Yes | `Front/PublicSeo.php`, provider, plugin boot |
| `web/wp-content/mu-plugins/` | No | |
| `docs/diagrams/plugin/` | No | |

## How to verify

```bash
curl -sk https://workshop-ledger.ddev.site/ | head
curl -sk -o /dev/null -w "%{http_code}\n" https://workshop-ledger.ddev.site/wp-sitemap.xml
```

Home head includes description, canonical, Open Graph, and JSON-LD. Sitemap stays 200.

## Outcome

(Filled when the epic lands on origin/main.)
