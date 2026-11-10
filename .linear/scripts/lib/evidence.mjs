/**
 * Ticket ## Evidence — compact code/command/result blocks for the living HTML.
 * Spec: host `.ai/specs/living-summary.md` (when the board is vendored in the harness).
 */

export const MAX_EVIDENCE_LINES = 48;

export function compactSnippet(text, max = MAX_EVIDENCE_LINES) {
  const lines = String(text ?? "")
    .replace(/\r\n/g, "\n")
    .split("\n");
  while (lines.length && lines[lines.length - 1] === "") lines.pop();
  if (lines.length <= max) return lines.join("\n");
  const extra = lines.length - max;
  return [
    ...lines.slice(0, max),
    `/* … truncated ${extra} more line${extra === 1 ? "" : "s"} — open the file */`,
  ].join("\n");
}

export function extractSection(body, heading) {
  const re = new RegExp(
    `##\\s*${heading}\\s*\\n([\\s\\S]*?)(?=\\n##\\s+|\\s*$)`,
    "i"
  );
  const m = String(body || "").match(re);
  return (m?.[1] || "").trim();
}

export function extractEvidence(body) {
  return extractSection(body, "Evidence");
}

export function hasEvidenceFences(body) {
  return /```/.test(extractEvidence(body));
}

export function fenceCount(body) {
  const ev = extractEvidence(body);
  const m = ev.match(/```/g);
  return m ? Math.floor(m.length / 2) : 0;
}

/**
 * One evidence entry (title + optional path, why, code, command, result).
 */
export function formatEvidenceEntry(opts) {
  const title = String(opts.title || "Change").trim();
  const pathLabel = String(opts.path || "").trim();
  const lang = String(opts.lang || "text").trim() || "text";
  const why = String(opts.why || "").trim();
  const code = opts.code != null && String(opts.code).trim() !== ""
    ? compactSnippet(opts.code)
    : "";
  const command = String(opts.command || "").trim();
  const result =
    opts.result != null && String(opts.result).trim() !== ""
      ? compactSnippet(opts.result)
      : "";
  const parts = [`### ${title}`, ""];
  if (why) parts.push(why, "");
  if (pathLabel) parts.push(`Path: \`${pathLabel}\``, "");
  if (code) parts.push("```" + lang, code, "```", "");
  if (command) parts.push("Ran:", "", "```bash", command, "```", "");
  if (result) parts.push("Result:", "", "```text", result, "```", "");
  return parts.join("\n").trimEnd() + "\n\n";
}

/**
 * Insert or append under ## Evidence, always before ## History when present.
 */
export function insertEvidence(body, entryMarkdown) {
  const raw = String(body || "");
  const entry = String(entryMarkdown || "").trimEnd() + "\n\n";
  const histRe = /^##\s*History\s*$/m;
  const evRe = /^##\s*Evidence\s*$/im;
  if (evRe.test(raw)) {
    const hist = raw.search(histRe);
    if (hist >= 0) return raw.slice(0, hist) + entry + raw.slice(hist);
    return raw.trimEnd() + "\n\n" + entry;
  }
  const section =
    "## Evidence\n\nLayer artifacts for the living HTML (code, commands, results). Compact windows — not the whole file.\n\n" +
    entry;
  const hist = raw.search(histRe);
  if (hist >= 0) return raw.slice(0, hist) + section + raw.slice(hist);
  return raw.trimEnd() + "\n\n" + section;
}
