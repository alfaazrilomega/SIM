<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SIM — Data Karyawan</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
      --bg: #07091a; --bg-card: #0c1230; --bg-card2: #111a3e;
      --border: rgba(100,149,255,.13); --accent: #4f8ef7; --success: #22c55e; --danger: #ef4444; --text: #e2e8f0; --muted: #5a6a8a;
    }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); padding: 2rem; font-size: 14px; }
    .card { background: var(--bg-card); border: 1px solid var(--border); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; }
    .nav { margin-bottom: 2rem; display: flex; gap: 10px; }
    .nav a { padding: 10px 15px; background: var(--bg-card); color: var(--accent); text-decoration: none; border-radius: 8px; border: 1px solid var(--border); }
    .nav a:hover, .nav a.active { background: var(--accent); color: white; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { padding: 10px; border-bottom: 1px solid var(--border); text-align: left; }
    th { background: var(--bg-card2); }
    input, button { padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card2); color: white; margin-bottom:10px; width: 100%; }
    button { background: var(--accent); cursor: pointer; font-weight: bold; border: none; }
    .btn-edit { background: var(--bg-card2); color: var(--accent); border: 1px solid var(--accent); padding: 4px 8px; font-size: 12px; margin-right: 5px; text-decoration: none; border-radius: 4px; }
    .btn-delete { background: var(--bg-card2); color: var(--danger); border: 1px solid var(--danger); padding: 4px 8px; font-size: 12px; text-decoration: none; border-radius: 4px; }
    .alert-success { background: rgba(34,197,94,.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px;}
  </style>
</head>
<body>

<div class="nav">
  <a href="<?= base_url('/hrd/karyawan') ?>" class="active">👥 Data Karyawan</a>
  <a href="<?= base_url('/hrd/absensi') ?>">📅 Absensi</a>
  <a href="<?= base_url('/hrd/penggajian') ?>">💸 Penggajian</a>
</div>

<h1>👥 Kelola Karyawan</h1>

<?php if(session()->getFlashdata('success')): ?>
  <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="grid">
  <div class="card">
    <h2 id="form-title">Registrasi Karyawan</h2>
    <form action="<?= base_url('/hrd/store-karyawan') ?>" method="post" id="form-karyawan">
      <input type="hidden" name="id_karyawan" id="id_karyawan">
      
      <label>Nama Lengkap</label>
      <input type="text" name="nama_karyawan" id="nama_karyawan" required>
      
      <label>Posisi / Job Desk</label>
      <input type="text" name="posisi" id="posisi" placeholder="Contoh: Admin Produksi" required>
      
      <label>Rate Gaji per Jam (IDR)</label>
      <input type="number" name="rate_gaji_per_jam" id="rate_gaji_per_jam" value="10000" required>
      
      <button type="submit" id="btn-submit">Tambah Karyawan</button>
      <button type="button" id="btn-cancel" style="display:none; background:var(--muted); margin-top:5px" onclick="resetForm()">Batal Edit</button>
    </form>
  </div>

  <div class="card">
    <h2>Daftar Karyawan Aktif</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Posisi</th>
          <th>Rate/Jam</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($karyawan as $k): ?>
        <tr>
          <td>#<?= $k['id_karyawan'] ?></td>
          <td><b><?= $k['nama_karyawan'] ?></b></td>
          <td><?= $k['posisi'] ?></td>
          <td style="color:var(--success)">Rp <?= number_format($k['rate_gaji_per_jam'], 0, ',', '.') ?></td>
          <td>
            <button type="button" class="btn-edit" onclick="editKaryawan(<?= htmlspecialchars(json_encode($k)) ?>)">
               <i class="bi bi-pencil"></i>
            </button>
            <form action="<?= base_url('/hrd/delete-karyawan/' . $k['id_karyawan']) ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus karyawan ini? (Data absen dan gaji terkait akan ikut terhapus)')">
              <button type="submit" class="btn-delete" style="width:auto; padding: 4px 8px;"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function editKaryawan(data) {
    document.getElementById('form-title').innerText = 'Edit Karyawan';
    document.getElementById('id_karyawan').value = data.id_karyawan;
    document.getElementById('nama_karyawan').value = data.nama_karyawan;
    document.getElementById('posisi').value = data.posisi;
    document.getElementById('rate_gaji_per_jam').value = data.rate_gaji_per_jam;
    document.getElementById('btn-submit').innerText = 'Simpan Perubahan';
    document.getElementById('btn-cancel').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Registrasi Karyawan';
    document.getElementById('form-karyawan').reset();
    document.getElementById('id_karyawan').value = '';
    document.getElementById('btn-submit').innerText = 'Tambah Karyawan';
    document.getElementById('btn-cancel').style.display = 'none';
}
</script>

</body>
</html>
