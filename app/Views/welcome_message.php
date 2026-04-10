<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIM — Sales Information Management</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg:       #07091a;
      --bg-card:  #0c1230;
      --border:   rgba(100,149,255,.13);
      --accent:   #4f8ef7;
      --gold:     #f59e0b;
      --text:     #e2e8f0;
      --muted:    #5a6a8a;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; background: var(--bg); color: var(--text);
      font-family: 'Inter', sans-serif; display: flex; flex-direction: column;
      align-items: center; justify-content: center; position: relative;
    }
    body::before {
      content: ''; position: fixed; inset: 0; z-index: 0;
      background: linear-gradient(rgba(79,142,247,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(79,142,247,.04) 1px, transparent 1px);
      background-size: 44px 44px; pointer-events: none;
    }
    body::after {
      content: ''; position: fixed; inset: 0; z-index: 0;
      background: radial-gradient(circle at 50% 50%, rgba(79,142,247,.05) 0%, transparent 60%);
      pointer-events: none;
    }
    .container { position: relative; z-index: 1; text-align: center; max-width: 800px; padding: 2rem; width: 100%; }
    .logo-icon {
      width: 70px; height: 70px; margin: 0 auto 1.5rem;
      background: linear-gradient(135deg, var(--accent), #7c5cfc);
      border-radius: 18px; display: flex; align-items: center; justify-content: center;
      font-size: 32px; box-shadow: 0 8px 32px rgba(79,142,247,.35);
    }
    h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: .5rem; letter-spacing: -.5px; }
    p { color: var(--muted); font-size: 1.05rem; margin-bottom: 3rem; }
    .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
    @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
    .card {
      background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px;
      padding: 2.5rem 2rem; text-decoration: none; color: var(--text);
      transition: all .25s; text-align: left; display: flex; flex-direction: column;
    }
    .card:hover { transform: translateY(-5px); border-color: rgba(79,142,247,.3); box-shadow: 0 12px 40px rgba(0,0,0,.4); }
    .card-icon { font-size: 2.5rem; margin-bottom: 1rem; color: var(--accent); }
    .card.gold:hover { border-color: rgba(245,158,11,.3); }
    .card.gold .card-icon { color: var(--gold); }
    .card-title { font-size: 1.25rem; font-weight: 700; margin-bottom: .5rem; }
    .card-desc { color: var(--muted); font-size: .9rem; line-height: 1.6; }
    .footer { position: absolute; bottom: 2rem; color: var(--muted); font-size: .8rem; }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-icon">📊</div>
    <h1>SIM Launchpad</h1>
    <p>Sales Information Management System</p>

    <div class="grid">
      <a href="<?= base_url('/import') ?>" class="card">
        <div class="card-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
        <div class="card-title">Import Data Pesanan</div>
        <div class="card-desc">Upload dan sinkronisasi data transaksi Excel dari TikTok Shop (OrderSKUList) ke database pusat.</div>
      </a>
      
      <a href="<?= base_url('/withdrawal') ?>" class="card gold">
        <div class="card-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <div class="card-title">Dashboard Penarikan</div>
        <div class="card-desc">Hak akses eksklusif CEO. Kelola riwayat pendapatan dan penarikan dana dari pesanan yang berstatus selesai.</div>
      </a>
    </div>
  </div>
  <div class="footer">SIM System &copy; <?= date('Y') ?> &nbsp;&middot;&nbsp; <code>sim_orders</code></div>
</body>
</html>
