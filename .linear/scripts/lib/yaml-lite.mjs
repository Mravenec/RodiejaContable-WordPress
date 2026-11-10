/**
 * Minimal YAML subset for config.yaml / meta.yaml / issue frontmatter.
 * Supports: scalars, quoted strings, null/true/false/numbers,
 * nested maps (2–3 levels), and lists of scalars or maps.
 */
export function parseYaml(text) {
  const lines = String(text).replace(/\r\n/g, "\n").split("\n");
  const root = {};
  const stack = [{ indent: -1, obj: root, key: null, isList: false }];

  function current() {
    return stack[stack.length - 1];
  }

  function parseScalar(raw) {
    const s = raw.trim();
    if (s === "" || s === "null" || s === "~") return null;
    if (s === "true") return true;
    if (s === "false") return false;
    if (
      (s.startsWith('"') && s.endsWith('"')) ||
      (s.startsWith("'") && s.endsWith("'"))
    ) {
      return s.slice(1, -1);
    }
    if (/^-?\d+(\.\d+)?$/.test(s)) return Number(s);
    // inline empty list
    if (s === "[]") return [];
    if (s === "{}") return {};
    return s;
  }

  for (let i = 0; i < lines.length; i++) {
    const raw = lines[i];
    if (!raw.trim() || raw.trim().startsWith("#")) continue;

    const indent = raw.match(/^ */)[0].length;
    const trimmed = raw.trim();

    while (stack.length > 1 && indent <= current().indent) {
      stack.pop();
    }

    const ctx = current();

    if (trimmed.startsWith("- ")) {
      const rest = trimmed.slice(2);
      if (!Array.isArray(ctx.obj[ctx.key])) {
        ctx.obj[ctx.key] = [];
      }
      const list = ctx.obj[ctx.key];
      if (rest.includes(":") && !rest.startsWith('"') && !rest.startsWith("'")) {
        const colon = rest.indexOf(":");
        const k = rest.slice(0, colon).trim();
        const vRaw = rest.slice(colon + 1).trim();
        const item = {};
        if (vRaw === "") {
          list.push(item);
          stack.push({ indent, obj: item, key: null, isList: false });
        } else {
          item[k] = parseScalar(vRaw);
          list.push(item);
          // peek next lines for more keys of this list item
          stack.push({ indent, obj: item, key: null, isList: false, listItem: true });
        }
      } else {
        list.push(parseScalar(rest));
      }
      continue;
    }

    // continuation of list-item map keys (same indent as list item content = indent+2 from "- ")
    if (ctx.listItem && indent > ctx.indent) {
      const colon = trimmed.indexOf(":");
      if (colon === -1) continue;
      const k = trimmed.slice(0, colon).trim();
      const vRaw = trimmed.slice(colon + 1).trim();
      if (vRaw === "") {
        ctx.obj[k] = {};
        stack.push({ indent, obj: ctx.obj[k], key: null, isList: false });
      } else {
        ctx.obj[k] = parseScalar(vRaw);
      }
      continue;
    }

    const colon = trimmed.indexOf(":");
    if (colon === -1) continue;
    const key = trimmed.slice(0, colon).trim();
    const vRaw = trimmed.slice(colon + 1).trim();

    if (vRaw === "") {
      // look ahead: list or map?
      const next = lines.slice(i + 1).find((l) => l.trim() && !l.trim().startsWith("#"));
      const nextTrim = next?.trim() ?? "";
      if (nextTrim.startsWith("- ")) {
        ctx.obj[key] = [];
        stack.push({ indent, obj: ctx.obj, key, isList: true });
      } else {
        ctx.obj[key] = {};
        stack.push({ indent, obj: ctx.obj[key], key: null, isList: false });
      }
    } else {
      ctx.obj[key] = parseScalar(vRaw);
    }
  }

  return root;
}

export function stringifyYaml(obj, indent = 0) {
  const pad = "  ".repeat(indent);
  const lines = [];
  for (const [k, v] of Object.entries(obj)) {
    if (v === null || v === undefined) {
      lines.push(`${pad}${k}: null`);
    } else if (typeof v === "boolean" || typeof v === "number") {
      lines.push(`${pad}${k}: ${v}`);
    } else if (typeof v === "string") {
      const needsQuote = /[:#\n]|^\s|\s$/.test(v) || v === "";
      lines.push(`${pad}${k}: ${needsQuote ? JSON.stringify(v) : v}`);
    } else if (Array.isArray(v)) {
      if (v.length === 0) {
        lines.push(`${pad}${k}: []`);
      } else if (v.every((x) => typeof x !== "object" || x === null)) {
        lines.push(`${pad}${k}:`);
        for (const item of v) {
          if (item === null) lines.push(`${pad}  - null`);
          else if (typeof item === "string") {
            const q = /[:#\n]/.test(item) ? JSON.stringify(item) : item;
            lines.push(`${pad}  - ${q}`);
          } else lines.push(`${pad}  - ${item}`);
        }
      } else {
        lines.push(`${pad}${k}:`);
        for (const item of v) {
          const entries = Object.entries(item);
          entries.forEach(([ik, iv], idx) => {
            const prefix = idx === 0 ? `${pad}  - ` : `${pad}    `;
            if (iv === null) lines.push(`${prefix}${ik}: null`);
            else if (typeof iv === "object") {
              lines.push(`${prefix}${ik}:`);
              lines.push(stringifyYaml(iv, indent + (idx === 0 ? 3 : 3)));
            } else if (typeof iv === "string") {
              const q = /[:#\n]/.test(iv) ? JSON.stringify(iv) : iv;
              lines.push(`${prefix}${ik}: ${q}`);
            } else lines.push(`${prefix}${ik}: ${iv}`);
          });
        }
      }
    } else if (typeof v === "object") {
      lines.push(`${pad}${k}:`);
      lines.push(stringifyYaml(v, indent + 1));
    }
  }
  return lines.filter(Boolean).join("\n");
}
