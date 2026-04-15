<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>
<h1>💰 Dashboard Finance & Ledger Operational</h1>
<p style="color:var(--muted)">Sistem Pencatatan Kas Masuk & Keluar (Support Operasional + Laporan Keuangan CEO)</p>

<?php if(session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card" style="text-align: center; max-width: 400px; margin: 0 auto 2rem;">
  <h2 style="color: var(--muted); font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px;">Total Saldo Berjalan (Live)</h2>
  <div style="font-size: 2.8rem; color: var(--accent); font-weight: 800;">Rp <?= number_format($saldo, 2, ',', '.') ?></div>
</div>

<div class="grid-2">
  <!-- FORM INPUT -->
  <div class="card" style="border-top: 4px solid var(--accent);">
    <h2><i class="bi bi-pencil-square"></i> Jurnal Transaksi Baru</h2>
    <hr style="border-color: var(--border); margin-bottom: 1.5rem;">
    
    <form action="<?= base_url('/finance/store') ?>" method="post" id="formKas">
      <label>Tanggal Transaksi</label>
      <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>">
      
      <label>Tipe Arus Kas</label>
      <select name="tipe_transaksi" id="tipe_transaksi" required>
        <option value="" selected disabled>-- Pilih Identitas Arus --</option>
        <option value="Pengeluaran">🔴 Pengeluaran (Kas Keluar)</option>
        <option value="Pemasukan">🟢 Pemasukan (Kas Masuk)</option>
      </select>

      <label>Kategori (Klasifikasi Ledger)</label>
      <select name="kategori" id="kategori" required disabled>
        <option value="">(Pilih Tipe Kas Terlebih Dahulu)</option>
      </select>

      <!-- BLOK DINAMIS UNTUK GAJI (GOD-TIER UI) -->
      <div id="blok_gaji" style="display: none; background: rgba(79, 142, 247, 0.05); padding: 15px; border-radius: 8px; border: 1px dashed var(--accent); margin-bottom: 15px;">
        <label style="color: var(--accent);"><i class="bi bi-person-lines-fill"></i> Pilih Tagihan Slip Gaji Aktif</label>
        <select name="id_penggajian" id="id_penggajian">
          <option value="" selected disabled>-- Pilih Karyawan yang belum dilunasi gajinya --</option>
          <?php foreach ($unpaidGaji as $g): ?>
            <option value="<?= $g['id_penggajian'] ?>" data-nominal="<?= $g['total_gaji'] ?>">
               [<?= $g['periode_bulan'] ?>] <?= $g['nama_karyawan'] ?> - Rp <?= number_format($g['total_gaji'], 0, ',', '.') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <small style="color: var(--muted);"><i class="bi bi-info-circle"></i> Membayar Gaji lewat form ini akan secara otomatis menutup dan merubah status di tabel HRD menjadi "Sudah Dibayar".</small>
      </div>

      <!-- BLOK STANDAR -->
      <div id="blok_manual">
        <label>Keterangan / Catatan Transaksi <span style="color:var(--danger)">*</span></label>
        <input type="text" name="keterangan" id="keterangan" placeholder="Contoh: Bayar listrik pabrik bulan April">

        <label>Nominal Pembayaran (IDR) <span style="color:var(--danger)">*</span></label>
        <input type="number" name="nominal" id="nominal" placeholder="Contoh: 1500000" min="0">
      </div>

      <button type="submit" id="btnSimpan" style="font-size: 1.1rem; margin-top: 10px;" disabled><i class="bi bi-save"></i> Eksekusi Transaksi ke Ledger</button>
    </form>
  </div>

  <!-- TABEL RIWAYAT TRANSAKSI -->
  <div class="card">
    <h2><i class="bi bi-journal-text"></i> Riwayat Buku Besar (Ledger)</h2>
    <hr style="border-color: var(--border); margin-bottom: 1rem;">
    <div style="overflow-y: auto; max-height: 500px; padding-right: 10px;">
      <table>
        <tr>
          <th>Tanggal</th>
          <th>Arus Kas</th>
          <th>Kategori Detail</th>
          <th style="text-align: right;">Nominal</th>
        </tr>
        <?php foreach($transaksi as $t): ?>
        <tr>
          <td style="white-space: nowrap;"><?= date('d M Y', strtotime($t['tanggal'])) ?></td>
          <td>
            <?php if($t['tipe_transaksi'] == 'Pemasukan'): ?>
              <span style="color: var(--success); font-weight: 600;"><i class="bi bi-arrow-down-left-circle"></i> Masuk</span>
            <?php else: ?>
              <span style="color: var(--danger); font-weight: 600;"><i class="bi bi-arrow-up-right-circle"></i> Keluar</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="font-weight: 600;"><?= $t['kategori'] ?></div>
            <div style="font-size: 0.85em; color: var(--muted);"><?= $t['keterangan'] ?></div>
          </td>
          <td style="text-align: right; font-weight: bold; font-family: monospace; font-size: 1.1em;">
            Rp <?= number_format($t['nominal'], 0, ',', '.') ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php if(empty($transaksi)): ?>
         <div style="text-align: center; color: var(--muted); padding: 2rem;">Belum ada sejarah pencatatan arus kas.</div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
  const elTipe = document.getElementById('tipe_transaksi');
  const elKategori = document.getElementById('kategori');
  const elBlokGaji = document.getElementById('blok_gaji');
  const elBlokManual = document.getElementById('blok_manual');
  const elIdPenggajian = document.getElementById('id_penggajian');
  const elNominal = document.getElementById('nominal');
  const elKeterangan = document.getElementById('keterangan');
  const elBtn = document.getElementById('btnSimpan');

  // Pisahkan array dropdown berdasar tipe! (Inilah yg diminta oleh CEO untuk laporan data mentah)
  const katPemasukan = [
    {val: 'Pendapatan Penjualan', text: 'Pendapatan Penjualan Bumbu'},
    {val: 'Pencairan / Withdrawal Platform', text: 'Pencairan Titipan Uang (Tiktok/Shopee)'},
    {val: 'Suntikan Modal / Lainnya', text: 'Lainnya'}
  ];

  const katPengeluaran = [
    {val: 'Listrik', text: 'Bayar Listrik / Air'},
    {val: 'Maintenance Mesin', text: 'Bayar Servis Mesin'},
    {val: 'Biaya Gaji / Upah Karyawan', text: '⚡ Bayar Gaji Karyawan (Interkoneksi Sistem)'},
    {val: 'Bahan Baku', text: 'Belanja Bahan Baku (Rekan Tim Inventory)'},
    {val: 'Operasional Lainnya', text: 'Operasional Harian Lainnya'}
  ];

  function renderOptions(array) {
    elKategori.innerHTML = '<option value="" selected disabled>-- Menunggu Pilihan Kategori --</option>';
    array.forEach(item => {
      elKategori.innerHTML += `<option value="${item.val}">${item.text}</option>`;
    });
    elKategori.disabled = false;
  }

  elTipe.addEventListener('change', function() {
    elBtn.disabled = true;
    elBlokGaji.style.display = 'none';
    elBlokManual.style.display = 'block';
    
    // Reset Data Form Security
    elIdPenggajian.value = '';
    elNominal.value = '';
    elNominal.readOnly = false;
    elKeterangan.value = '';
    elKeterangan.readOnly = false;

    if(this.value === 'Pemasukan') {
      renderOptions(katPemasukan);
    } else if(this.value === 'Pengeluaran') {
      renderOptions(katPengeluaran);
    }
  });

  elKategori.addEventListener('change', function() {
    elBtn.disabled = false;

    if(this.value === 'Biaya Gaji / Upah Karyawan') {
      // Mode God-Tier: Buka portal koneksi ke tabel Gaji di backend. Matikan form manual
      elBlokGaji.style.display = 'block';
      elNominal.readOnly = true;
      elKeterangan.readOnly = true;
      elIdPenggajian.required = true;
      elNominal.required = false; 
      
      // User tidak usah repot ngetik keterangan. Otomatisasi data kotor.
      elNominal.value = '';
      elKeterangan.value = 'Auto-synchronized by HRD Module System';
    } else {
      // Mode Reguler Kas Biasa
      elBlokGaji.style.display = 'none';
      elNominal.readOnly = false;
      elKeterangan.readOnly = false;
      elIdPenggajian.required = false;
      elNominal.required = true;

      // Beri helper prefix untuk kategori rekan tim
      if(this.value === 'Bahan Baku') {
         elKeterangan.value = '[INV] Pembelian: ';
      } else if (this.value === 'Pencairan / Withdrawal Platform') {
         elKeterangan.value = '[WD] Tarik dana dari ';
      } else {
         elKeterangan.value = '';
      }
    }
  });

  // Kosmetik: Ketika user memilih gaji siapa yang mau dibayar, angka "Nominal" menyala (hanya buat preview visual frontend)
  elIdPenggajian.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const nom = selectedOption.getAttribute('data-nominal');
    if(nom) {
      elNominal.value = nom;
    }
  });
</script>
<?= $this->endSection() ?>
