<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM — Import Excel Pesanan</title>
  <meta name="description" content="Import file Excel TikTok OrderSKUList ke database MySQL secara otomatis">
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
      --text:     #e2e8f0;
      --muted:    #5a6a8a;
      --r:        14px;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      min-height: 100vh;
      background: var(--bg);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      font-size: 15px;
    }

    /* ===== ANIMATED GRID BG ===== */
    body::before {
      content: '';
      position: fixed; inset: 0; z-index: 0;
      background:
        linear-gradient(rgba(79,142,247,.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(79,142,247,.04) 1px, transparent 1px);
      background-size: 44px 44px;
      pointer-events: none;
    }
    /* Glow blobs */
    body::after {
      content: '';
      position: fixed; inset: 0; z-index: 0;
      background:
        radial-gradient(ellipse 60% 40% at 20% 20%, rgba(79,142,247,.06) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 80% 80%, rgba(124,92,252,.05) 0%, transparent 60%);
      pointer-events: none;
    }

    /* ===== LAYOUT ===== */
    .page { position: relative; z-index: 1; min-height: 100vh; display: flex; flex-direction: column; }

    /* ===== HEADER ===== */
    header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1.1rem 2rem;
      background: rgba(7,9,26,.8);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(20px);
      position: sticky; top: 0; z-index: 100;
    }
    .logo { display: flex; align-items: center; gap: .75rem; text-decoration: none; color: var(--text); }
    .logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      border-radius: 11px;
      display: flex; align-items: center; justify-content: center;
      font-size: 19px; box-shadow: 0 4px 16px rgba(79,142,247,.3);
    }
    .logo-name { font-weight: 800; font-size: 1.05rem; letter-spacing: -.3px; }
    .logo-sub  { font-size: .68rem; color: var(--muted); }
    .header-pills { display: flex; gap: .6rem; }
    .pill {
      padding: .3rem .75rem;
      border: 1px solid var(--border);
      border-radius: 20px;
      font-size: .72rem;
      color: var(--accent);
      background: rgba(79,142,247,.08);
      text-decoration: none;
      transition: all .2s;
    }
    .pill:hover { background: rgba(79,142,247,.18); color: var(--accent); }

    /* ===== MAIN ===== */
    main {
      flex: 1; padding: 2.5rem 1.5rem;
      max-width: 980px; margin: 0 auto; width: 100%;
    }
    .page-header { margin-bottom: 2rem; }
    .page-header h1 {
      font-size: 1.8rem; font-weight: 800; letter-spacing: -.5px;
      background: linear-gradient(135deg, #fff 30%, #8ab4ff 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }
    .page-header p { color: var(--muted); font-size: .9rem; margin-top: .35rem; }

    /* ===== GRID ===== */
    .grid { display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }

    /* ===== CARDS ===== */
    .card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--r);
      padding: 1.75rem;
      transition: border-color .25s;
    }
    .card:hover { border-color: rgba(79,142,247,.22); }
    .card-label {
      font-size: .7rem; text-transform: uppercase; letter-spacing: 1.2px;
      color: var(--muted); margin-bottom: 1.2rem;
      display: flex; align-items: center; gap: .4rem;
    }

    /* ===== DROP ZONE ===== */
    #drop-zone {
      border: 2px dashed rgba(79,142,247,.28);
      border-radius: var(--r);
      padding: 2.75rem 2rem;
      text-align: center; cursor: pointer;
      background: rgba(79,142,247,.025);
      transition: all .25s; position: relative;
      user-select: none;
    }
    #drop-zone:focus { outline: 2px solid var(--accent); outline-offset: 3px; }
    #drop-zone.drag-over {
      border-color: var(--accent);
      background: rgba(79,142,247,.07);
      transform: scale(1.008);
    }
    #drop-zone.has-file {
      border-color: var(--success);
      background: rgba(34,197,94,.04);
    }
    .dz-icon {
      font-size: 2.8rem; color: var(--accent);
      display: block; margin-bottom: .7rem;
      transition: transform .3s;
    }
    #drop-zone:hover .dz-icon, #drop-zone.drag-over .dz-icon { transform: translateY(-5px) scale(1.06); }
    .dz-title { font-size: 1.05rem; font-weight: 600; margin-bottom: .3rem; }
    .dz-sub   { color: var(--muted); font-size: .82rem; }
    .dz-tag   { font-size: .72rem; color: var(--muted); margin-top: .6rem; }

    #file-picker { display: none; }

    /* File info strip */
    #file-strip {
      display: none;
      align-items: center; gap: .75rem;
      margin-top: 1rem; padding: .75rem 1rem;
      background: rgba(34,197,94,.07);
      border: 1px solid rgba(34,197,94,.2);
      border-radius: 10px;
    }
    #file-strip .fs-icon { font-size: 1.5rem; color: var(--success); flex-shrink: 0; }
    #file-strip .fs-name { font-weight: 600; font-size: .88rem; }
    #file-strip .fs-size { font-size: .75rem; color: var(--muted); }
    #btn-clear {
      margin-left: auto; background: none; border: none;
      color: var(--muted); cursor: pointer; font-size: 1.2rem; line-height: 1;
      padding: .2rem .4rem; border-radius: 6px; transition: color .2s;
    }
    #btn-clear:hover { color: var(--danger); }

    /* ===== IMPORT BUTTON ===== */
    #btn-import {
      width: 100%; margin-top: 1.4rem;
      padding: .95rem 1.5rem;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
      border: none; border-radius: 10px;
      color: #fff; font-size: 1rem; font-weight: 700;
      cursor: pointer; transition: all .25s;
      display: flex; align-items: center; justify-content: center; gap: .6rem;
      box-shadow: 0 4px 20px rgba(79,142,247,.25);
    }
    #btn-import:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 30px rgba(79,142,247,.4);
    }
    #btn-import:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ===== PROGRESS ===== */
    #progress-wrap { display: none; margin-top: 1.4rem; }
    .prog-track { height: 5px; background: rgba(255,255,255,.06); border-radius: 99px; overflow: hidden; margin: .6rem 0; }
    .prog-fill  {
      height: 100%; border-radius: 99px;
      background: linear-gradient(90deg, var(--accent), var(--accent2));
      transition: width .4s ease; width: 0%;
    }
    .prog-label { font-size: .82rem; color: var(--muted); }
    @keyframes pulse { 0%,100%{opacity:.5} 50%{opacity:1} }
    .pulsing { animation: pulse 1.4s ease infinite; }

    /* ===== RESULTS ===== */
    #results-wrap { display: none; margin-top: 1.5rem; }

    /* Stat chips */
    .stat-row { display: grid; grid-template-columns: repeat(4,1fr); gap: .85rem; margin-bottom: 1.4rem; }
    .chip {
      background: var(--bg-card2); border: 1px solid var(--border);
      border-radius: 12px; padding: 1rem; text-align: center;
    }
    .chip .cv { font-size: 1.75rem; font-weight: 800; }
    .chip .cl { font-size: .68rem; color: var(--muted); text-transform: uppercase; letter-spacing: .8px; margin-top: .2rem; }
    .chip.c-total   .cv { color: var(--accent); }
    .chip.c-new     .cv { color: var(--success); }
    .chip.c-updated .cv { color: var(--warning); }
    .chip.c-rows    .cv { color: #a78bfa; }

    /* Error */
    .err-box {
      background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.22);
      border-radius: 10px; padding: 1rem 1.25rem;
      color: #fca5a5; font-size: .86rem; margin-bottom: 1rem; display: none;
    }

    /* Preview table */
    .table-card {
      background: var(--bg-card); border: 1px solid var(--border);
      border-radius: var(--r); overflow: hidden;
    }
    .table-card-head {
      padding: 1rem 1.4rem; border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: .5rem;
    }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    thead th {
      background: var(--bg-card2); padding: .65rem 1rem;
      text-align: left; color: var(--muted); font-weight: 500;
      white-space: nowrap; border-bottom: 1px solid var(--border);
    }
    tbody td {
      padding: .65rem 1rem; border-bottom: 1px solid rgba(255,255,255,.04);
      white-space: nowrap;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: rgba(79,142,247,.04); }

    /* Action badges */
    .badge {
      display: inline-flex; align-items: center; gap: .3rem;
      padding: .22rem .6rem; border-radius: 20px; font-size: .7rem; font-weight: 700;
    }
    .badge-new     { background: rgba(34,197,94,.12); color: #4ade80; }
    .badge-updated { background: rgba(245,158,11,.12); color: #fbbf24; }

    /* Status colors */
    .s-selesai { color: #4ade80; }
    .s-dikirim { color: #60a5fa; }
    .s-batal   { color: #f87171; }
    .s-other   { color: var(--muted); }

    /* ===== SIDEBAR CARDS ===== */
    .info-list { list-style: none; }
    .info-list li {
      display: flex; gap: .65rem; padding: .6rem 0;
      border-bottom: 1px solid var(--border); font-size: .82rem;
    }
    .info-list li:last-child { border-bottom: none; }
    .ii { font-size: .95rem; flex-shrink: 0; margin-top: .1rem; }
    .it strong { display: block; font-size: .8rem; color: var(--text); }
    .it span   { font-size: .75rem; color: var(--muted); }

    pre.cmd {
      background: rgba(0,0,0,.5); border: 1px solid var(--border);
      border-radius: 8px; padding: .8rem 1rem;
      font-size: .75rem; color: #93c5fd; overflow-x: auto; margin: .6rem 0;
    }

    /* ===== FOOTER ===== */
    footer {
      text-align: center; padding: 1.2rem;
      border-top: 1px solid var(--border);
      color: var(--muted); font-size: .75rem;
    }
    footer code { color: var(--accent); }
  </style>
</head>
<body>
<div class="page">

  <!-- HEADER -->
  <header>
    <a href="<?= base_url('/import') ?>" class="logo">
      <div class="logo-icon">📊</div>
      <div>
        <div class="logo-name">SIM Import Tool</div>
        <div class="logo-sub">Sales Information Management</div>
      </div>
    </a>
    <div class="header-pills">
      <a href="<?= base_url('/withdrawal') ?>" class="pill" style="color:#f59e0b; border-color:rgba(245,158,11,.4); background:rgba(245,158,11,.08); font-weight:600;"><i class="bi bi-shield-lock-fill me-1"></i>Dashboard Penarikan (CEO)</a>
      <span class="pill" style="pointer-events:none"><i class="bi bi-tiktok me-1"></i>TikTok OrderSKUList</span>
    </div>
  </header>

  <!-- MAIN -->
  <main>
    <div class="page-header">
      <h1>Import Excel Pesanan</h1>
      <p>Upload file <strong>.xlsx</strong> export TikTok Seller Center. Data di-<em>upsert</em> otomatis — duplikat diupdate, <code>status_penarikan</code> CEO tetap aman.</p>
    </div>

    <div class="grid">

      <!-- ====== LEFT: Upload Panel ====== -->
      <div>
        <div class="card">
          <p class="card-label"><i class="bi bi-cloud-arrow-up"></i>Upload File Excel</p>

          <!-- Drop Zone -->
          <div id="drop-zone" tabindex="0" role="button"
               aria-label="Klik atau seret file Excel .xlsx ke area ini">
            <i class="bi bi-file-earmark-spreadsheet dz-icon"></i>
            <div class="dz-title">Seret & Lepas file di sini</div>
            <div class="dz-sub">atau <u style="color:var(--accent)">klik untuk memilih file</u></div>
            <div class="dz-tag">Format: .xlsx &nbsp;·&nbsp; Maks: 50 MB</div>
          </div>
          <input type="file" id="file-picker" accept=".xlsx">

          <!-- File Strip -->
          <div id="file-strip">
            <i class="bi bi-file-earmark-excel-fill fs-icon"></i>
            <div>
              <div id="fs-name" class="fs-name"></div>
              <div id="fs-size" class="fs-size"></div>
            </div>
            <button id="btn-clear" title="Hapus file">&times;</button>
          </div>

          <!-- Import Button -->
          <button id="btn-import" disabled>
            <i class="bi bi-database-up" id="btn-icon"></i>
            <span id="btn-text">Pilih file terlebih dahulu</span>
          </button>

          <!-- Progress -->
          <div id="progress-wrap">
            <div class="prog-label pulsing" id="prog-label">Membaca file Excel…</div>
            <div class="prog-track">
              <div class="prog-fill" id="prog-fill"></div>
            </div>
          </div>
        </div><!-- /.card -->

        <!-- Results -->
        <div id="results-wrap">

          <!-- Stat Chips -->
          <div class="stat-row" id="stat-row"></div>

          <!-- Error Box -->
          <div class="err-box" id="err-box"></div>

          <!-- Preview Table -->
          <div class="table-card" id="preview-card">
            <div class="table-card-head">
              <i class="bi bi-table" style="color:var(--accent)"></i>
              <span class="card-label" style="margin:0">Preview (10 Pesanan Pertama)</span>
            </div>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Produk (Sample)</th>
                    <th>Items</th>
                    <th>Total (Rp)</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody id="preview-tbody"></tbody>
              </table>
            </div>
          </div>

        </div><!-- /#results-wrap -->
      </div><!-- /LEFT -->

      <!-- ====== RIGHT: Info Sidebar ====== -->
      <div>
        <!-- Business Rules -->
        <div class="card" style="margin-bottom:1rem">
          <p class="card-label"><i class="bi bi-shield-check"></i>Business Rules Aktif</p>
          <ul class="info-list">
            <li>
              <i class="bi bi-arrow-repeat ii" style="color:var(--warning)"></i>
              <div class="it"><strong>Upsert by Order ID</strong>
              <span>Sudah ada → UPDATE. Baru → INSERT. Tanpa duplikat.</span></div>
            </li>
            <li>
              <i class="bi bi-lock-fill ii" style="color:var(--success)"></i>
              <div class="it"><strong>Status Penarikan CEO Aman</strong>
              <span><code>status_penarikan</code> TIDAK direset saat re-import.</span></div>
            </li>
            <li>
              <i class="bi bi-funnel-fill ii" style="color:var(--accent)"></i>
              <div class="it"><strong>Laporan filter "Selesai"</strong>
              <span>View laporan hanya tampilkan pesanan berstatus <em>Selesai</em>.</span></div>
            </li>
            <li>
              <i class="bi bi-tags-fill ii" style="color:#c084fc"></i>
              <div class="it"><strong>Kombinasi Produk + Variasi</strong>
              <span>Variasi "Default" → cek tabel <code>product_mapping</code>. Non-Default → nama + variasi.</span></div>
            </li>
            <li>
              <i class="bi bi-recycle ii" style="color:var(--warning)"></i>
              <div class="it"><strong>Detail Rebuild Otomatis</strong>
              <span>Item lama dihapus & diganti versi terbaru setiap re-import.</span></div>
            </li>
          </ul>
        </div>

        <!-- Columns -->
        <div class="card" style="margin-bottom:1rem">
          <p class="card-label"><i class="bi bi-columns"></i>Kolom yang Dibaca</p>
          <ul class="info-list">
            <?php
            $cols = [
              ['Order ID',              'Primary Key pesanan (18-digit TikTok ID)'],
              ['Order Status',          'Selesai / Dikirim / Dibatalkan'],
              ['Product Name',          'Nama produk verbatim dari Excel'],
              ['Variation',             'Variasi SKU (incl. "Default")'],
              ['Quantity',              'Jumlah unit tiap SKU'],
              ['SKU Settlement Amount', 'Jumlah yang diterima seller'],
              ['Create Time & Paid Time', 'Waktu order & pembayaran'],
            ];
            foreach ($cols as [$title, $desc]):
            ?>
            <li>
              <i class="bi bi-check-circle-fill ii" style="color:var(--success)"></i>
              <div class="it"><strong><?= esc($title) ?></strong><span><?= esc($desc) ?></span></div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Setup -->
        <div class="card">
          <p class="card-label"><i class="bi bi-terminal"></i>Setup Pertama Kali</p>
          <div style="font-size:.8rem;color:var(--muted);line-height:1.9">
            <div>1️⃣ Jalankan database migration:</div>
            <pre class="cmd">php spark migrate</pre>
            <div>2️⃣ Server development:</div>
            <pre class="cmd">php spark serve</pre>
            <div style="margin-top:.5rem">3️⃣ Buka <a href="<?= base_url('/import') ?>" style="color:var(--accent)"><?= base_url('/import') ?></a></div>
          </div>
        </div>

      </div><!-- /RIGHT -->
    </div><!-- /.grid -->
  </main>

  <footer>
    SIM Import Tool &nbsp;·&nbsp; CodeIgniter 4 &nbsp;·&nbsp; Platform: <code>TikTok OrderSKUList</code> &nbsp;·&nbsp; DB: <code>sim_orders</code>
  </footer>
</div>

<script>
// ====================================================
// SIM Import Tool — Frontend JS
// ====================================================
const dropZone  = document.getElementById('drop-zone');
const picker    = document.getElementById('file-picker');
const fileStrip = document.getElementById('file-strip');
const fsName    = document.getElementById('fs-name');
const fsSize    = document.getElementById('fs-size');
const btnClear  = document.getElementById('btn-clear');
const btnImport = document.getElementById('btn-import');
const btnIcon   = document.getElementById('btn-icon');
const btnText   = document.getElementById('btn-text');
const progWrap  = document.getElementById('progress-wrap');
const progLabel = document.getElementById('prog-label');
const progFill  = document.getElementById('prog-fill');
const resultsWrap = document.getElementById('results-wrap');
const statRow   = document.getElementById('stat-row');
const errBox    = document.getElementById('err-box');
const prevTbody = document.getElementById('preview-tbody');

let activeFile = null;

const fmtSize = b => b < 1e6 ? (b/1024).toFixed(1)+' KB' : (b/1e6).toFixed(2)+' MB';
const fmtRp   = n => 'Rp ' + Number(n).toLocaleString('id-ID');
const esc     = s => String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

function setFile(f) {
  if (!f) return;
  if (!f.name.toLowerCase().endsWith('.xlsx')) {
    alert('❌ Hanya file .xlsx yang diizinkan.\n\nFile kamu: ' + f.name);
    return;
  }
  activeFile = f;
  fsName.textContent = f.name;
  fsSize.textContent = fmtSize(f.size);
  fileStrip.style.display = 'flex';
  dropZone.classList.add('has-file');
  dropZone.classList.remove('drag-over');
  btnImport.disabled = false;
  btnText.textContent = 'Import ke Database';
}

function clearFile() {
  activeFile = null;
  picker.value = '';
  fileStrip.style.display = 'none';
  dropZone.classList.remove('has-file','drag-over');
  btnImport.disabled = true;
  btnText.textContent = 'Pilih file terlebih dahulu';
  resultsWrap.style.display = 'none';
  progWrap.style.display = 'none';
  progFill.style.width = '0%';
}

// Drop zone interactions
dropZone.addEventListener('click',    () => picker.click());
dropZone.addEventListener('keydown',  e => { if(e.key==='Enter'||e.key===' ') picker.click(); });
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave',() => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
  e.preventDefault();
  if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
});
picker.addEventListener('change', () => { if(picker.files[0]) setFile(picker.files[0]); });
btnClear.addEventListener('click', e => { e.stopPropagation(); clearFile(); });

// Progress animation
let progTimer = null;
const startProgress = () => {
  let pct = 0;
  progFill.style.width = '0%';
  progFill.style.background = 'linear-gradient(90deg,var(--accent),var(--accent2))';
  progWrap.style.display = 'block';
  progLabel.classList.add('pulsing');
  progTimer = setInterval(() => {
    pct = Math.min(pct + Math.random()*6, 85);
    progFill.style.width = pct + '%';
    progLabel.textContent =
      pct < 25 ? 'Membaca file Excel…' :
      pct < 55 ? 'Memproses baris data…' :
                 'Mengupdate database…';
  }, 320);
};
const endProgress = (ok = true) => {
  clearInterval(progTimer);
  progFill.style.width = '100%';
  progLabel.classList.remove('pulsing');
  if (!ok) progFill.style.background = 'var(--danger)';
  progLabel.textContent = ok ? '✅ Selesai!' : '❌ Import gagal';
  setTimeout(() => {
    progWrap.style.display = 'none';
    progFill.style.width = '0%';
    progFill.style.background = '';
    progLabel.classList.add('pulsing');
  }, 2000);
};

function statusCls(s) {
  const l = (s||'').toLowerCase();
  if (l.includes('selesai')) return 's-selesai';
  if (l.includes('kirim'))   return 's-dikirim';
  if (l.includes('batal') || l.includes('cancel')) return 's-batal';
  return 's-other';
}

function renderResults(d) {
  statRow.innerHTML = `
    <div class="chip c-total"><div class="cv">${d.total_orders.toLocaleString()}</div><div class="cl">Total Order</div></div>
    <div class="chip c-new"><div class="cv">${d.inserted.toLocaleString()}</div><div class="cl">Baru (Insert)</div></div>
    <div class="chip c-updated"><div class="cv">${d.updated.toLocaleString()}</div><div class="cl">Diperbarui</div></div>
    <div class="chip c-rows"><div class="cv">${d.total_rows.toLocaleString()}</div><div class="cl">Baris Dibaca</div></div>
  `;

  // Errors
  if (d.errors && d.errors.length) {
    errBox.style.display = 'block';
    errBox.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i><strong>Error:</strong> '
      + d.errors.map(e => '<div>' + esc(e) + '</div>').join('');
  } else {
    errBox.style.display = 'none';
  }

  // Preview table
  prevTbody.innerHTML = '';
  (d.preview||[]).forEach(row => {
    const isNew = row.action === 'INSERTED';
    prevTbody.insertAdjacentHTML('beforeend', `
      <tr>
        <td><code style="font-size:.73rem;color:#93c5fd">${esc(row.order_id)}</code></td>
        <td><span class="${statusCls(row.status_pesanan)}">${esc(row.status_pesanan)}</span></td>
        <td style="max-width:170px;overflow:hidden;text-overflow:ellipsis" title="${esc(row.produk_sample)}">${esc(row.produk_sample)}</td>
        <td style="text-align:center">${row.items}</td>
        <td>${fmtRp(row.total_amount)}</td>
        <td><span class="badge ${isNew ? 'badge-new':'badge-updated'}">
          <i class="bi ${isNew ? 'bi-plus-circle':'bi-arrow-repeat'}"></i>${row.action}
        </span></td>
      </tr>`);
  });

  resultsWrap.style.display = 'block';
  setTimeout(() => resultsWrap.scrollIntoView({behavior:'smooth', block:'start'}), 100);
}

// ---- Main Import ----
btnImport.addEventListener('click', async () => {
  if (!activeFile) return;
  btnImport.disabled = true;
  btnIcon.className  = 'bi bi-hourglass-split';
  btnText.textContent = 'Mengimport…';
  resultsWrap.style.display = 'none';
  startProgress();

  const fd = new FormData();
  fd.append('excel_file', activeFile);

  try {
    const resp = await fetch('<?= base_url('/import/process') ?>', {
      method: 'POST', body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    const data = await resp.json();

    if (data.success) {
      endProgress(true);
      renderResults(data);
    } else {
      endProgress(false);
      errBox.style.display = 'block';
      errBox.innerHTML = '<i class="bi bi-exclamation-triangle"></i> <strong>Error:</strong> '
        + esc(data.error || 'Terjadi kesalahan.')
        + (data.hint ? '<br><small style="opacity:.8">' + esc(data.hint) + '</small>' : '');
      statRow.innerHTML = '';
      prevTbody.innerHTML = '';
      resultsWrap.style.display = 'block';
    }
  } catch (err) {
    endProgress(false);
    alert('Request gagal: ' + err.message);
  } finally {
    btnImport.disabled = false;
    btnIcon.className  = 'bi bi-database-up';
    btnText.textContent = 'Import ke Database';
  }
});
</script>
</body>
</html>
