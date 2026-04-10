<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM — Dashboard Penarikan Dana (CEO)</title>
  <meta name="description" content="Dashboard eksklusif CEO untuk mengelola dan menarik dana dari pesanan yang sudah selesai.">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    /* ===== VARIABLES ===== */
    :root {
      --bg:       #07091a;
      --bg-card:  #0c1230;
      --bg-card2: #111a3e;
      --border:   rgba(100,149,255,.13);
      --accent:   #4f8ef7;
      --accent2:  #7c5cfc;
      --success:  #22c55e;
      --warning:  #f59e0b;
      --danger:   #ef4444;
      --gold:     #f59e0b;
      --text:     #e2e8f0;
      --muted:    #5a6a8a;
      --r:        14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { min-height: 100vh; background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; font-size: 15px; }

    /* BG effects */
    body::before {
      content: ''; position: fixed; inset: 0; z-index: 0;
      background: linear-gradient(rgba(79,142,247,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(79,142,247,.04) 1px, transparent 1px);
      background-size: 44px 44px; pointer-events: none;
    }
    body::after {
      content: ''; position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 60% 40% at 10% 20%, rgba(245,158,11,.05) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 90% 80%, rgba(124,92,252,.06) 0%, transparent 60%);
      pointer-events: none;
    }

    .page { position: relative; z-index: 1; min-height: 100vh; display: flex; flex-direction: column; }

    /* HEADER */
    header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1.1rem 2rem;
      background: rgba(7,9,26,.85);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(20px);
      position: sticky; top: 0; z-index: 100;
    }
    .logo { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--text); }
    .logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--gold), #f97316);
      border-radius: 11px;
      display: flex; align-items: center; justify-content: center;
      font-size: 19px; box-shadow: 0 4px 16px rgba(245,158,11,.35);
    }
    .logo-name { font-weight: 800; font-size: 1.05rem; letter-spacing: -.3px; }
    .logo-sub  { font-size: .68rem; color: var(--muted); }
    .header-actions { display: flex; gap: .75rem; align-items: center; }
    .btn-nav {
      padding: .35rem .85rem; border: 1px solid var(--border);
      border-radius: 20px; font-size: .75rem; color: var(--accent);
      background: rgba(79,142,247,.08); text-decoration: none;
      transition: all .2s; cursor: pointer;
      display: inline-flex; align-items: center; gap: .3rem;
    }
    .btn-nav:hover { background: rgba(79,142,247,.2); }
    .badge-ceo {
      padding: .25rem .7rem; background: linear-gradient(135deg, rgba(245,158,11,.2), rgba(249,115,22,.15));
      border: 1px solid rgba(245,158,11,.3); border-radius: 20px;
      font-size: .7rem; font-weight: 700; color: var(--gold); letter-spacing: .5px;
    }

    /* MAIN */
    main { flex: 1; padding: 2.5rem 1.5rem; max-width: 1100px; margin: 0 auto; width: 100%; }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 {
      font-size: 1.85rem; font-weight: 800; letter-spacing: -.5px;
      background: linear-gradient(135deg, #fff 30%, #fbbf24 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .page-header p { color: var(--muted); font-size: .9rem; margin-top: .4rem; }

    /* ===== STAT CARDS ===== */
    .stats-grid {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;
      margin-bottom: 2rem;
    }
    @media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 500px) { .stats-grid { grid-template-columns: 1fr; } }

    .stat-card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: var(--r); padding: 1.5rem 1.4rem;
      position: relative; overflow: hidden; transition: all .25s;
    }
    .stat-card:hover { border-color: rgba(79,142,247,.28); transform: translateY(-2px); }
    .stat-card::before {
      content: ''; position: absolute; inset: 0;
      background: radial-gradient(circle at top right, var(--glow, rgba(79,142,247,.06)), transparent 65%);
      pointer-events: none;
    }
    .stat-card.card-gold   { --glow: rgba(245,158,11,.08); }
    .stat-card.card-green  { --glow: rgba(34,197,94,.07); }
    .stat-card.card-purple { --glow: rgba(124,92,252,.07); }
    .stat-card.card-blue   { --glow: rgba(79,142,247,.07); }

    .sc-icon { font-size: 1.5rem; margin-bottom: .75rem; }
    .sc-val  { font-size: 1.55rem; font-weight: 800; line-height: 1.1; color: var(--text); }
    .sc-subval { font-size: .9rem; font-weight: 600; color: var(--muted); margin-top: .1rem; }
    .sc-label { font-size: .72rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-top: .5rem; }

    /* ===== ACTION BAR ===== */
    .action-bar {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 1.25rem; flex-wrap: wrap;
    }
    .action-bar h2 { font-size: 1.1rem; font-weight: 700; flex: 1; }
    .selected-info { font-size: .82rem; color: var(--gold); display: none; align-items: center; gap: .4rem; }
    .selected-info.visible { display: flex; }

    .btn-action {
      padding: .55rem 1.3rem; border: none; border-radius: 10px;
      font-size: .85rem; font-weight: 700; cursor: pointer;
      display: inline-flex; align-items: center; gap: .45rem;
      transition: all .22s; white-space: nowrap;
    }
    .btn-tarik {
      background: linear-gradient(135deg, var(--gold), #f97316);
      color: #fff; box-shadow: 0 4px 18px rgba(245,158,11,.3);
    }
    .btn-tarik:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(245,158,11,.45); }
    .btn-tarik:disabled { opacity: .35; cursor: not-allowed; transform: none; }

    .btn-tarik-all {
      background: linear-gradient(135deg, var(--success), #16a34a);
      color: #fff; box-shadow: 0 4px 18px rgba(34,197,94,.25);
    }
    .btn-tarik-all:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(34,197,94,.4); }
    .btn-tarik-all:disabled { opacity: .35; cursor: not-allowed; transform: none; }

    .btn-refresh {
      background: rgba(79,142,247,.1); color: var(--accent);
      border: 1px solid rgba(79,142,247,.2);
    }
    .btn-refresh:hover { background: rgba(79,142,247,.2); }

    /* ===== TABLE ===== */
    .table-card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: var(--r); overflow: hidden; margin-bottom: 2rem;
    }
    .table-card-head {
      padding: 1rem 1.4rem; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: .6rem;
      background: var(--bg-card2);
    }
    .table-card-head span { font-size: .8rem; color: var(--muted); margin-left: auto; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    thead th {
      background: rgba(255,255,255,.03); padding: .7rem 1rem;
      text-align: left; color: var(--muted); font-weight: 500;
      white-space: nowrap; border-bottom: 1px solid var(--border);
    }
    thead th.col-check { width: 40px; text-align: center; }
    tbody td {
      padding: .7rem 1rem; border-bottom: 1px solid rgba(255,255,255,.04);
      white-space: nowrap; transition: background .15s;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: rgba(79,142,247,.035); }
    tbody tr.selected td { background: rgba(245,158,11,.06); }

    td.col-check { text-align: center; }
    input[type="checkbox"] {
      width: 16px; height: 16px; accent-color: var(--gold);
      cursor: pointer; border-radius: 4px;
    }

    .amount { font-weight: 700; color: #4ade80; }
    .order-code { font-size: .73rem; color: #93c5fd; font-family: monospace; }
    .platform-badge {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .18rem .55rem; border-radius: 20px; font-size: .68rem; font-weight: 600;
      background: rgba(79,142,247,.12); color: var(--accent);
    }

    /* ===== TABS ===== */
    .tabs { display: flex; gap: .15rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
    .tab-btn {
      padding: .65rem 1.3rem; background: none; border: none;
      font-family: 'Inter', sans-serif; font-size: .88rem; font-weight: 500;
      color: var(--muted); cursor: pointer; position: relative;
      transition: color .2s; border-bottom: 2px solid transparent; margin-bottom: -1px;
    }
    .tab-btn.active { color: var(--gold); border-bottom-color: var(--gold); }
    .tab-btn .tab-count {
      display: inline-flex; align-items: center; justify-content: center;
      width: 20px; height: 20px; border-radius: 50%; font-size: .65rem; font-weight: 700;
      margin-left: .4rem; background: rgba(245,158,11,.15); color: var(--gold);
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
      text-align: center; padding: 4rem 2rem; color: var(--muted);
    }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: .4; display: block; }
    .empty-state p { font-size: .9rem; }

    /* ===== LOADING ===== */
    #loading-overlay {
      display: none; position: fixed; inset: 0; z-index: 999;
      background: rgba(7,9,26,.7); backdrop-filter: blur(4px);
      align-items: center; justify-content: center; flex-direction: column; gap: 1rem;
    }
    #loading-overlay.visible { display: flex; }
    .spinner {
      width: 48px; height: 48px; border-radius: 50%;
      border: 3px solid rgba(245,158,11,.2);
      border-top-color: var(--gold);
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-text { color: var(--gold); font-weight: 600; font-size: .9rem; }

    /* ===== TOAST ===== */
    #toast {
      position: fixed; bottom: 2rem; right: 2rem; z-index: 9999;
      padding: 1rem 1.5rem; border-radius: 12px;
      font-size: .88rem; font-weight: 600;
      display: flex; align-items: center; gap: .65rem;
      transform: translateY(120px); opacity: 0;
      transition: all .35s cubic-bezier(.34,1.56,.64,1);
      max-width: 360px; pointer-events: none;
      box-shadow: 0 16px 40px rgba(0,0,0,.5);
    }
    #toast.show { transform: translateY(0); opacity: 1; pointer-events: all; }
    #toast.success { background: rgba(34,197,94,.15); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
    #toast.error   { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }

    /* ===== CONFIRM MODAL ===== */
    .modal-overlay {
      display: none; position: fixed; inset: 0; z-index: 500;
      background: rgba(0,0,0,.6); backdrop-filter: blur(4px);
      align-items: center; justify-content: center;
    }
    .modal-overlay.visible { display: flex; }
    .modal-box {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: 18px; padding: 2rem; max-width: 420px; width: 90%;
      box-shadow: 0 24px 64px rgba(0,0,0,.6);
      animation: popIn .25s ease;
    }
    @keyframes popIn { from { transform: scale(.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .modal-icon    { font-size: 2.5rem; margin-bottom: 1rem; }
    .modal-title   { font-size: 1.15rem; font-weight: 800; margin-bottom: .5rem; }
    .modal-desc    { color: var(--muted); font-size: .88rem; line-height: 1.65; margin-bottom: 1.5rem; }
    .modal-amount  { font-size: 1.35rem; font-weight: 800; color: var(--gold); margin: .5rem 0 1rem; }
    .modal-actions { display: flex; gap: .75rem; }
    .btn-cancel {
      flex: 1; padding: .7rem; border-radius: 10px; border: 1px solid var(--border);
      background: none; color: var(--muted); font-size: .88rem; cursor: pointer;
      font-family: 'Inter', sans-serif; font-weight: 600; transition: all .2s;
    }
    .btn-cancel:hover { background: rgba(255,255,255,.05); color: var(--text); }
    .btn-confirm {
      flex: 2; padding: .7rem; border-radius: 10px; border: none;
      background: linear-gradient(135deg, var(--gold), #f97316);
      color: #fff; font-size: .88rem; cursor: pointer;
      font-family: 'Inter', sans-serif; font-weight: 700; transition: all .2s;
    }
    .btn-confirm:hover { box-shadow: 0 6px 20px rgba(245,158,11,.4); }

    /* ===== FOOTER ===== */
    footer {
      text-align: center; padding: 1.2rem; border-top: 1px solid var(--border);
      color: var(--muted); font-size: .75rem;
    }
    footer code { color: var(--accent); }
  </style>
</head>
<body>
<div class="page">

  <!-- LOADING OVERLAY -->
  <div id="loading-overlay">
    <div class="spinner"></div>
    <div class="loading-text" id="loading-text">Memproses penarikan…</div>
  </div>

  <!-- TOAST -->
  <div id="toast"></div>

  <!-- CONFIRM MODAL -->
  <div class="modal-overlay" id="confirm-modal">
    <div class="modal-box">
      <div class="modal-icon">💰</div>
      <div class="modal-title" id="modal-title">Konfirmasi Penarikan Dana</div>
      <div class="modal-desc" id="modal-desc">Apakah kamu yakin ingin menarik dana dari pesanan yang dipilih?</div>
      <div class="modal-amount" id="modal-amount"></div>
      <div class="modal-actions">
        <button class="btn-cancel" id="modal-cancel">Batal</button>
        <button class="btn-confirm" id="modal-confirm">
          <i class="bi bi-cash-stack"></i> Ya, Tarik Sekarang
        </button>
      </div>
    </div>
  </div>

  <!-- HEADER -->
  <header>
    <a href="<?= base_url('/withdrawal') ?>" class="logo">
      <div class="logo-icon">💰</div>
      <div>
        <div class="logo-name">SIM Withdrawal</div>
        <div class="logo-sub">Dashboard Penarikan Dana</div>
      </div>
    </a>
    <div class="header-actions">
      <span class="badge-ceo"><i class="bi bi-shield-lock-fill"></i> CEO ACCESS</span>
      <a href="<?= base_url('/import') ?>" class="btn-nav"><i class="bi bi-cloud-arrow-up"></i> Import</a>
    </div>
  </header>

  <!-- MAIN -->
  <main>
    <div class="page-header">
      <h1>Dashboard Penarikan Dana</h1>
      <p>Kelola dan tarik dana dari pesanan yang sudah berstatus <strong>Selesai</strong>. Status penarikan aman dari re-import Excel.</p>
    </div>

    <!-- SUMMARY STATS -->
    <div class="stats-grid" id="stats-grid">
      <div class="stat-card card-gold">
        <div class="sc-icon">⏳</div>
        <div class="sc-val" id="stat-belum-val">—</div>
        <div class="sc-subval" id="stat-belum-count">— pesanan</div>
        <div class="sc-label">Belum Ditarik</div>
      </div>
      <div class="stat-card card-green">
        <div class="sc-icon">✅</div>
        <div class="sc-val" id="stat-sudah-val">—</div>
        <div class="sc-subval" id="stat-sudah-count">— pesanan</div>
        <div class="sc-label">Sudah Ditarik</div>
      </div>
      <div class="stat-card card-purple">
        <div class="sc-icon">📦</div>
        <div class="sc-val" id="stat-total-order">—</div>
        <div class="sc-subval">order selesai</div>
        <div class="sc-label">Total Pesanan Selesai</div>
      </div>
      <div class="stat-card card-blue">
        <div class="sc-icon">💵</div>
        <div class="sc-val" id="stat-total-rev">—</div>
        <div class="sc-subval">kumulatif</div>
        <div class="sc-label">Total Pendapatan</div>
      </div>
    </div>

    <!-- TABS -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="pending" onclick="switchTab('pending')">
        Belum Ditarik <span class="tab-count" id="tab-count-pending">0</span>
      </button>
      <button class="tab-btn" data-tab="history" onclick="switchTab('history')">
        Riwayat Terakhir
      </button>
    </div>

    <!-- TAB: PENDING -->
    <div id="tab-pending">
      <!-- Action Bar -->
      <div class="action-bar">
        <h2><i class="bi bi-hourglass-split" style="color:var(--gold)"></i> Antrian Penarikan</h2>
        <span class="selected-info" id="selected-info">
          <i class="bi bi-check-circle-fill"></i>
          <span id="selected-count">0</span> dipilih ·
          <strong id="selected-amount">Rp 0</strong>
        </span>
        <button class="btn-action btn-refresh" id="btn-refresh" onclick="loadData()">
          <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
        <button class="btn-action btn-tarik" id="btn-tarik-selected" disabled onclick="confirmTarik(false)">
          <i class="bi bi-cash-coin"></i> Tarik Dipilih
        </button>
        <button class="btn-action btn-tarik-all" id="btn-tarik-all" onclick="confirmTarik(true)">
          <i class="bi bi-cash-stack"></i> Tarik Semua
        </button>
      </div>

      <!-- Table -->
      <div class="table-card">
        <div class="table-card-head">
          <i class="bi bi-list-check" style="color:var(--gold)"></i>
          <strong style="font-size:.85rem">Daftar Pesanan Belum Ditarik</strong>
          <span id="pending-count-label">— pesanan</span>
        </div>
        <div class="table-wrap">
          <table id="pending-table">
            <thead>
              <tr>
                <th class="col-check">
                  <input type="checkbox" id="check-all" title="Pilih Semua">
                </th>
                <th>Order ID</th>
                <th>Platform</th>
                <th>Tanggal Pesanan</th>
                <th>Tanggal Bayar</th>
                <th>Total Dana (Rp)</th>
              </tr>
            </thead>
            <tbody id="pending-tbody">
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <i class="bi bi-hourglass"></i>
                    <p>Memuat data…</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- TAB: HISTORY -->
    <div id="tab-history" style="display:none">
      <div class="table-card">
        <div class="table-card-head">
          <i class="bi bi-clock-history" style="color:var(--success)"></i>
          <strong style="font-size:.85rem">10 Penarikan Terakhir</strong>
          <span>Riwayat terbaru</span>
        </div>
        <div class="table-wrap">
          <table id="history-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Platform</th>
                <th>Tanggal Bayar</th>
                <th>Dana (Rp)</th>
                <th>Terakhir Update</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="history-tbody">
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <i class="bi bi-clock"></i>
                    <p>Memuat riwayat…</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>

  <footer>
    SIM Withdrawal &nbsp;·&nbsp; CodeIgniter 4 &nbsp;·&nbsp; DB: <code>sim_orders</code> &nbsp;·&nbsp; Akses: <code>CEO Only</code>
  </footer>

</div>

<script>
// ========================================================
// SIM Withdrawal Dashboard — Frontend JS
// ========================================================

const fmt  = n => 'Rp ' + (parseFloat(n)||0).toLocaleString('id-ID', {minimumFractionDigits: 0});
const fmtK = n => {
  const v = parseFloat(n) || 0;
  if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toFixed(1) + ' Jt';
  if (v >= 1_000)     return 'Rp ' + (v / 1_000).toFixed(0) + ' Rb';
  return 'Rp ' + v.toLocaleString('id-ID');
};
const esc  = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
const fmtDt = s => s ? new Date(s).toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}) : '—';

let allPending = [];
let selectedIds = new Set();
let pendingConfirmFn = null;

// ---- TABS ----
function switchTab(name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === name));
  document.getElementById('tab-pending').style.display  = name === 'pending'  ? '' : 'none';
  document.getElementById('tab-history').style.display  = name === 'history'  ? '' : 'none';
}

// ---- TOAST ----
let toastTimer;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  const icon = type === 'success' ? '✅' : '❌';
  t.className = 'show ' + type;
  t.innerHTML = `<span>${icon}</span><span>${esc(msg)}</span>`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = ''; }, 3500);
}

