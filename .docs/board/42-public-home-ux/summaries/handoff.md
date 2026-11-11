---
git: "cd .worktrees/42-public-home-ux/_epic && git push -u origin feature/42-public-home-ux"
recorded_at: 2026-11-11T03:34:27.762Z
---

I finished epic 42 after the home ticket reached done. The public home is seeded again as WordPress page content (flag wl_public_site_seeded_v4) and restyled in front.css. The layer audit on feature/42-public-home-ux reported no violations.

You can confirm it doing the following:

1. Open https://workshop-ledger.ddev.site/ after this epic is on main and hard-refresh.
2. The page opens with a workshop headline, then "A day on the floor" (Morning, Late morning, Afternoon, Before close).
3. Under that, "What opens after login" shows Vehicles, Inventory, Finance, and Audatex InPart.
4. The page body has no form and no Sign in button. Login stays in the header.
5. Open https://workshop-ledger.ddev.site/about/ and confirm it uses the same header treatment and a short audience section.
