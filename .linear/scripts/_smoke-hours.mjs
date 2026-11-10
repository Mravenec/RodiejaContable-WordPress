/**
 * Smoke: month clipping of active + elapsed totals.
 * Run: node scripts/_smoke-hours.mjs
 */
import {
  clockTotalsForMonth,
  boardHoursForMonth,
  collectMonthKeys,
  currentMonthKey,
} from "./lib/work-clock.mjs";

const doc = {
  idle_ms: 180000,
  started_at: "2026-09-09T04:00:00.000Z",
  finished_at: "2026-09-09T05:00:00.000Z",
  issues: {
    "APP-1-1": {
      active_ms: 600000,
      paused: false,
      open: null,
      segments: [
        {
          start: "2026-09-09T04:00:00.000Z",
          end: "2026-09-09T04:10:00.000Z",
          ms: 600000,
          close: "done",
          agent: "smoke",
        },
      ],
    },
  },
};

let failed = false;

const sep = clockTotalsForMonth(doc, "2026-09");
if (sep.active_ms !== 600000) {
  console.error("FAIL: September active expected 10m", sep);
  failed = true;
} else {
  console.log("OK: September active 10m");
}
if (sep.wall_ms !== 3600000) {
  console.error("FAIL: September elapsed expected 1h", sep);
  failed = true;
} else {
  console.log("OK: September elapsed 1h");
}

const aug = clockTotalsForMonth(doc, "2026-08");
if (aug.active_ms !== 0 || aug.wall_ms !== 0) {
  console.error("FAIL: August should be empty", aug);
  failed = true;
} else {
  console.log("OK: August empty");
}

const board = boardHoursForMonth(
  [
    {
      id: 1,
      slug: "demo",
      key: "1-demo",
      title: "Demo",
      color: "#b45309",
      doc,
    },
    {
      id: 2,
      slug: "other",
      key: "2-other",
      title: "Other",
      color: "#b45309",
      doc,
    },
  ],
  "2026-09"
);
if (board.active_ms !== 1200000 || board.wall_ms !== 7200000 || board.epics.length !== 2) {
  console.error("FAIL: board sum of two epics", board);
  failed = true;
} else {
  console.log("OK: board sums two epics");
}

const months = collectMonthKeys([doc], new Date("2026-09-15T00:00:00.000Z"));
if (!months.includes("2026-09") || months[0] !== currentMonthKey(new Date("2026-09-15T00:00:00.000Z"))) {
  console.error("FAIL: month keys", months);
  failed = true;
} else {
  console.log("OK: month keys include September");
}

if (failed) process.exit(1);
console.log("hours smoke OK");
