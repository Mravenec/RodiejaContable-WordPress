# Harness — WordPress (unified) · MariaDB · Custom Plugins

Neutral multi-agent scaffold on a **realistic WordPress tree** — one DDEV site, custom plugins under `wp-content`, harness control plane beside it (same idea as guilford/porton/tandon: CMS docroot + `.ai`/`.ddev`, **not** a Spring `0/1/2` monorepo).

Board: **`.linear/`** (private.board). Worktrees: **`.ai/scripts/worktree-*.mjs`**.

## Day zero layout

```
.ai/ .claude/ .cursor/ .ddev/ .docs/ .linear/
web/                                      # WordPress docroot
  wp-content/plugins/harness-plugin/      # OOP · DI · SOLID · security-first
  wp-content/mu-plugins/harness-hardening.php
docs/database/  docs/diagrams/            # optional mirrors
AGENTS.md  CLAUDE.md  README.md
```

**Native-first:** core WordPress APIs first; custom only after propose + human OK (`.ai/rules/native-first.md`).

When a plugin is justified, architecture lives **inside** it (`src/Contracts|Repositories|Services|Controllers|Security|Providers|Core`) — not as sibling “apps”.

## Quick start (WSL + DDEV)

```bash
ddev start
ddev wp core download --force   # if needed
ddev wp plugin activate harness-plugin
curl -sk https://harness-wp.ddev.site/wp-json/harness/v1/health
ddev launch
```

Admin (change immediately if still default): see prior install / `ddev wp user list`.

## Board + worktrees

```bash
node .linear/scripts/board.mjs epics
node .ai/scripts/epic-new.mjs 1 my-feature --title "My first feature"
node .ai/scripts/worktree-add.mjs APP-1-1   # after claim
node .linear/scripts/api.mjs && npm run ui --prefix .linear/ui
# http://127.0.0.1:47382
```

## Security baseline

XSS · SQLi · CSRF · privilege checks · no PHP in uploads · `DISALLOW_FILE_EDIT` · XML-RPC off · MU hardening — see `AGENTS.md`.

Read **[AGENTS.md](./AGENTS.md)** and **[CLAUDE.md](./CLAUDE.md)** before writing code.
