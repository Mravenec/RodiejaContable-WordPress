/**
 * Harness: MariaDB → WordPress → PHP Custom Plugins (native only)
 *
 * Agents implement in `doing`, then `move testing`, verify locally, set flags,
 * `move done`, and wait. Summarize only after every ticket in the epic is in `done`.
 *
 * Flags:
 *   database → tests_ok
 *   plugin   → tests_ok and/or http_ok  (REST / WP-CLI smoke)
 *   security → tests_ok (caps/nonces/sanitize gates still hold)
 *   orchestrator → board hygiene only
 *
 * Layer order:
 *   SQL/dbDelta → Contracts → Repositories ($wpdb) → Services → Controllers (REST/Admin)
 *   → Security on every entrypoint → ddev wp smoke / curl REST
 */

import {
  canMoveToTesting as neutralTesting,
  canMoveToDone as neutralDone,
  canMoveToSummarize as neutralSummarize,
} from "./neutral.mjs";

export async function canMoveToTesting(ctx) {
  return neutralTesting(ctx);
}

export async function canMoveToDone(ctx) {
  const base = await neutralDone(ctx);
  if (!base.ok) return base;
  if (ctx.force) return { ok: true };

  const role = ctx.meta?.role;

  if (role === "database") {
    if (ctx.meta?.tests_ok !== true) {
      return {
        ok: false,
        reason:
          "mariadb-wordpress-php-plugins: database role requires tests_ok: true after schema/dbDelta verify, or --force.",
      };
    }
  }

  if (role === "plugin") {
    if (ctx.meta?.tests_ok !== true && ctx.meta?.http_ok !== true) {
      return {
        ok: false,
        reason:
          "mariadb-wordpress-php-plugins: plugin requires tests_ok and/or http_ok after layers→REST/admin smoke, or --force.",
      };
    }
  }

  if (role === "security") {
    if (ctx.meta?.tests_ok !== true) {
      return {
        ok: false,
        reason:
          "mariadb-wordpress-php-plugins: security role requires tests_ok: true after hardening verify, or --force.",
      };
    }
  }

  return { ok: true };
}

export async function canMoveToSummarize(ctx) {
  return neutralSummarize(ctx);
}

export function testingHints(role) {
  const map = {
    database: [
      "Update 0.database/canonical_sql/ (one file per DB ticket) + SOURCE in 00_run_all.sql",
      "Keep runtime schema in plugin dbDelta (ItemRepository::install_schema or equivalent)",
      "ddev wp db query / DESCRIBE to verify",
      "Update 3.diagrams/0.database/dbdiagram/schema.dbml",
      "Set tests_ok: true",
    ],
    plugin: [
      "Native only: hooks, REST, $wpdb, Options, Capabilities, Nonces, sanitize/escape",
      "Order: Contracts → Repositories → Services → Controllers (REST/Admin)",
      "Constructor DI via Core\\Container + Providers\\ServiceProvider",
      "Mutating REST must use real permission_callback (never __return_true)",
      "Prove with curl / ddev wp eval rest_do_request → http_ok: true",
      "Optional PHPUnit / WPCS → tests_ok: true",
      "No Laravel/Illuminate/Doctrine/Eloquent",
    ],
    security: [
      "Confirm caps + nonces on admin/AJAX state changes",
      "Confirm sanitize early / escape late",
      "Confirm $wpdb->prepare / CRUD helpers only",
      "Confirm MU-plugin + nginx deny PHP in uploads still linked",
      "Anonymous mutating REST must 401/403",
      "Set tests_ok: true",
    ],
    orchestrator: [
      "Create journey + epic + issues with blocked_by chain",
      "For large briefs: create EVERY epic + ticket before agents code",
      "Drive next/claim hygiene; no product feature code",
      "After every claim: node .ai/scripts/worktree-add.mjs <ID>",
      "Park tickets in done until the whole epic is done, then summarize + handoff",
    ],
  };
  return map[role] || ["Implement in doing, verify in testing, park in done, summarize the epic only when every ticket is done"];
}

