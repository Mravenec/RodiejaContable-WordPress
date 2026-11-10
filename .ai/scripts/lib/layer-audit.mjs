/**
 * Layer audit — WordPress native plugin tree.
 * Spec: .ai/specs/wordpress-native-layers.md
 *
 * Checks under 1.plugin/<module_name>/:
 *   forbidden-package       composer.json requires Laravel/Illuminate/Doctrine/etc.
 *   service-without-repository   XxxService does not reference an *Repository / Interface (unless allowlisted)
 *   repository-without-wpdb      XxxRepository does not mention $wpdb / wpdb
 *   concrete-repository-injection  constructor typed with concrete *Repository instead of *Interface
 *   rest-public-mutation    register_rest_route CREATABLE/DELETABLE/EDITABLE with __return_true
 *   missing-abspath-guard   bootstrap/uninstall PHP missing ABSPATH / WP_UNINSTALL_PLUGIN guard
 *
 * Day zero without module folder → ok: true.
 */
import fs from "fs";
import path from "path";

const FORBIDDEN = [
  /illuminate\//i,
  /laravel\//i,
  /doctrine\//i,
  /symfony\/framework-bundle/i,
  /"eloquent"/i,
];

/**
 * @param {string} root
 * @returns {{ moduleName: string|null, servicesWithoutRepository: string[] }}
 */
