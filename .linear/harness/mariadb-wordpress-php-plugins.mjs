/**
 * Harness: unified WordPress project (DDEV + MariaDB + custom plugins)
 *
 * Layout is a real WP tree under `web/` — not Spring-style 0/1/2 app segments.
 * Architecture (DI/SOLID) lives *inside* `web/wp-content/plugins/<module>/`.
 *
 * Roles: database · plugin · security · orchestrator
 * Flags: tests_ok · http_ok
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
          "mariadb-wordpress-php-plugins: plugin requires tests_ok and/or http_ok after REST/admin smoke, or --force.",
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
      "Schema runtime = plugin dbDelta (Repositories)",
      "Optional mirror: docs/database/canonical_sql/ + docs/diagrams/database/schema.dbml",
      "ddev wp db query / DESCRIBE to verify",
      "Set tests_ok: true",
    ],
    plugin: [
      "NATIVE-FIRST: CPT/meta/Options/Settings/WP_Query/core REST before custom tables",
      "If custom seems required: propose native gap + plan → human OK on ticket → then code",
      "Work in web/wp-content/plugins/<module_name>/ when a plugin is justified",
      "Inside plugin: Contracts → Repositories → Services → Controllers + Security + DI",
      "Mutating REST must use real permission_callback (never __return_true)",
      "curl / ddev wp REST smoke → http_ok: true",
      "Optional PHPUnit / WPCS → tests_ok: true",
    ],
    security: [
      "Confirm caps + nonces on admin/AJAX state changes",
      "Confirm MU-plugin web/wp-content/mu-plugins/harness-hardening.php",
      "Anonymous mutating REST must 401/403",
      "Set tests_ok: true",
    ],
    orchestrator: [
      "Create journey + epic + issues with blocked_by chain",
      "After every claim: node .ai/scripts/worktree-add.mjs <ID>",
      "Park tickets in done until the whole epic is done, then summarize + handoff",
    ],
  };
  return map[role] || ["Implement in doing, verify in testing, park in done, summarize when every ticket is done"];
}

export function summaryProfile() {
  return {
    id: "mariadb-wordpress-php-plugins",
    label: "MariaDB · WordPress (unified) · Custom Plugins",
    tagline: "Realistic WordPress project + private.board — architecture inside the plugin.",
    forWhom:
      "This is one WordPress site (web/), not a multi-app monorepo. DI/SOLID live inside the custom plugin under wp-content/plugins.",
    howToRead: [
      "Read Basic concepts (unified WP tree + plugin layers + security).",
      "Open What was built: SQL/dbDelta, repository, service, REST/admin, security checks.",
      "Walk the checklist as numbered steps.",
    ],
    concepts: [
      {
        term: "Unified WordPress tree",
        def: "Like a real WP/DDEV project: web/ is the docroot. Custom code lives in wp-content/plugins and mu-plugins — not in numbered 0/1/2 app folders.",
      },
      {
        term: "Plugin architecture",
        def: "Inside the plugin only: Contracts → Repositories ($wpdb) → Services → Controllers with constructor DI.",
      },
      {
        term: "Native only",
        def: "hooks/filters, REST API, $wpdb/dbDelta, capabilities, nonces, sanitize/escape, WP-CLI, Composer PSR-4.",
      },
      {
        term: "GATE_HTTP",
        def: "REST/admin smoke proves the contract before dependents proceed.",
      },
      {
        term: "Worktrees",
        def: "After every claim, isolate with node .ai/scripts/worktree-add.mjs <ISSUE_ID>.",
      },
    ],
    layers: [
      { path: "web/", why: "WordPress docroot (core mostly gitignored)." },
      {
        path: "web/wp-content/plugins/<module_name>/",
        why: "Custom plugin (OOP DI SOLID). Role: plugin.",
      },
      {
        path: "web/wp-content/mu-plugins/",
        why: "Always-on hardening. Role: security.",
      },
      {
        path: "docs/database/ · docs/diagrams/",
        why: "Optional SQL/DBML/PlantUML twins — documentation, not separate apps.",
      },
      { path: ".linear/", why: "Private board + worktree helpers." },
      { path: ".docs/journey/", why: "Epic narrative." },
    ],
    layersNote:
      "Do not recreate Spring-style 0.database/1.backend/2.frontend. Keep one WP project; put architecture inside the plugin.",
    boardFlow: [
      "backlog — waiting",
      "doing — implement",
      "testing — verify + flags",
      "done — park until epic complete",
      "summarize — handoff wrap-up",
    ],
    roleExplain: {
      orchestrator: "Board/journey/worktree hygiene only.",
      database: "Schema via plugin dbDelta + optional docs/database mirror.",
      plugin: "Owns web/wp-content/plugins/<module>/.",
      security: "Owns mu-plugin + Security/ classes + REST permission proofs.",
    },
    verifyHints: {
      database: [
        "dbDelta / DESCRIBE proves tables.",
        "Optional: docs/database/canonical_sql in sync.",
      ],
      plugin: [
        "Contracts → Repository → Service → Controller order inside the plugin.",
        "health 200; protected routes 401 anonymous; authenticated 2xx.",
      ],
      security: [
        "Anonymous POST protected routes → 401/403.",
        "DISALLOW_FILE_EDIT / XML-RPC off still hold.",
      ],
      orchestrator: [
        "worktree-add after each claim.",
        "Summarize only after every epic ticket is done.",
      ],
      default: ["Compare checklist to this page; confirm flags."],
    },
    verifyCommands: {
      database: ['ddev wp db query "SHOW TABLES LIKE \'%harness%\'"'],
      plugin: [
        "ddev wp plugin activate harness-plugin",
        "curl -sk https://harness-wp.ddev.site/wp-json/harness/v1/health",
        "ddev composer install",
      ],
      security: [
        "curl -sk -o /dev/null -w '%{http_code}' -X POST https://harness-wp.ddev.site/wp-json/harness/v1/items",
      ],
    },
    evidenceWindows: {
      database: ["dbDelta / SQL mirror", "DESCRIBE result", "DBML twin"],
      plugin: [
        "Contract",
        "Repository ($wpdb)",
        "Service",
        "REST/Admin controller",
        "DI provider",
        "HTTP result",
      ],
      security: ["CapabilityGate / permission_callback", "Nonce", "Sanitize/escape", "401/403 proof"],
      orchestrator: ["Journey or board snippet"],
    },
    faq: [
      {
        q: "Why no 0.database / 1.plugin folders?",
        a: "Those mimic a multi-app Spring harness. A real WordPress site is one docroot with plugins inside wp-content.",
      },
      {
        q: "Where does DI/SOLID live?",
        a: "Inside the custom plugin under web/wp-content/plugins/<module>/src/.",
      },
      {
        q: "Is Composer allowed?",
        a: "Yes for PSR-4 and WPCS/PHPUnit — not for Laravel/Doctrine app frameworks.",
      },
    ],
  };
}
