<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';

require_warga_login();

$pdo = db();
$warga_id   = (int)$_SESSION['warga_id'];
$warga_nama = $_SESSION['warga_nama'] ?? '';

// ambil data warga untuk info
$st = $pdo->prepare('SELECT * FROM warga WHERE id = ? LIMIT 1');
$st->execute([$warga_id]);
$w = $st->fetch();

// ambil nominal iuran
$nominal = (int)($pdo->query("SELECT nominal_iuran FROM settings WHERE id=1")->fetch()['nominal_iuran'] ?? 0);

// riwayat pembayaran warga ini
$st = $pdo->prepare('
  SELECT p.*
  FROM pembayaran p
  WHERE p.warga_id = ?
  ORDER BY p.tahun DESC, p.bulan DESC
');
$st->execute([$warga_id]);
$riwayat = $st->fetchAll();

// helper nama bulan
$bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tagihan Saya - Portal Warga</title>
  <link rel="stylesheet" href="<?=base_url('assets/style.css')?>">
  <style>
    .header-warga{
      background:#111827;padding:12px 16px;display:flex;
      justify-content:space-between;align-items:center;
      border-bottom:1px solid #334155;
    }
    .header-warga h1{margin:0;font-size:18px}
    .header-warga span{font-size:14px;color:#94a3b8}
    .btn-logout{
      background:#ef4444;color:#f9fafb;padding:6px 10px;border-radius:8px;
      text-decoration:none;font-size:13px;
    }
    .card{
      background:#111827;margin:20px auto;padding:16px;border-radius:12px;
      max-width:980px;box-shadow:0 8px 24px rgba(0,0,0,.5);
    }
    .card h2{margin-top:0;font-size:18px}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{padding:8px 10px;border-bottom:1px solid #334155;font-size:14px}
    th{text-align:left}
    .muted{color:#94a3b8;font-size:13px}
    .badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:12px}
    .badge-paid{background:#22c55e33;color:#4ade80}
    .badge-unpaid{background:#f9731633;color:#fdba74}
    input,select{
      background:#020617;border:1px solid #334155;border-radius:8px;
      color:#e2e8f0;padding:8px;
    }
    button{
      padding:9px 14px;border-radius:8px;border:0;background:#38bdf8;
      color:#0f172a;font-weight:600;cursor:pointer;
    }
    .bukti-link a{color:#38bdf8;font-size:13px}
  </style>
</head>
<body>

<div class="header-warga">
  <div>
    <h1>Portal Warga - Tagihan Saya</h1>
    <span><?=esc($warga_nama)?></span>
  </div>
  <div>
    <a href="<?=base_url('/auth/login.php')?>" class="btn-logout">Logout</a>
  </div>
</div>

<div class="card">
  <h2>Identitas Warga</h2>
  <?php if($w): ?>
    <p><strong>Nama:</strong> <?=esc($w['nama'])?></p>
    <p><strong>NIK:</strong> <?=esc($w['nik'] ?: '-')?></p>
    <p><strong>Alamat:</strong> <?=esc($w['alamat'] ?: '-')?></p>
    <p class="muted">Nominal iuran per bulan: Rp <?=number_format($nominal,0,',','.')?></p>
  <?php else: ?>
    <p>Data warga tidak ditemukan.</p>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Upload Bukti Pembayaran</h2>
  <p class="muted">Silakan upload bukti transfer / pembayaran untuk bulan yang ingin dibayar.</p>

  <form action="<?=base_url('warga/upload_bukti.php')?>" method="post" enctype="multipart/form-data">
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
      <div>
        <label>Tahun</label><br>
        <input type="number" name="tahun" value="<?=date('Y')?>" required>
      </div>
      <div>
        <label>Bulan</label><br>
        <select name="bulan" required>
          <?php for($b=1;$b<=12;$b++): ?>
            <option value="<?=$b?>" <?=$b==(int)date('n')?'selected':''?>><?=$bulanNama[$b]?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div>
        <label>Tanggal Bayar</label><br>
        <input type="date" name="tanggal_bayar" value="<?=date('Y-m-d')?>" required>
      </div>
      <div>
        <label>Jumlah (Rp)</label><br>
        <input type="number" name="jumlah" value="<?=$nominal?>" min="0" required>
      </div>
      <div>
        <label>Bukti (JPG/PNG)</label><br>
        <input type="file" name="bukti" accept="image/*" required>
      </div>
      <div style="align-self:flex-end">
        <button type="submit">Kirim Bukti</button>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <h2>Riwayat Pembayaran Saya</h2>
  <?php if(empty($riwayat)): ?>
    <p class="muted">Belum ada data pembayaran.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Tahun</th>
          <th>Bulan</th>
          <th>Tanggal Bayar</th>
          <th>Jumlah</th>
          <th>Metode</th>
          <th>Bukti</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($riwayat as $p): ?>
          <tr>
            <td><?=esc($p['tahun'])?></td>
            <td><?=$bulanNama[(int)$p['bulan']] ?? $p['bulan']?></td>
            <td><?=esc($p['tanggal_bayar'])?></td>
            <td>Rp <?=number_format($p['jumlah'],0,',','.')?></td>
            <td><?=esc($p['metode'])?></td>
            <td class="bukti-link">
              <?php if(!empty($p['bukti_path'])): ?>
                <a href="<?=base_url($p['bukti_path'])?>" target="_blank">Lihat Bukti</a>
              <?php else: ?>
                <span class="muted">Belum ada</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

</body>
</html>
