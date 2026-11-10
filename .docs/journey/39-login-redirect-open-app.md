# Epic 39 — login-redirect-open-app

**Status:** In progress · Board: `.linear/epics/39-login-redirect-open-app/` · Snapshot: `.docs/board/39-login-redirect-open-app/`

## Title

After login, open Workshop Ledger app (native redirect)

## Why

Header **Login** used `wp_login_url( home_url( '/' ) )`, so after sign-in operators landed on the public home instead of **Open app** (`admin.php?page=workshop-ledger`).

## What we will do

- [x] **Native-first:** `wp_login_url( $app )` + `login_redirect` filter (no custom auth)
- [ ] Ticket: fix header/footer Login redirect + login_redirect for capable users
- [ ] GATE: log in from front → land on Workshop Ledger hub

## Acceptance

- Given a guest on the public site
- When they click **Login** and authenticate as an operator/admin
- Expect they land on `wp-admin/admin.php?page=workshop-ledger` (same as **Open app**)

## Verify

1. Log out → https://workshop-ledger.ddev.site/ → Login → admin / Admin123!
2. URL is `…/wp-admin/admin.php?page=workshop-ledger`
3. Header shows **Open app**

## Notes

Native WordPress `login_redirect` + `wp_login_url( $redirect )`. Caps: `manage_workshop_ledger` or `manage_options`.