export function summaryProfile() {
  return {
    id: "mariadb-wordpress-php-plugins",
    label: "MariaDB · WordPress · PHP Custom Plugins",
    tagline: "Native WordPress plugin harness with private.board gates.",
    forWhom:
      "You are new to this harness. Words like dbDelta, GATE_HTTP (REST), Contracts, or summarize may be unfamiliar — this page explains them using only this ticket’s work.",
    howToRead: [
      "Read Basic concepts once (MariaDB, WordPress plugin layers, security).",
      "Skim Stack layers so you know which folder each role touches.",
      "Open What was built: SQL, repository, service, REST/admin, security checks.",
      "Walk the checklist as numbered steps — that is the real work contract.",
      "Use How you can verify to confirm without reading the whole tree first.",
    ],
    concepts: [
      {
        term: "MariaDB (0.database)",
        def: "Real database via DDEV. Canonical SQL mirrors plugin dbDelta. Agents do not invent fake data stores.",
      },
      {
        term: "WordPress plugin layers",
        def: "Strict order: Contracts → Repositories ($wpdb prepared) → Services → Controllers (REST/Admin) with Security on every entrypoint.",
      },
      {
        term: "Native only",
        def: "hooks/filters, REST API, $wpdb/dbDelta, capabilities, nonces, sanitize/escape, WP-CLI, Composer PSR-4. No Laravel/Doctrine SPA frameworks.",
      },
      {
        term: "GATE_HTTP",
        def: "REST or admin smoke must prove the contract (2xx authenticated / 401 anonymous on protected routes) before dependents proceed.",
      },
      {
        term: "Worktrees",
        def: "After every claim, isolate code with node .ai/scripts/worktree-add.mjs <ISSUE_ID>. Shared board via .linear junctions/symlinks.",
      },
      {
        term: "Summarize",
        def: "Wrap-up column after every ticket is in done. Living HTML + handoff store git command and Supervisor / Manager note.",
      },
    ],
    layers: [
      {
        path: "0.database/",
        why: "Canonical SQL + DDEV MariaDB. Role: database.",
      },
      {
        path: "0.security/",
        why: "Always-on MU-plugin hardening. Role: security.",
      },
      {
        path: "1.plugin/<module_name>/",
        why: "Custom plugin (OOP DI SOLID). Role: plugin. Name from .ai/project.yaml.",
      },
      {
        path: "web/",
        why: "WordPress core docroot (gitignored). DDEV mounts plugins/mu-plugins.",
      },
      {
        path: ".linear/",
        why: "Private board + worktree helpers. Role: orchestrator for board hygiene.",
      },
      {
        path: ".docs/journey/",
        why: "Human-readable epic story; stays aligned with board outcomes.",
      },
    ],
    layersNote:
      "Never invert: database (if schema changed) → plugin layers → REST/admin smoke → security verify.",
    boardFlow: [
      "backlog — waiting",
      "doing — implement one checklist item at a time (no verification yet)",
      "testing — run role verification and set flags",
      "done — flags set (tests_ok / http_ok); wait until the whole epic is done",
      "summarize — wrap-up: living HTML + handoff",
    ],
    roleExplain: {
      orchestrator:
        "Creates journey/epic/issues and keeps next/claim/worktree hygiene. Does not write product features.",
      database:
        "Owns SQL under 0.database/ and schema.dbml. Sets tests_ok after real verify.",
      plugin:
        "Owns 1.plugin/<module_name>/. Proves REST/admin with http_ok and/or tests_ok.",
      security:
        "Owns hardening checks (0.security + plugin Security/). Sets tests_ok after verify.",
    },
    verifyHints: {
      database: [
        "Confirm SQL merged into 0.database/canonical_sql/.",
        "DESCRIBE / ddev wp db query proves the table.",
        "Issue frontmatter should show tests_ok: true.",
      ],
      plugin: [
        "Confirm Contracts → Repository → Service → Controller order.",
        "Mutating REST must not use __return_true.",
        "curl health 200; protected routes 401 anonymous; authenticated 2xx.",
        "Confirm http_ok / tests_ok when each applied.",
      ],
      security: [
        "Anonymous POST to protected routes returns 401/403.",
        "DISALLOW_FILE_EDIT and XML-RPC off still hold.",
        "Nonces + capabilities present on admin state changes.",
      ],
      orchestrator: [
        "Confirm epic/journey exist and blocked_by matches layer order.",
        "Confirm worktree-add after each claim.",
        "Summarize only after every epic ticket is in done.",
      ],
      default: [
        "Open the issue Markdown and compare checklist text to this page.",
        "Confirm harness flags for the role are present when required.",
      ],
    },
    verifyCommands: {
      database: [
        "ddev wp db query \"SHOW TABLES LIKE '%harness%'\"",
        "ddev mysql < 0.database/canonical_sql/01_harness_items.sql",
      ],
      plugin: [
        "ddev wp plugin activate harness-plugin",
        "curl -sk https://harness-wp.ddev.site/wp-json/harness/v1/health",
        "ddev composer install",
      ],
      security: [
        "curl -sk -o /dev/null -w '%{http_code}' -X POST https://harness-wp.ddev.site/wp-json/harness/v1/items",
        "ddev wp eval 'echo DISALLOW_FILE_EDIT ? 1 : 0;'",
      ],
    },
    evidenceWindows: {
      database: [
        "SQL (canonical_sql file + compact CREATE)",
        "dbDelta / DESCRIBE result (tests_ok)",
        "DBML twin (schema.dbml snippet)",
      ],
      plugin: [
        "Contract interface",
        "Repository ($wpdb prepare)",
        "Service",
        "REST / Admin controller",
        "DI provider wiring",
        "HTTP result (status/body; 401 then 2xx)",
      ],
      security: [
        "CapabilityGate / permission_callback",
        "Nonce verification",
        "Sanitize / escape call sites",
        "Anonymous 401/403 proof",
      ],
      orchestrator: [
        "Journey or board snippet that defined the tickets",
      ],
    },
    faq: [
      {
        q: "Is Composer allowed?",
        a: "Yes for PSR-4 autoload and WPCS/PHPUnit. Not for Laravel/Illuminate/Doctrine app frameworks.",
      },
      {
        q: "Why worktrees?",
        a: "One board ticket = one isolated folder under .worktrees/{n}-{slug}/{ISSUE_ID} so agents do not collide.",
      },
      {
        q: "Why summarize after done?",
        a: "Done parks finished tickets. When the whole epic is done, summarize wraps up; handoff stores git + Supervisor / Manager note.",
      },
      {
        q: "Where is the board of record?",
        a: "Only under .linear/ on disk (UI brand private.board). No cloud issue-tracker API is required.",
      },
    ],
  };
}
