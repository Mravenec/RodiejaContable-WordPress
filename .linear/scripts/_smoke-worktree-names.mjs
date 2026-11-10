/**
 * Ticket branches must not nest under the epic ref (git file/dir collision).
 * Run: node scripts/_smoke-worktree-names.mjs
 */
import {
  epicBranchName,
  ticketBranchName,
  ticketBranchAliases,
} from "./lib/worktree-paths.mjs";

const epic = "1-my-feature";
const id = "APP-1-2";
const epicRef = epicBranchName(epic);
const ticketRef = ticketBranchName(epic, id);
let failed = false;

if (epicRef !== "feature/1-my-feature") {
  console.error("FAIL: epic branch", epicRef);
  failed = true;
} else {
  console.log("OK: epic branch", epicRef);
}

if (ticketRef !== "ticket/1-my-feature/APP-1-2") {
  console.error("FAIL: ticket branch", ticketRef);
  failed = true;
} else {
  console.log("OK: ticket branch", ticketRef);
}

if (ticketRef.startsWith(epicRef + "/")) {
  console.error("FAIL: ticket branch nests under epic ref (git cannot store both)");
  failed = true;
} else {
  console.log("OK: ticket ref is not nested under epic ref");
}

const aliases = ticketBranchAliases(epic, id);
if (!aliases.includes("feature/1-my-feature/APP-1-2") || !aliases.includes("feature/APP-1-2")) {
  console.error("FAIL: missing legacy aliases", aliases);
  failed = true;
} else {
  console.log("OK: legacy aliases still listed for lookup");
}

process.exit(failed ? 1 : 0);
