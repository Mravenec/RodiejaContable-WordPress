---
git: "git push origin main"
recorded_at: 2026-11-10T16:29:54.051Z
---

Supervisor land note — Epic 35 orphan-meta-english-cleanup

Migrated orphan wl_estado meta to taxonomy wl_estado_vehiculo (then deleted), blocked reintroduction via domain import, and English-only gallery/pedido leftovers. Read-Only untouched.

You can confirm it doing the following:
1. ddev exec wp --path=web db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='wl_estado';" → 0 after boot.
2. Vehicle status still appears via taxonomy wl_estado_vehiculo on edit screen.
3. Gallery help text and Order meta labels are English; layer audit green.
