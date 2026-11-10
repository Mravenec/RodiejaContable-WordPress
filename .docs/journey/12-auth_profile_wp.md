# Epic 12 — auth_profile_wp

**Status:** Done · LANDED on `origin/main` · Board: `.linear/epics/12-auth_profile_wp/` · Snapshot: `.docs/board/12-auth_profile_wp/`

## Title

Native Auth/Profile: users, caps, profile meta

## Why

Identity = WP users. Domain employees link to users; contador vs admin matrix must be verified.

## What we did

- [x] Profile fields + employee↔user_id link
- [x] Contador vs admin roles/caps matrix verified

## Acceptance

- Given contador user
- Expect scoped access vs Rodieja administrator

## Agent notes (constraints / edge cases)

No external auth framework.

## Board issues (= tickets)

- APP-12-1 profile + link
- APP-12-2 caps matrix

## How to verify

- `WP profile`
- `contador vs admin caps`

```bash
node .linear/scripts/board.mjs progress --epic 12
```

## Outcome

Native auth/profile LANDED.
