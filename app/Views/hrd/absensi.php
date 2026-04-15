<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SIM — HRD Absensi Karyawan</title>
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
    input, select, button { padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card2); color: white; margin-bottom:10px; width: 100%; }
    button { background: var(--accent); cursor: pointer; font-weight: bold; border: none; }
    .btn-edit { background: var(--bg-card2); color: var(--accent); border: 1px solid var(--accent); padding: 4px 8px; font-size: 12px; margin-right: 5px; text-decoration: none; border-radius: 4px; }
    .btn-delete { background: var(--bg-card2); color: var(--danger); border: 1px solid var(--danger); padding: 4px 8px; font-size: 12px; text-decoration: none; border-radius: 4px; }
    .alert-success { background: rgba(34,197,94,.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .alert-danger { background: rgba(239,68,68,.1); border: 1px solid var(--danger); color: #fca5a5; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px;}
  </style>
</head>
<body>

<div class="nav">
  <a href="/hrd/karyawan">👥 Data Karyawan</a>
  <a href="/hrd/absensi" class="active">📅 Absensi</a>
  <a href="/hrd/penggajian">💸 Penggajian</a>
</div>

<h1>📅 Log Absensi Karyawan</h1>

<?php if(session()->getFlashdata('success')): ?>
  <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
  <div class="alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="grid">
  <div class="card">
    <h2 id="form-title">Input Absen Harian</h2>
    <form action="<?= base_url('/hrd/store-absensi') ?>" method="post" id="form-absensi">
      <input type="hidden" name="id_absensi" id="id_absensi">
      
      <label>Karyawan</label>
      <select name="id_karyawan" id="id_karyawan" required>
        <?php foreach($karyawan as $k): ?>
          <option value="<?= $k['id_karyawan'] ?>"><?= $k['nama_karyawan'] ?> (<?= $k['posisi'] ?>)</option>
        <?php endforeach; ?>
      </select>
      
      <label>Tanggal</label>
      <input type="date" name="tanggal" id="tanggal" required value="<?= date('Y-m-d') ?>">
      
      <label>Jam Masuk</label>
      <input type="time" name="jam_masuk" id="jam_masuk" required>
      
      <label>Jam Keluar</label>
      <input type="time" name="jam_keluar" id="jam_keluar" required>
      
      <button type="submit" id="btn-submit">Catat Jam Kerja</button>
      <button type="button" id="btn-cancel" style="display:none; background:var(--muted); margin-top:5px" onclick="resetForm()">Batal Edit</button>
    </form>
  </div>

  <div class="card">
    <h2>Riwayat Jam Kerja</h2>
    <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Karyawan</th>
            <th>Masuk - Keluar</th>
            <th>Total Jam</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($absensi as $a): ?>
          <tr>
            <td><?= date('d M Y', strtotime($a['tanggal'])) ?></td>
            <td><b><?= $a['nama_karyawan'] ?></b></td>
            <td>
                <span style="color:var(--success)"><?= substr($a['jam_masuk'], 0,5) ?></span> - 
                <span style="color:#f87171"><?= substr($a['jam_keluar'], 0,5) ?></span>
            </td>
            <td><?= $a['total_jam_kerja'] ?> Jam</td>
            <td>
                <button type="button" class="btn-edit" onclick="editAbsensi(<?= htmlspecialchars(json_encode($a)) ?>)">
                   <i class="bi bi-pencil"></i>
                </button>
                <form action="<?= base_url('/hrd/delete-absensi/' . $a['id_absensi']) ?>" method="post" style="display:inline" onsubmit="return confirm('Hapus absen ini?')">
                  <button type="submit" class="btn-delete" style="width:auto; padding: 4px 8px;"><i class="bi bi-trash"></i></button>
                </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function editAbsensi(data) {
    document.getElementById('form-title').innerText = 'Edit Absensi';
    document.getElementById('id_absensi').value = data.id_absensi;
    document.getElementById('id_karyawan').value = data.id_karyawan;
    document.getElementById('tanggal').value = data.tanggal;
    document.getElementById('jam_masuk').value = data.jam_masuk;
    document.getElementById('jam_keluar').value = data.jam_keluar;
    document.getElementById('btn-submit').innerText = 'Simpan Perubahan';
    document.getElementById('id_karyawan').disabled = true; // Jangan ganti orang saat edit absen
    
    // Namun kita butuh tetap kirim id_karyawan, jadi kita buat hidden field bayangan atau urus di submit
    // Biar gampang, kita allow ganti orang aja kalau admin mau.
    document.getElementById('id_karyawan').disabled = false; 

    document.getElementById('btn-cancel').style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = 'Input Absen Harian';
    document.getElementById('form-absensi').reset();
    document.getElementById('id_absensi').value = '';
    document.getElementById('btn-submit').innerText = 'Catat Jam Kerja';
    document.getElementById('btn-cancel').style.display = 'none';
}
</script>

</body>
</html>