// ---- LOADING ----
function setLoading(visible, text = 'Memproses penarikan…') {
  document.getElementById('loading-text').textContent = text;
  document.getElementById('loading-overlay').classList.toggle('visible', visible);
}

// ---- SUMMARY ----
function renderSummary(s) {
  document.getElementById('stat-belum-val').textContent   = fmtK(s.total_belum_ditarik);
  document.getElementById('stat-belum-count').textContent = (s.jumlah_pending || 0) + ' pesanan';
  document.getElementById('stat-sudah-val').textContent   = fmtK(s.total_sudah_ditarik);
  document.getElementById('stat-sudah-count').textContent = (s.jumlah_ditarik || 0) + ' pesanan';
  document.getElementById('stat-total-order').textContent = (s.total_selesai || 0).toLocaleString('id-ID');
  document.getElementById('stat-total-rev').textContent   = fmtK(s.total_pendapatan);
}

// ---- CHECKBOX LOGIC ----
function updateSelectedUI() {
  const infoEl = document.getElementById('selected-info');
  const btnTarikSel = document.getElementById('btn-tarik-selected');
  const count = selectedIds.size;

  if (count > 0) {
    const total = allPending
      .filter(r => selectedIds.has(r.order_id))
      .reduce((a, r) => a + parseFloat(r.total_amount || 0), 0);
    document.getElementById('selected-count').textContent = count;
    document.getElementById('selected-amount').textContent = fmt(total);
    infoEl.classList.add('visible');
    btnTarikSel.disabled = false;
  } else {
    infoEl.classList.remove('visible');
    btnTarikSel.disabled = true;
  }
}

