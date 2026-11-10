# Epic 19 — english-only-surfaces

**Status:** Landed on main · Board: `.linear/epics/19-english-only-surfaces/`

## Title

English-only: journeys, board titles, plugin UI strings

## Why

Spanish/Spanglish leaked into journeys and wp-admin labels. Product must be English-only even when the human writes Spanish. Read-Only is out of scope.

## What we did

- [x] Adopt english-only rule/skill/spec/adapters (parity with harness)
- [x] Rewrite journeys 1–19 (+ epic meta titles) in English
- [x] Translate plugin `__()` admin UI strings to English
- [x] Land to origin/main

## Acceptance

- Given any journey or Rodieja admin menu label authored by agents
- Expect English prose/labels
- Read-Only repo untouched

## Agent notes

CPT/meta keys may stay Spanish-derived; labels English. Never edit Read-Only.

## Board issues

- APP-19-1 English journeys + meta titles
- APP-19-2 plugin __() UI English

## How to verify

- Spot-check journeys for Spanish
- Spot-check AdminMenu / PostTypeRegistrar `__()` strings
- Diff must not touch Read-Only

## Outcome

English-only surfaces shipped on origin/main. Harness and Rodieja share the english-only rule/skill/spec.


## Land on main

- Merge commit: `82cd06c77844edf9061ca7ba92e3bef90b2d846c`
- Branch: `feature/19-english-only-surfaces` → `main`

### Supervisor / Manager (land)

Epic 19 landed: English-only journeys, board titles, plugin admin UI. Read-Only not modified.

