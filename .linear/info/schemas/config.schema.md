# Config schema (private.board)

Canonical file: `config.yaml` at the `.linear` root.

| Key | Type | Description |
|---|---|---|
| `version` | number | Schema version (1) |
| `team_key` | string | Issue id prefix (`APP-1-1`) |
| `harness` | string | Module under `harness/<name>.mjs` |
| `board.columns[]` | list | `{ id, folder, order }` |
| `roles[]` | string[] | Allowed issue roles |
| `heartbeat_ttl_ms` | number | Claim stale after this many ms |
| `time.idle_ms` | number | Active clock: gap longer than this between board pulses is not counted (approval wait / away). Default 180000 (3 min). |
| `progress.done_column` | string | Final column for 100% column weight (recommended: `summarize`). Epic % averages each issue’s `(checklist_done + column.order) / (checklist_total + maxOrder)`. |
| `progress.unlock_column` | string | Column id (and later) that unblocks `blocked_by` (recommended: `done`). Deps may be in another epic. |
| `claims_file` | string | Path relative to `.linear` root |

Epic `meta.yaml`: `id`, `slug`, `title`, `color`, `status`, `created_at`, `updated_at`.

Issue frontmatter: `id`, `title`, `role`, `epic`, `estimate?`, `blocked_by[]`, `blocks[]`, `agent`, `claimed_at`, optional harness flags + `summary_html`.
