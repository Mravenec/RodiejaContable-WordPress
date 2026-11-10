# Epic 1 — foundation

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/1-foundation/` · Snapshot: `.docs/board/1-foundation/`

## Title

Foundation: WP + plugin scaffold + roles

## Why

Rodieja Contable needs a unified WordPress plugin with DI, health REST, and roles/caps — not a Spring port. Without this base there is no safe native domain.

## What we did

- [x] Scaffold rodieja-contable with Composer PSR-4, Container, ServiceProvider
- [x] Health REST + Security (CapabilityGate, OutputEscaper)
- [x] Roles/caps + MU-plugin hardening baseline
- [x] Native-first map in AGENTS / journey

## Acceptance

- Given DDEV/WP with the plugin active
- When health is called
- Expect 200 and DI wiring
- Given a user without Rodieja caps
- Expect REST/admin mutations denied

## Agent notes (constraints / edge cases)

Native-first from day one. No Laravel/Doctrine. Layer-audit green. LANDED = origin/main.

## Board issues (= tickets)

- APP-1-1 scaffold DI + health REST
- APP-1-2 roles/caps + MU verify
- APP-1-3 docs journey + native map

## How to verify

- `ddev wp plugin list`
- `health endpoint`
- `node .ai/scripts/layer-audit.mjs`

```bash
node .linear/scripts/board.mjs progress --epic 1
```

## Outcome

Plugin foundation on origin/main; board 100%/LANDED.
