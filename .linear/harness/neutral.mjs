/**
 * Neutral harness — checklist gates only. No stack assumptions.
 * summaryProfile() drives beginner HTML in the `summarize` column.
 *
 * Column contract:
 *   doing     — implement (do not run the testing section here)
 *   testing   — verify (harness tests, flags)
 *   done      — park finished tickets until the whole epic is done
 *   summarize — only after every ticket in the epic is in done; then HTML
 */

export async function canMoveToTesting(ctx) {
  if (ctx.force) return { ok: true };
  if (ctx.column !== "doing" && ctx.column !== "testing") {
    return {
      ok: false,
      reason: `Move to doing first, then testing (now in ${ctx.column}). Use --force to skip.`,
    };
  }
  // Do not require a complete checklist. Verification items belong in testing.
  return { ok: true };
}

export async function canMoveToDone(ctx) {
  if (ctx.force) return { ok: true };
  if (ctx.column !== "testing" && ctx.column !== "done") {
    return {
      ok: false,
      reason: `Move to testing first and finish verification there (now in ${ctx.column}). Use --force to skip.`,
    };
  }
  const { checklistSummary } = await import("../scripts/lib/checklist.mjs");
  const { total, pending } = checklistSummary(ctx.body);
  if (total > 0 && pending > 0) {
    return {
      ok: false,
      reason: `Checklist incomplete (${total - pending}/${total}). Finish verification in testing before done.`,
    };
  }
  return { ok: true };
}

export async function canMoveToSummarize(ctx) {
  if (ctx.force) return { ok: true };
  if (ctx.column !== "done" && ctx.column !== "summarize") {
    return {
      ok: false,
      reason: `Park the ticket in done first (now in ${ctx.column}). Summarize only after the whole epic is in done.`,
    };
  }
  const open = (ctx.issues || []).filter(
    (i) => i.column !== "done" && i.column !== "summarize"
  );
  if (open.length) {
    const ids = open.map((i) => i.meta?.id || "?").join(", ");
    return {
      ok: false,
      reason: `Summarize waits until every ticket in this epic is in done. Still open: ${ids}`,
    };
  }
  return { ok: true };
}

export function testingHints(role) {
  return [
    `role:${role}`,
    "doing = implement only; then move to testing",
    "testing = run verification and set flags; then move to done",
    "Leave tickets in done until every epic ticket is done, then summarize (HTML)",
  ];
}

export function summaryProfile() {
  return {
    id: "neutral",
    label: "Neutral private.board",
    tagline: "Filesystem board with roles, checklist, and gates — no fixed product stack.",
    forWhom:
      "You are new to private.board. This page explains one finished ticket in plain English: what the goal was, which checklist steps existed, and how the board columns work. No product brand is assumed.",
    howToRead: [
      "Read Basic concepts once.",
      "Walk the checklist as numbered steps — that is the real work contract.",
      "Open What was built: those windows are this ticket’s code, commands, and results.",
      "Use How you can verify to confirm without reading source first.",
    ],
    concepts: [
      {
        term: "private.board",
        def: "A board made of folders and Markdown files. Agents never need a cloud API to know what is next.",
      },
      {
        term: "Epic",
        def: "A folder of related issues. Progress % rises with each column move (weighted); 100% when all issues are in summarize.",
      },
      {
        term: "Issue",
        def: "One Markdown ticket that moves: backlog → doing → testing → done → summarize.",
      },
      {
        term: "Doing vs testing",
        def: "Doing is implementation. Testing is where you run verification. Do not run the testing section while the ticket is still in doing.",
      },
      {
        term: "Done parking",
        def: "Finished tickets stay in done until every ticket in the epic is in done. The living HTML already grew with the work. Then move them to summarize and record git + Supervisor / Manager with handoff.",
      },
      {
        term: "Checklist",
        def: "Numbered tasks inside the issue. Mark exactly one pending item at a time.",
      },
      {
        term: "Harness",
        def: "A small policy module that adds extra rules before testing/done (this harness is neutral: checklist only).",
      },
      {
        term: "Summarize",
        def: "Last wrap-up column. The living HTML grew from the first claim/move. This column is for git command + Supervisor / Manager (handoff) after all tickets are in done.",
      },
    ],
    layers: [],
    boardFlow: [
      "backlog — ready to claim",
      "doing — implementation only (no verification section yet)",
      "testing — local verification and harness flags",
      "done — work accepted; wait here until the whole epic is done",
      "summarize — wrap-up: living HTML already grew; record git + Supervisor / Manager (you are here)",
    ],
    roleExplain: {
      orchestrator:
        "Creates epics/issues and keeps the board healthy. Does not implement product features.",
      database: "Owns data/schema work defined by the project that uses this board.",
      backend: "Owns server/API work defined by the project that uses this board.",
      frontend: "Owns UI work; usually waits for upstream API proof when the project defines a gate.",
    },
    faq: [
      {
        q: "Why move to testing before running tests?",
        a: "Doing is for building. Testing is the column that means you are proving the work. Mixing them hides whether verification actually happened.",
      },
      {
        q: "Why stay in done instead of summarizing each ticket immediately?",
        a: "Summarize is the wrap-up column. The beginner HTML is living: it grows from the first claim/move (all tickets, including testing → doing). Tickets wait in done until every sibling is done, then you move them to summarize and record git + Supervisor / Manager with handoff.",
      },
      {
        q: "Does summarize block other tickets?",
        a: "By default, dependents unlock when a ticket reaches done (or later). Epic % is column + checklist and reaches 100% when every ticket is in summarize with its checklist complete.",
      },
      {
        q: "Can I regenerate the HTML?",
        a: "Yes: node scripts/board.mjs summarize-epic <EPIC_N>. Wrap-up git + Supervisor / Manager: node scripts/board.mjs handoff <EPIC_N> --git \"…\" --note-file …",
      },
    ],
    verifyHints: {
      default: [
        "Find the issue Markdown under epics/<n>-<slug>/board/.",
        "Confirm checklist items match the steps on this page.",
        "Open What was built — expect compact code/command/result windows for this ticket.",
        "Open the living summary (board UI Open living summary, or summaries/<n>-<slug>-beginner.html) and skim evidence, bounce-backs, git command, and Supervisor / Manager note.",
      ],
    },
    evidenceWindows: {
      default: [
        "What changed (compact snippet + path)",
        "Command that ran",
        "Result (pass, fail, status, or output)",
      ],
    },
  };
}