export function readProject(root) {
  const file = path.join(root, ".ai", "project.yaml");
  const out = { moduleName: null, servicesWithoutRepository: ["HealthService"] };
  if (!fs.existsSync(file)) return out;
  const lines = fs.readFileSync(file, "utf8").split(/\r?\n/);
  let list = null;
  for (const raw of lines) {
    const line = raw.replace(/\s+#.*$/, "");
    if (/^\s*#/.test(raw) || !line.trim()) continue;
    const key = line.match(/^([A-Za-z_]+):\s*(.*)$/);
    if (key) {
      list = null;
      const value = key[2].trim();
      if (key[1] === "module_name") {
        out.moduleName = !value || value === "null" || value === "~" ? null : value.replace(/^["']|["']$/g, "");
      } else if (key[1] === "services_without_repository") {
        if (value.startsWith("[")) {
          out.servicesWithoutRepository = value
            .replace(/^\[|\]$/g, "")
            .split(",")
            .map((s) => s.trim().replace(/^["']|["']$/g, ""))
            .filter(Boolean);
        } else {
          list = out.servicesWithoutRepository = [];
        }
      }
      continue;
    }
    const item = line.match(/^\s+-\s+(.+)$/);
    if (item && list) {
      list.push(item[1].trim().replace(/^["']|["']$/g, ""));
    }
  }
  return out;
}

function walk(dir, out = []) {
  if (!fs.existsSync(dir)) return out;
  for (const name of fs.readdirSync(dir)) {
    if (name === "vendor" || name === "node_modules" || name === ".git") continue;
    const p = path.join(dir, name);
    const st = fs.statSync(p);
    if (st.isDirectory()) walk(p, out);
    else out.push(p);
  }
  return out;
}

/**
 * @param {string} root
 * @returns {{ ok: boolean, violations: Array<{code:string,file:string,detail:string}>, skipped?: string }}
 */
export function runLayerAudit(root) {
  const { moduleName, servicesWithoutRepository } = readProject(root);
  const violations = [];
  const add = (code, file, detail) =>
    violations.push({ code, file: path.relative(root, file).replace(/\\/g, "/"), detail });

  if (!moduleName) {
    return { ok: true, violations: [], skipped: "module_name is null (day zero)" };
  }

  const pluginRoot = path.join(root, "1.plugin", moduleName);
  if (!fs.existsSync(pluginRoot)) {
    return { ok: true, violations: [], skipped: `missing 1.plugin/${moduleName}` };
  }

  const composer = path.join(pluginRoot, "composer.json");
  if (fs.existsSync(composer)) {
    const raw = fs.readFileSync(composer, "utf8");
    for (const re of FORBIDDEN) {
      if (re.test(raw)) add("forbidden-package", composer, `Forbidden dependency pattern: ${re}`);
    }
  }

  const allow = new Set(servicesWithoutRepository);
  const servicesDir = path.join(pluginRoot, "src", "Services");
  for (const file of walk(servicesDir).filter((f) => f.endsWith(".php"))) {
    const name = path.basename(file, ".php");
    if (allow.has(name)) continue;
    const code = fs.readFileSync(file, "utf8");
    if (!/Repository/.test(code)) {
      add(
        "service-without-repository",
        file,
        `${name} does not reference a Repository/Interface (add to services_without_repository with a reason if intentional)`
      );
    }
  }

  const reposDir = path.join(pluginRoot, "src", "Repositories");
  for (const file of walk(reposDir).filter((f) => f.endsWith(".php"))) {
    const name = path.basename(file, ".php");
    const code = fs.readFileSync(file, "utf8");
    if (!/\$wpdb|\bwpdb\b|\\\\wpdb/.test(code)) {
      add("repository-without-wpdb", file, `${name} does not use $wpdb / wpdb`);
    }
  }

  for (const file of walk(path.join(pluginRoot, "src")).filter((f) => f.endsWith(".php"))) {
    const code = fs.readFileSync(file, "utf8");
    if (
      /function\s+__construct\s*\([^)]*\b([A-Z][A-Za-z0-9_]*Repository)\b\s+\$/.test(code) &&
      !/Interface\b/.test(RegExp.lastMatch || "")
    ) {
      const m = code.match(
        /function\s+__construct\s*\([^)]*\b([A-Z][A-Za-z0-9_]*Repository)\b\s+\$/
      );
      if (m && !m[1].endsWith("Interface")) {
        add(
          "concrete-repository-injection",
          file,
          `Constructor injects concrete ${m[1]}; prefer ${m[1]}Interface (DIP)`
        );
      }
    }
  }

  for (const file of walk(path.join(pluginRoot, "src", "Controllers")).filter((f) =>
    f.endsWith(".php")
  )) {
    const code = fs.readFileSync(file, "utf8");
    const mutating =
      /WP_REST_Server::(?:CREATABLE|EDITABLE|DELETABLE)/.test(code) ||
      /['"](?:POST|PUT|PATCH|DELETE)['"]/.test(code);
    if (mutating && /permission_callback['"]?\s*=>\s*['"]__return_true['"]/.test(code)) {
      add(
        "rest-public-mutation",
        file,
        "Mutating REST route uses permission_callback __return_true"
      );
    }
    if (mutating && /permission_callback['"]?\s*=>\s*['"]__return_true['"]/.test(code.replace(/\s+/g, " "))) {
      // already added
    } else if (
      mutating &&
      /'permission_callback'\s*=>\s*'__return_true'|"permission_callback"\s*=>\s*"__return_true"|permission_callback'\s*=>\s*__return_true/.test(
        code
      )
    ) {
      add(
        "rest-public-mutation",
        file,
        "Mutating REST route uses permission_callback __return_true"
      );
    }
  }

  // Broader scan for __return_true next to CREATABLE blocks
  for (const file of walk(path.join(pluginRoot, "src", "Controllers")).filter((f) =>
    f.endsWith(".php")
  )) {
    const code = fs.readFileSync(file, "utf8");
    const chunks = code.split(/register_rest_route\s*\(/).slice(1);
    for (const chunk of chunks) {
      const head = chunk.slice(0, 1200);
      const isMut =
        /WP_REST_Server::(?:CREATABLE|EDITABLE|DELETABLE)/.test(head) ||
        /['"]methods['"]\s*=>\s*['"](?:POST|PUT|PATCH|DELETE)/.test(head);
      const isPublic = /permission_callback['"]?\s*=>\s*(?:['"]__return_true['"]|__return_true)/.test(
        head
      );
      if (isMut && isPublic) {
        add(
          "rest-public-mutation",
          file,
          "Mutating register_rest_route uses __return_true"
        );
      }
    }
  }

  const boot = path.join(pluginRoot, `${moduleName}.php`);
  if (fs.existsSync(boot)) {
    const code = fs.readFileSync(boot, "utf8");
    if (!/defined\s*\(\s*['"]ABSPATH['"]\s*\)/.test(code)) {
      add("missing-abspath-guard", boot, "Main plugin file must guard ABSPATH");
    }
  }
  const uninstall = path.join(pluginRoot, "uninstall.php");
  if (fs.existsSync(uninstall)) {
    const code = fs.readFileSync(uninstall, "utf8");
    if (!/WP_UNINSTALL_PLUGIN/.test(code)) {
      add("missing-abspath-guard", uninstall, "uninstall.php must guard WP_UNINSTALL_PLUGIN");
    }
  }

  // Dedupe
  const seen = new Set();
  const uniq = [];
  for (const v of violations) {
    const k = `${v.code}|${v.file}|${v.detail}`;
    if (seen.has(k)) continue;
    seen.add(k);
    uniq.push(v);
  }

  return { ok: uniq.length === 0, violations: uniq };
}

export function formatAudit(audit) {
  const lines = (audit.violations || []).map(
    (v) => `  - [${v.code}] ${v.file}: ${v.detail}`
  );
  const more = lines.length ? "" : "  (none)";
  return `Layer audit failed (${audit.violations.length} violation(s)) — .ai/specs/wordpress-native-layers.md:\n${lines.join("\n")}${more}`;
}
