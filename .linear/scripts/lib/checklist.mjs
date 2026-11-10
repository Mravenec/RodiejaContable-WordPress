const CHECKLIST_RE = /^(\s*)- \[([ xX])\](.*)$/gm;

export function parseChecklist(description) {
  if (!description) return [];
  const items = [];
  let index = 0;
  for (const m of description.matchAll(CHECKLIST_RE)) {
    index++;
    items.push({
      index,
      done: m[2].toLowerCase() === "x",
      text: m[3].trim(),
      line: m[0],
    });
  }
  return items;
}

export function checklistSummary(description) {
  const items = parseChecklist(description);
  const done = items.filter((i) => i.done).length;
  return { items, total: items.length, done, pending: items.length - done };
}

export function markChecklistInDescription(description, indices) {
  if (!description) return description;
  let index = 0;
  return description.replace(CHECKLIST_RE, (match, indent, _check, text) => {
    index++;
    if (indices.includes(index)) return `${indent}- [x]${text}`;
    return match;
  });
}

export function parseChecklistSpec(spec) {
  if (!spec || String(spec).toLowerCase() === "all") {
    throw new Error(
      "Batch marking forbidden. Use a single item: checklist <id> 1"
    );
  }
  const nums = String(spec)
    .split(",")
    .map((s) => Number(s.trim()))
    .filter((n) => n > 0);
  if (!nums.length) {
    throw new Error(`Invalid checklist spec: "${spec}"`);
  }
  if (nums.length > 1) {
    throw new Error(
      `Batch marking forbidden (${nums.join(",")}). One item per command.`
    );
  }
  return nums;
}

export function validateSequentialChecklistMark(description, spec) {
  const nums = parseChecklistSpec(spec);
  const n = nums[0];
  const { items } = checklistSummary(description);
  if (items.length === 0) return { ok: true };

  const target = items.find((i) => i.index === n);
  if (!target) return { ok: false, error: `Item ${n} does not exist in the checklist.` };
  if (target.done) return { ok: true, noop: true };

  const firstPending = items.find((i) => !i.done);
  if (!firstPending) return { ok: false, error: "Checklist already complete." };
  if (n !== firstPending.index) {
    return {
      ok: false,
      error: `Out of order: mark item ${firstPending.index} before ${n}.`,
    };
  }
  return { ok: true };
}
