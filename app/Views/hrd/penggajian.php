<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SIM — Penggajian Karyawan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #07091a; --bg-card: #0c1230; --bg-card2: #111a3e;
      --border: rgba(100,149,255,.13); --accent: #4f8ef7; --success: #22c55e; --warning: #f59e0b;
      --text: #e2e8f0; --muted: #5a6a8a;
    }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 2rem; font-size: 14px; }
    .card { background: var(--bg-card); border: 1px solid var(--border); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; }
    .nav { margin-bottom: 2rem; display: flex; gap: 10px; }
    .nav a { padding: 10px 15px; background: var(--bg-card); color: var(--accent); text-decoration: none; border-radius: 8px; border: 1px solid var(--border); }
    .nav a:hover, .nav a.active { background: var(--accent); color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; }
    th { background: var(--bg-card2); }
    input, select, button { padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card2); color: white; margin-bottom:10px; width: 100%; }
    button { background: var(--accent); cursor: pointer; font-weight: bold; border: none; }
    .btn-pay { background: var(--success); display: inline-block; padding: 5px 10px; border-radius: 5px; text-decoration: none; color: white; font-size: 12px;}
    .alert-success { background: rgba(34,197,94,.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .alert-error { background: rgba(239,68,68,.1); border: 1px solid #ef4444; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px;}
    .badge { padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-pending { background: rgba(245,158,11,.15); color: var(--warning); border: 1px solid var(--warning); }
    .badge-paid { background: rgba(34,197,94,.15); color: var(--success); border: 1px solid var(--success); }
  </style>
</head>
<body>

<div class="nav">
  <a href="<?= base_url('/hrd/karyawan') ?>">👥 Data Karyawan</a>
  <a href="<?= base_url('/hrd/absensi') ?>">📅 Absensi</a>
  <a href="<?= base_url('/hrd/penggajian') ?>" class="active">💸 Penggajian</a>
</div>

<h1>💸 Hitung & Bayar Gaji</h1>

<?php if(session()->getFlashdata('success')): ?>
  <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
  <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="grid">
  <div class="card">
    <h2>Generate Slip Gaji</h2>
    <p style="color:var(--muted); font-size:12px">Otomatis menghitung total jam disilang dengan Rate/Jam karyawan pada bulan tertentu.</p>
    <form action="<?= base_url('/hrd/generate-gaji') ?>" method="post" style="margin-top:15px">
      <label>Pilih Karyawan</label>
      <select name="id_karyawan" required>
        <?php foreach($karyawan as $k): ?>
          <option value="<?= $k['id_karyawan'] ?>"><?= $k['nama_karyawan'] ?> (<?= $k['posisi'] ?>)</option>
        <?php endforeach; ?>
      </select>
      
      <label>Periode Bulan (YYYY-MM)</label>
      <input type="month" name="periode_bulan" value="<?= date('Y-m') ?>" required>
      
      <button type="submit">Kalkulasi Gaji</button>
    </form>
  </div>

  <div class="card">
    <h2>Riwayat Pembayaran Gaji</h2>
    <table>
      <thead>
        <tr>
          <th>Periode</th>
          <th>Karyawan</th>
          <th>Total Jam</th>
          <th>Total Gaji (Rp)</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($penggajian as $p): ?>
        <tr>
          <td><b><?= $p['periode_bulan'] ?></b></td>
          <td><?= $p['nama_karyawan'] ?></td>
          <td><?= $p['total_jam'] ?> Jam</td>
          <td style="font-weight:bold">Rp <?= number_format($p['total_gaji'], 0, ',', '.') ?></td>
          <td>
            <?php if($p['status_pembayaran'] === 'Belum Dibayar'): ?>
                <span class="badge badge-pending">Menunggu</span>
            <?php else: ?>
                <span class="badge badge-paid">Lunas (<?= date('d/m/y', strtotime($p['tanggal_bayar'])) ?>)</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if($p['status_pembayaran'] === 'Belum Dibayar'): ?>
              <form action="<?= base_url('/hrd/bayar-gaji/' . $p['id_penggajian']) ?>" method="post" style="margin:0">
                <button type="submit" class="btn-pay" style="width:auto;margin:0" onclick="return confirm('Bayar gaji ini menggunakan Uang Kas Operasional?')">Bayar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
