---
description: Native WordPress APIs first — custom only after propose + human OK
alwaysApply: true
---

# Native-first (day zero law)

**Priority:** use WordPress core APIs first. Do **not** invent custom tables, packages, or mini-frameworks when a native API already fits.

## 1. Prefer native (default)

Use these **before** writing custom persistence or architecture:

| Need | Native first |
|---|---|
| Content / entities | CPT, taxonomies, post meta, revisions |
| Settings / flags | Options API, Settings API |
| Queries | `WP_Query`, `get_posts`, `get_terms`, user/query APIs |
| HTTP API | REST (`register_rest_route`), admin-ajax only if REST unfit |
| AuthZ / CSRF | Capabilities, nonces, `current_user_can`, Application Passwords |
| Scheduling | WP-Cron |
| Files / media | Media Library / filesystem APIs |
| Extensibility | Actions & filters |
| CLI | WP-CLI |
| i18n | Text domain / l10n APIs |

Sanitize · validate · escape with core helpers. SQL via `$wpdb->prepare` **only when** native object APIs are insufficient.

## 2. Custom requires a proposal

**Custom** means anything beyond core APIs for that ticket, including:

- New `dbDelta` / custom tables
- New Composer runtime libraries (beyond PSR-4 / WPCS / PHPUnit)
- New patterns that bypass CPT/Options/REST when those would work
- Framework-like code (Laravel-style routers, Doctrine, etc. — always forbidden)

**Before implementing custom**, the agent must:

1. State the **native option** and why it was considered.
2. State the **custom option** and the concrete gap native cannot cover.
3. Ask the human: proceed native / proceed custom / hybrid.
4. Record the decision on the board ticket (`evidence` or checklist note).

No silent custom. Day zero default answer is **native**.

## 3. Plugin scaffold vs feature custom

`web/wp-content/plugins/<module>/` (DI, Services, Repositories) is the **house** for plugin code when a plugin is needed.

It does **not** mean every feature gets a custom table. Prefer:

- CPT + meta + REST over custom table + repository
- Options/Settings over ad-hoc config tables
- Core REST / `show_in_rest` over bespoke CRUD when enough

Use Repository/`$wpdb` when the human approved custom persistence or native APIs are proven insufficient.

## 4. Always forbidden (not “custom with OK”)

Laravel/Illuminate, Doctrine/Eloquent, Symfony Framework Bundle, Spring-style `0/1/2` app folders, REST mutations with `__return_true`, raw SQL concatenation.

## 5. Enforcement surfaces

| Surface | Role |
|---|---|
| This rule + `.ai/skills/native-first/SKILL.md` | Agent law |
| `AGENTS.md` / `CLAUDE.md` / `.cursor` / `.claude` | Entry mirrors |
| `.ai/project.yaml` → `native_first: true` | Project flag |
| Board checklists | First item: “Native API chosen? If custom, human OK recorded” |
| `layer-audit` | Blocks forbidden packages / unsafe REST — not a substitute for human OK on custom schema |

Skill: `.ai/skills/native-first/SKILL.md`