function onRowCheck(orderId, checked) {
  if (checked) selectedIds.add(orderId);
  else selectedIds.delete(orderId);

  // Update row highlight
  document.querySelectorAll('.pending-row').forEach(tr => {
    tr.classList.toggle('selected', selectedIds.has(tr.dataset.id));
  });

  // Update check-all state
  const all = document.getElementById('check-all');
  if (all) all.indeterminate = selectedIds.size > 0 && selectedIds.size < allPending.length;
  if (all) all.checked = selectedIds.size === allPending.length && allPending.length > 0;

  updateSelectedUI();
}

// ---- RENDER PENDING TABLE ----
function renderPending(rows) {
  allPending = rows;
  selectedIds.clear();
  updateSelectedUI();

  const tbody = document.getElementById('pending-tbody');
  const countLbl = document.getElementById('pending-count-label');
  const tabCount = document.getElementById('tab-count-pending');

  countLbl.textContent = rows.length.toLocaleString('id-ID') + ' pesanan';
  tabCount.textContent = rows.length;

  const all = document.getElementById('check-all');
  if (all) { all.checked = false; all.indeterminate = false; }

  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6">
      <div class="empty-state">
        <i class="bi bi-check-circle" style="color:var(--success)"></i>
        <p>🎉 Semua pesanan sudah ditarik! Tidak ada antrian.</p>
      </div></td></tr>`;
    document.getElementById('btn-tarik-all').disabled = true;
    return;
  }

  document.getElementById('btn-tarik-all').disabled = false;
  tbody.innerHTML = rows.map(r => `
    <tr class="pending-row" data-id="${esc(r.order_id)}">
      <td class="col-check">
        <input type="checkbox" class="row-check" data-id="${esc(r.order_id)}" title="Pilih ${esc(r.order_id)}">
      </td>
      <td><code class="order-code">${esc(r.order_id)}</code></td>
      <td><span class="platform-badge"><i class="bi bi-tiktok"></i>${esc(r.platform)}</span></td>
      <td>${fmtDt(r.create_time)}</td>
      <td>${fmtDt(r.paid_time)}</td>
      <td class="amount">${fmt(r.total_amount)}</td>
    </tr>
  `).join('');

  // Event delegation for checkboxes
  tbody.querySelectorAll('.row-check').forEach(cb => {
    cb.addEventListener('change', () => onRowCheck(cb.dataset.id, cb.checked));
  });

  // Check-all
  const checkAll = document.getElementById('check-all');
  if (checkAll) {
    checkAll.addEventListener('change', () => {
      rows.forEach(r => {
        if (checkAll.checked) selectedIds.add(r.order_id);
        else selectedIds.delete(r.order_id);
      });
      tbody.querySelectorAll('.row-check').forEach(cb => { cb.checked = checkAll.checked; });
      tbody.querySelectorAll('.pending-row').forEach(tr => { tr.classList.toggle('selected', checkAll.checked); });
      updateSelectedUI();
    });
  }
}

// ---- RENDER HISTORY TABLE ----
function renderHistory(rows) {
  const tbody = document.getElementById('history-tbody');
  if (!rows.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada riwayat penarikan.</p></div></td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(r => `
    <tr>
      <td><code class="order-code">${esc(r.order_id)}</code></td>
      <td><span class="platform-badge"><i class="bi bi-tiktok"></i>${esc(r.platform)}</span></td>
      <td>${fmtDt(r.paid_time)}</td>
      <td class="amount">${fmt(r.total_amount)}</td>
      <td style="color:var(--muted);font-size:.75rem">${fmtDt(r.tanggal_update)}</td>
      <td>
        <button class="btn-nav btn-reset-one" data-id="${esc(r.order_id)}" style="font-size:.7rem;padding:.2rem .55rem;color:var(--danger);border-color:rgba(239,68,68,.2)">
          <i class="bi bi-arrow-counterclockwise"></i> Batalkan
        </button>
      </td>
    </tr>
  `).join('');

  tbody.querySelectorAll('.btn-reset-one').forEach(btn => {
    btn.addEventListener('click', () => resetOne(btn.dataset.id));
  });
}

// ---- LOAD DATA ----
async function loadData() {
  document.getElementById('btn-refresh').innerHTML = '<i class="bi bi-arrow-clockwise" style="animation:spin .6s linear infinite"></i> Refresh';
  try {
    const resp = await fetch('<?= base_url('/withdrawal/data') ?>', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await resp.json();
    if (!data.success) throw new Error(data.error || 'Gagal memuat data');
    renderSummary(data.summary || {});
    renderPending(data.pending || []);
    renderHistory(data.history || []);
  } catch (e) {
    showToast('Gagal memuat data: ' + e.message, 'error');
  } finally {
    document.getElementById('btn-refresh').innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
  }
}

// ---- CONFIRM & TARIK ----
function confirmTarik(tarikSemua) {
  const modal   = document.getElementById('confirm-modal');
  const title   = document.getElementById('modal-title');
  const desc    = document.getElementById('modal-desc');
  const amountEl = document.getElementById('modal-amount');

  if (tarikSemua) {
    const totalAmt = allPending.reduce((a, r) => a + parseFloat(r.total_amount||0), 0);
    title.textContent = 'Tarik Semua Dana Pending';
    desc.textContent  = `Tindakan ini akan menandai semua ${allPending.length} pesanan sebagai "Sudah Ditarik". Apakah kamu yakin?`;
    amountEl.textContent = fmt(totalAmt);
    pendingConfirmFn = () => executeTarik(true, []);
  } else {
    const ids  = [...selectedIds];
    const amt  = allPending.filter(r => ids.includes(r.order_id)).reduce((a, r) => a + parseFloat(r.total_amount||0), 0);
    title.textContent = `Tarik ${ids.length} Pesanan Dipilih`;
    desc.textContent  = `Tindakan ini akan menandai ${ids.length} pesanan yang dipilih sebagai "Sudah Ditarik".`;
    amountEl.textContent = fmt(amt);
    pendingConfirmFn = () => executeTarik(false, ids);
  }
  modal.classList.add('visible');
}

document.getElementById('modal-cancel').addEventListener('click', () => {
  document.getElementById('confirm-modal').classList.remove('visible');
});
document.getElementById('modal-confirm').addEventListener('click', () => {
  document.getElementById('confirm-modal').classList.remove('visible');
  if (pendingConfirmFn) pendingConfirmFn();
});

// ---- EXECUTE TARIK ----
async function executeTarik(tarikSemua, orderIds) {
  setLoading(true, tarikSemua ? 'Menarik semua dana…' : `Menarik ${orderIds.length} pesanan…`);
  try {
    const body = tarikSemua ? { tarik_semua: true } : { order_ids: orderIds };
    const resp = await fetch('<?= base_url('/withdrawal/tarik') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(body),
    });
    const data = await resp.json();
    if (!data.success) throw new Error(data.error || 'Gagal menarik dana');
    showToast(data.message, 'success');
    await loadData();
  } catch (e) {
    showToast('Gagal: ' + e.message, 'error');
  } finally {
    setLoading(false);
  }
}

// ---- RESET ONE ----
async function resetOne(orderId) {
  if (!confirm(`Batalkan status penarikan untuk order ${orderId}?\nOrder akan kembali ke "Belum Ditarik".`)) return;
  setLoading(true, 'Membatalkan penarikan…');
  try {
    const resp = await fetch('<?= base_url('/withdrawal/reset') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ order_ids: [orderId] }),
    });
    const data = await resp.json();
    if (!data.success) throw new Error(data.error);
    showToast(data.message, 'success');
    await loadData();
  } catch (e) {
    showToast('Gagal: ' + e.message, 'error');
  } finally {
    setLoading(false);
  }
}

// ---- INIT ----
loadData();
</script>
</body>
</html>
