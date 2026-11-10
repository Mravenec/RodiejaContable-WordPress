import { useEffect, useState } from "react";

function ProgressRing({ pct, size = 22 }) {
  const r = (size - 4) / 2;
  const c = 2 * Math.PI * r;
  const offset = c - (pct / 100) * c;
  return (
    <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`} aria-hidden>
      <circle
        cx={size / 2}
        cy={size / 2}
        r={r}
        fill="none"
        stroke="#2a3140"
        strokeWidth="2.5"
      />
      <circle
        cx={size / 2}
        cy={size / 2}
        r={r}
        fill="none"
        stroke={pct === 100 ? "var(--ok)" : "var(--accent)"}
        strokeWidth="2.5"
        strokeDasharray={c}
        strokeDashoffset={offset}
        strokeLinecap="round"
        transform={`rotate(-90 ${size / 2} ${size / 2})`}
      />
    </svg>
  );
}

function epicKey(epic) {
  if (epic?.id == null || !epic?.slug) return epic?.title || "";
  return `${epic.id}-${epic.slug}`;
}

function formatMonthLabel(ym) {
  if (!/^\d{4}-\d{2}$/.test(ym || "")) return ym || "";
  const y = Number(ym.slice(0, 4));
  const m = Number(ym.slice(5, 7));
  return new Date(Date.UTC(y, m - 1, 1)).toLocaleString("en-US", {
    month: "long",
    year: "numeric",
    timeZone: "UTC",
  });
}

function HoursView({ data, month, months, onMonth }) {
  return (
    <section className="hours-view">
      <header className="page-head">
        <h1>Hours worked</h1>
        <p className="muted">
          Board-wide totals for the selected month. Active is working windows
          (idle gaps omitted). Elapsed is claim-to-handoff wall time. Land
          active / elapsed cover merge-to-main. All four are the sum of every
          epic that had time in that month.
        </p>
      </header>
      <div className="hours-bar">
        <label className="month-pick">
          <span>Month</span>
          <select
            value={month}
            onChange={(e) => onMonth(e.target.value)}
            aria-label="Month"
          >
            {(months || []).map((ym) => (
              <option key={ym} value={ym}>
                {formatMonthLabel(ym)}
              </option>
            ))}
          </select>
        </label>
        <div className="hours-totals" aria-live="polite">
          <div>
            <div className="muted hours-k">Active</div>
            <strong className="mono hours-v">
              {data?.active_label || "0s"}
            </strong>
          </div>
          <div>
            <div className="muted hours-k">Elapsed</div>
            <strong className="mono hours-v">
              {data?.wall_label || "0s"}
            </strong>
          </div>
          <div>
            <div className="muted hours-k">Land active</div>
            <strong className="mono hours-v">
              {data?.land_active_label || "0s"}
            </strong>
          </div>
          <div>
            <div className="muted hours-k">Land elapsed</div>
            <strong className="mono hours-v">
              {data?.land_wall_label || "0s"}
            </strong>
          </div>
        </div>
      </div>
      {data ? (
        <>
          <ul className="hours-epics">
            {(data.epics || []).map((e) => (
              <li key={e.key || e.id}>
                <span className="dot" style={{ background: e.color || "#b45309" }} />
                <span className="epic-key mono">{e.key}</span>
                <span className="muted hours-epic-title">{e.title}</span>
                <span className="mono">
                  active {e.active_label} · elapsed {e.wall_label}
                  {e.land_active_ms || e.land_wall_ms
                    ? ` · land ${e.land_active_label}/${e.land_wall_label}`
                    : ""}
                </span>
              </li>
            ))}
          </ul>
          {!(data.epics || []).length ? (
            <p className="muted">No recorded work in this month.</p>
          ) : null}
        </>
      ) : (
        <p className="muted">Loading…</p>
      )}
    </section>
  );
}

function landBadgeLabel(land) {
  const status = land?.status;
  if (status === "landed") return "Landed";
  if (status === "smoke-main") return "Smoke-main";
  if (status === "landing") return "Landing";
  if (status === "on-origin") return "On origin";
  // Missing land.json / not on main yet — never "No updates" (that hid real land gaps).
  return "Not landed";
}

function EpicRow({ epic, selected, onSelect }) {
  const key = epicKey(epic);
  const landLabel = landBadgeLabel(epic.land);
  const landed = epic.land?.status === "landed";
  return (
    <button
      type="button"
      className={`epic-row ${selected ? "selected" : ""}`}
      onClick={() => onSelect(epic)}
    >
      <span className="dot" style={{ background: epic.color || "#b45309" }} />
      <span className="epic-title">
        <span className="epic-key mono">{key}</span>
        {epic.title && epic.title !== key ? (
          <span className="epic-human-title muted"> {epic.title}</span>
        ) : null}
      </span>
      <span className="muted updates">
        <span className={`land-badge${landed ? "" : " land-badge-muted"}`}>
          {landLabel}
        </span>
      </span>
      <span className="mono count">{epic.issues}</span>
      <span className="pct">
        <ProgressRing pct={epic.progress} />
        <span className="mono">{epic.progress}%</span>
      </span>
    </button>
  );
}

function Checklist({ checklist }) {
  if (!checklist?.total) {
    return <p className="muted">No checklist on this issue.</p>;
  }
  return (
    <div className="checklist">
      <div className="checklist-head">
        <span>Checklist</span>
        <span className="mono muted">
          {checklist.done}/{checklist.total}
        </span>
      </div>
      <ul>
        {checklist.items.map((item) => (
          <li key={item.index} className={item.done ? "done" : ""}>
            <span className={`box ${item.done ? "on" : ""}`} aria-hidden>
              {item.done ? "✓" : ""}
            </span>
            <span>
              <span className="mono muted idx">{item.index}.</span> {item.text}
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}

function IssuePanel({ issue, onClose, loading }) {
  if (!issue && !loading) return null;
  return (
    <div className="issue-overlay" role="dialog" aria-modal="true">
      <button type="button" className="issue-backdrop" onClick={onClose} aria-label="Close" />
      <aside className="issue-panel">
        {loading || !issue ? (
          <p className="muted">Loading…</p>
        ) : (
          <>
            <header className="issue-head">
              <div>
                <div className="mono issue-id">{issue.id}</div>
                <h2>{issue.title}</h2>
              </div>
              <button type="button" className="close-btn" onClick={onClose}>
                Esc
              </button>
            </header>

            <div className="issue-chips">
              <span className="chip">{issue.column}</span>
              <span className="chip">{issue.meta.role}</span>
              {issue.meta.agent ? (
                <span className="chip agent">{issue.meta.agent}</span>
              ) : null}
              {issue.meta.estimate != null ? (
                <span className="chip mono">{issue.meta.estimate}pt</span>
              ) : null}
              {issue.time?.active_label ? (
                <span className="chip mono">active {issue.time.active_label}</span>
              ) : null}
            </div>

            {issue.objective ? (
              <section className="issue-sec">
                <h3>Objetivo</h3>
                <p>{issue.objective}</p>
              </section>
            ) : null}

            <section className="issue-sec">
              <Checklist checklist={issue.checklist} />
            </section>

            {(issue.meta.blocked_by || []).length > 0 ? (
              <section className="issue-sec">
                <h3>Blocked by</h3>
                <p className="mono">{issue.meta.blocked_by.join(", ")}</p>
              </section>
            ) : null}

            {issue.meta.summary_html || issue.epic?.summary_html ? (
              <section className="issue-sec">
                <h3>Living summary</h3>
                <p>
                  <a
                    className="living-summary-link"
                    href={`/api/epic/${encodeURIComponent(issue.epic?.id || "")}/summary`}
                    target="_blank"
                    rel="noreferrer"
                  >
                    Open HTML
                  </a>
                  <span className="mono muted">
                    {" "}
                    {issue.meta.summary_html || issue.epic?.summary_html}
                  </span>
                </p>
              </section>
            ) : null}

            {issue.history ? (
              <section className="issue-sec">
                <h3>History</h3>
                <pre className="history mono">{issue.history}</pre>
              </section>
            ) : null}
          </>
        )}
      </aside>
    </div>
  );
}

function Board({ data, onOpenIssue }) {
  if (!data) return null;
  const cols = data.columns || [];
  return (
    <div className="board">
      <header className="board-head">
        <div>
          <h2>
            <span className="epic-key mono">{epicKey(data.meta)}</span>
            {data.meta.title && data.meta.title !== epicKey(data.meta)
              ? ` · ${data.meta.title}`
              : ""}
          </h2>
          <p className="muted">
            Status <strong>{data.meta.status}</strong> · Progress{" "}
            <strong className="mono">{data.progress.pct}%</strong>
            {" "}(column + checklist · {data.progress.done}/{data.progress.total} in
            summarize)
            {data.time?.active_label ? (
              <>
                {" · "}active{" "}
                <strong className="mono">{data.time.active_label}</strong>
                {data.time.wall_label ? (
                  <>
                    {" "}
                    (elapsed {data.time.wall_label})
                  </>
                ) : null}
              </>
            ) : null}
            {" · click a ticket to open checklist"}
            {" · "}
            <a
              className="living-summary-link"
              href={`/api/epic/${encodeURIComponent(data.meta.id)}/summary`}
              target="_blank"
              rel="noreferrer"
            >
              Open living summary
            </a>
          </p>
        </div>
      </header>
      <div
        className="columns"
        style={{
          gridTemplateColumns: `repeat(${Math.max(cols.length, 1)}, minmax(160px, 1fr))`,
        }}
      >
        {cols.map((c) => (
          <section key={c.id} className="column">
            <h3>
              {c.folder}
              <span className="mono badge">{(data.board[c.id] || []).length}</span>
            </h3>
            <ul>
              {(data.board[c.id] || []).map((issue) => {
                const cl = issue.checklist;
                return (
                  <li key={issue.id}>
                    <button
                      type="button"
                      className="card"
                      onClick={() => onOpenIssue(issue.id)}
                    >
                      <div className="card-id mono">{issue.id}</div>
                      <div className="card-title">{issue.title}</div>
                      <div className="card-meta muted">
                        <span className="role">{issue.role}</span>
                        {issue.agent ? <span> · {issue.agent}</span> : null}
                        {cl?.total ? (
                          <span className="mono cl-mini">
                            {" "}
                            · {cl.done}/{cl.total}
                          </span>
                        ) : null}
                      </div>
                    </button>
                  </li>
                );
              })}
            </ul>
          </section>
        ))}
      </div>
    </div>
  );
}

export default function App() {
  const [epics, setEpics] = useState([]);
  const [selected, setSelected] = useState(null);
  const [detail, setDetail] = useState(null);
  const [issue, setIssue] = useState(null);
  const [issueLoading, setIssueLoading] = useState(false);
  const [error, setError] = useState(null);
  const [view, setView] = useState("epics");
  const [month, setMonth] = useState(() => new Date().toISOString().slice(0, 7));
  const [hours, setHours] = useState(null);

  async function loadHours(ym = month) {
    try {
      const q = ym ? `?month=${encodeURIComponent(ym)}` : "";
      const r = await fetch(`/api/hours${q}`);
      if (!r.ok) throw new Error(`API ${r.status}`);
      const data = await r.json();
      setHours(data);
      if (!ym && data.month) setMonth(data.month);
      setError(null);
    } catch (e) {
      setError(
        `Could not reach the local API (${e.message}). Run: node .linear/scripts/api.mjs`
      );
    }
  }

  async function loadEpics() {
    try {
      const r = await fetch("/api/epics");
      if (!r.ok) throw new Error(`API ${r.status}`);
      const data = await r.json();
      setEpics(data.epics || []);
      setError(null);
    } catch (e) {
      setError(
        `Could not reach the local API (${e.message}). Run: node .linear/scripts/api.mjs`
      );
    }
  }

  async function loadEpic(epic) {
    setSelected(epic);
    setView("board");
    setIssue(null);
    try {
      const r = await fetch(`/api/epic/${epic.id}`);
      if (!r.ok) throw new Error(`API ${r.status}`);
      setDetail(await r.json());
      setError(null);
    } catch (e) {
      setError(e.message);
    }
  }

  async function openIssue(id) {
    setIssueLoading(true);
    try {
      const r = await fetch(`/api/issue/${encodeURIComponent(id)}`);
      if (!r.ok) throw new Error(`API ${r.status}`);
      setIssue(await r.json());
      setError(null);
    } catch (e) {
      setError(e.message);
      setIssue(null);
    } finally {
      setIssueLoading(false);
    }
  }

  useEffect(() => {
    loadEpics();
    const t = setInterval(() => {
      loadEpics();
      if (selected) {
        fetch(`/api/epic/${selected.id}`)
          .then((r) => r.json())
          .then(setDetail)
          .catch(() => {});
      }
      if (issue?.id) {
        fetch(`/api/issue/${encodeURIComponent(issue.id)}`)
          .then((r) => r.json())
          .then(setIssue)
          .catch(() => {});
      }
      if (view === "hours") {
        fetch(`/api/hours?month=${encodeURIComponent(month)}`)
          .then((r) => r.json())
          .then(setHours)
          .catch(() => {});
      }
    }, 2000);
    return () => clearInterval(t);
  }, [selected?.id, issue?.id, view, month]);

  useEffect(() => {
    function onKey(e) {
      if (e.key === "Escape") setIssue(null);
    }
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  return (
    <div className="app">
      <aside className="nav">
        <div className="brand mono">private.board</div>
        <button
          type="button"
          className={view === "epics" ? "nav-item active" : "nav-item"}
          onClick={() => {
            setView("epics");
            setIssue(null);
          }}
        >
          Epics
        </button>
        <button
          type="button"
          className={view === "board" && selected ? "nav-item active" : "nav-item"}
          onClick={() => selected && setView("board")}
          disabled={!selected}
        >
          Board
        </button>
        <button
          type="button"
          className={view === "hours" ? "nav-item active" : "nav-item"}
          onClick={() => {
            setView("hours");
            setIssue(null);
            loadHours(month);
          }}
        >
          Hours worked
        </button>
        <p className="nav-foot muted">
          FS source of truth · poll 2s · no cloud API
        </p>
      </aside>

      <main>
        {error ? <div className="banner">{error}</div> : null}

        {view === "epics" ? (
          <section className="epics-view">
            <header className="page-head">
              <h1>Epics</h1>
              <p className="muted">
                Local filesystem board for epics. The living summary HTML grows as
                tickets move (including testing → doing). SUMMARIZE records the git
                command and Supervisor / Manager note.
              </p>
            </header>
            <div className="epic-list">
              {epics.map((e) => (
                <EpicRow
                  key={e.id}
                  epic={e}
                  selected={selected?.id === e.id}
                  onSelect={loadEpic}
                />
              ))}
              {!epics.length && !error ? (
                <p className="muted">
                  No epics yet. Create one with{" "}
                  <span className="mono">node scripts/epic-new.mjs …</span>.
                </p>
              ) : null}
            </div>
          </section>
        ) : view === "hours" ? (
          <HoursView
            data={hours}
            month={month}
            months={hours?.months}
            onMonth={(ym) => {
              setMonth(ym);
              loadHours(ym);
            }}
          />
        ) : (
          <Board data={detail} onOpenIssue={openIssue} />
        )}
      </main>

      <IssuePanel
        issue={issue}
        loading={issueLoading}
        onClose={() => setIssue(null)}
      />

      <style>{`
        .app { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .nav {
          border-right: 1px solid var(--border);
          padding: 1.25rem 1rem;
          background: rgba(0,0,0,.25);
          display: flex; flex-direction: column; gap: .35rem;
        }
        .brand { font-weight: 500; letter-spacing: .02em; margin-bottom: 1.25rem; color: var(--text); }
        .nav-item {
          text-align: left; background: transparent; border: 0; color: var(--muted);
          padding: .55rem .7rem; border-radius: 8px;
        }
        .nav-item.active, .nav-item:hover:not(:disabled) { background: var(--surface-2); color: var(--text); }
        .nav-item:disabled { opacity: .4; cursor: not-allowed; }
        .nav-foot { margin-top: auto; font-size: .75rem; line-height: 1.4; }
        main { padding: 1.5rem 1.75rem 2.5rem; }
        .page-head h1, .board-head h2 { margin: 0 0 .35rem; font-weight: 600; letter-spacing: -0.02em; }
        .page-head p, .board-head p { margin: 0 0 1.25rem; }
        .banner {
          background: #3a2218; border: 1px solid #6b3a28; color: #ffc9b0;
          padding: .75rem 1rem; border-radius: 10px; margin-bottom: 1rem; font-size: .9rem;
        }
        .epic-list { display: flex; flex-direction: column; gap: .35rem; }
        .epic-row {
          display: grid;
          grid-template-columns: 14px 1fr 110px 36px 72px;
          gap: .75rem; align-items: center;
          width: 100%; text-align: left;
          background: var(--surface); border: 1px solid transparent;
          color: var(--text); padding: .7rem .9rem; border-radius: 10px;
        }
        .epic-row:hover, .epic-row.selected { border-color: var(--border); background: var(--surface-2); }
        .dot { width: 10px; height: 10px; border-radius: 50%; }
        .epic-title { font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .epic-key { font-weight: 600; }
        .epic-human-title { font-weight: 400; }
        .updates { font-size: .8rem; }
        .count { color: var(--muted); font-size: .85rem; text-align: right; }
        .pct { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; font-size: .85rem; color: var(--muted); }
        .muted { color: var(--muted); }
        .living-summary-link {
          color: #9ec5ff;
          font-weight: 600;
          text-decoration: underline;
          text-underline-offset: 3px;
        }
        .living-summary-link:hover { color: #c5dcff; }
        .columns {
          display: grid;
          gap: .85rem;
          overflow-x: auto;
        }
        .column {
          background: rgba(20,24,32,.7);
          border: 1px solid var(--border);
          border-radius: 12px;
          padding: .75rem;
          min-height: 320px;
        }
        .column h3 {
          margin: 0 0 .75rem; font-size: .8rem; text-transform: uppercase;
          letter-spacing: .06em; color: var(--muted);
          display: flex; justify-content: space-between; align-items: center;
        }
        .badge {
          background: var(--surface-2); padding: .1rem .4rem; border-radius: 6px; font-size: .75rem;
        }
        .column ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .5rem; }
        .card {
          display: block; width: 100%; text-align: left;
          background: var(--surface);
          border: 1px solid var(--border);
          border-radius: 10px;
          padding: .65rem .7rem;
          color: var(--text);
          cursor: pointer;
        }
        .card:hover { border-color: var(--accent); }
        .card-id { font-size: .7rem; color: var(--accent); margin-bottom: .25rem; }
        .card-title { font-size: .88rem; line-height: 1.35; }
        .card-meta { font-size: .75rem; margin-top: .35rem; }
        .role { text-transform: uppercase; letter-spacing: .04em; }

        .issue-overlay {
          position: fixed; inset: 0; z-index: 40;
          display: flex; justify-content: flex-end;
        }
        .issue-backdrop {
          position: absolute; inset: 0; border: 0;
          background: rgba(0,0,0,.45); cursor: pointer;
        }
        .issue-panel {
          position: relative; z-index: 1;
          width: min(480px, 100%);
          height: 100%;
          overflow: auto;
          background: #12161f;
          border-left: 1px solid var(--border);
          padding: 1.25rem 1.35rem 2rem;
          box-shadow: -12px 0 40px rgba(0,0,0,.35);
        }
        .issue-head {
          display: flex; justify-content: space-between; gap: 1rem;
          align-items: flex-start; margin-bottom: 1rem;
        }
        .issue-head h2 {
          margin: .2rem 0 0; font-size: 1.15rem; font-weight: 600;
          letter-spacing: -0.02em; line-height: 1.3;
        }
        .issue-id { color: var(--accent); font-size: .8rem; }
        .close-btn {
          background: var(--surface-2); border: 1px solid var(--border);
          color: var(--muted); border-radius: 8px; padding: .35rem .6rem;
          font-size: .75rem;
        }
        .issue-chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1.25rem; }
        .chip {
          font-size: .72rem; text-transform: uppercase; letter-spacing: .04em;
          padding: .25rem .5rem; border-radius: 6px;
          background: var(--surface-2); border: 1px solid var(--border); color: var(--muted);
        }
        .chip.agent { text-transform: none; color: var(--text); }
        .issue-sec { margin-bottom: 1.25rem; }
        .issue-sec h3 {
          margin: 0 0 .5rem; font-size: .75rem; text-transform: uppercase;
          letter-spacing: .06em; color: var(--muted); font-weight: 500;
        }
        .issue-sec p { margin: 0; line-height: 1.45; white-space: pre-wrap; }
        .checklist-head {
          display: flex; justify-content: space-between; align-items: center;
          margin-bottom: .6rem; font-size: .75rem; text-transform: uppercase;
          letter-spacing: .06em; color: var(--muted);
        }
        .checklist ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .45rem; }
        .checklist li {
          display: flex; gap: .6rem; align-items: flex-start;
          padding: .45rem .5rem; border-radius: 8px; background: var(--surface);
          border: 1px solid var(--border); font-size: .9rem; line-height: 1.35;
        }
        .checklist li.done { opacity: .75; }
        .checklist li.done span:last-child { text-decoration: line-through; color: var(--muted); }
        .box {
          flex: 0 0 18px; width: 18px; height: 18px; margin-top: 1px;
          border: 1.5px solid var(--muted); border-radius: 4px;
          display: grid; place-items: center; font-size: .7rem; color: #0c0e12;
        }
        .box.on { background: var(--ok); border-color: var(--ok); }
        .idx { margin-right: .25rem; }
        .history {
          margin: 0; white-space: pre-wrap; font-size: .72rem; line-height: 1.45;
          color: var(--muted); background: var(--surface); padding: .75rem;
          border-radius: 8px; border: 1px solid var(--border); max-height: 220px; overflow: auto;
        }
        .hours-bar {
          display: flex;
          flex-wrap: wrap;
          align-items: flex-end;
          gap: 1rem 1.5rem;
          margin: 0 0 1.25rem;
        }
        .month-pick {
          display: flex; align-items: center; gap: .65rem;
          margin: 0; color: var(--muted); font-size: .85rem;
          flex: 0 0 auto;
        }
        .month-pick select {
          background: var(--surface-2); color: var(--text);
          border: 1px solid var(--border); border-radius: 8px;
          padding: .4rem .6rem; font: inherit;
        }
        .hours-totals {
          display: grid;
          grid-template-columns: repeat(4, minmax(100px, 1fr));
          gap: .75rem 1.25rem;
          flex: 1 1 420px;
          margin: 0;
        }
        .hours-k { font-size: .75rem; text-transform: uppercase; letter-spacing: .06em; }
        .hours-v { font-size: 1.35rem; }
        .land-badge {
          display: inline-block;
          font-size: .72rem;
          letter-spacing: .04em;
          text-transform: uppercase;
          color: var(--accent, #b45309);
          border: 1px solid rgba(180, 83, 9, 0.35);
          padding: .1rem .4rem;
          border-radius: 4px;
        }
        .land-badge-muted {
          color: var(--muted, #78716c);
          border-color: rgba(120, 113, 108, 0.35);
        }
        .hours-epics { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .4rem; }
        @media (max-width: 900px) {
          .hours-totals { grid-template-columns: repeat(2, minmax(100px, 1fr)); }
        }
        .hours-epics li {
          display: grid; grid-template-columns: 14px auto 1fr auto;
          gap: .65rem; align-items: center;
          background: var(--surface); border: 1px solid var(--border);
          border-radius: 10px; padding: .65rem .85rem; font-size: .9rem;
        }
        .hours-epic-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        @media (max-width: 900px) {
          .app { grid-template-columns: 1fr; }
          .nav { flex-direction: row; flex-wrap: wrap; align-items: center; border-right: 0; border-bottom: 1px solid var(--border); }
          .nav-foot { display: none; }
          .epic-row { grid-template-columns: 14px 1fr 56px; }
          .updates { display: none; }
          .issue-panel { width: 100%; }
        }
      `}</style>
    </div>
  );
}
