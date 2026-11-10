# Epic 19 — english-only-surfaces

**Status:** In progress · Board: `.linear/epics/19-english-only-surfaces/` · Snapshot: `.docs/board/19-english-only-surfaces/`

## Title

English-only: journeys, board titles, plugin UI strings

## Why

Spanish/Spanglish leaked into journeys and wp-admin labels. Product must be English-only even when the human writes Spanish. Read-Only is out of scope.

## What we did

- [x] Adopt english-only rule/skill/spec/adapters
- [x] Rewrite journeys 1–18 (+ epic titles) in English
- [x] Translate plugin admin __() strings to English
- [x] Land origin/main

## Acceptance

- Given agent-authored journeys or Rodieja admin labels
- Expect English
- Read-Only repo untouched

## Agent notes (constraints / edge cases)

CPT/meta keys may stay Spanish-derived; labels English. Never edit Read-Only.

## Board issues (= tickets)

- APP-19-1 English journeys + meta titles
- APP-19-2 plugin __() UI English

## How to verify

- `Spot-check journeys`
- `Spot-check AdminMenu/PostTypeRegistrar`
- `No Read-Only paths in diff`

```bash
node .linear/scripts/board.mjs progress --epic 19
```

## Outcome

(At land.)
