<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/functions.php';

// Kalau sudah login sebagai warga, langsung ke halaman tagihan
if (is_warga_logged_in()) {
  header('Location: '.base_url('warga/tagihan_saya.php'));
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nik = trim($_POST['nik'] ?? '');

  if ($nik === '') {
    $error = 'NIK wajib diisi.';
  } else {
    $st = db()->prepare('SELECT * FROM warga WHERE nik = ? AND aktif = 1 LIMIT 1');
    $st->execute([$nik]);
    $w = $st->fetch();

    if ($w) {
      // set session khusus warga
      $_SESSION['warga_id']   = $w['id'];
      $_SESSION['warga_nama'] = $w['nama'];

      header('Location: '.base_url('warga/tagihan_saya.php'));
      exit;
    } else {
      $error = 'NIK tidak ditemukan atau warga tidak aktif.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Warga - Cek Tagihan Iuran</title>
  <link rel="stylesheet" href="<?=base_url('assets/style.css')?>">
  <style>
    .card {
      background:#111827; padding:20px; border-radius:12px;
      max-width:420px; margin:40px auto; box-shadow:0 10px 30px rgba(0,0,0,.4);
    }
    h1{margin-top:0;font-size:22px}
    label{display:block;margin-bottom:6px}
    input[type="text"]{
      width:100%;padding:10px;border-radius:8px;border:1px solid #334155;
      background:#020617;color:#e2e8f0;
    }
    button{
      margin-top:10px;padding:10px 16px;border-radius:8px;border:0;
      background:#38bdf8;color:#0f172a;font-weight:600;cursor:pointer;
    }
    .error{color:#fca5a5;margin-top:8px}
    .info{font-size:13px;color:#94a3b8;margin-top:6px}
  </style>
</head>
<body>
  <div class="nav">
    <a href="<?=base_url('auth/login.php')?>">Login Admin / RT</a>
    <span style="color:#e2e8f0">|</span>
    <span style="color:#e5e7eb;font-weight:600">Portal Warga</span>
  </div>

  <div class="card">
    <h1>Portal Warga</h1>
    <p>Masukkan NIK untuk melihat tagihan iuran Anda.</p>

    <?php if($error): ?>
      <div class="error"><?=esc($error)?></div>
    <?php endif; ?>

    <form method="post">
      <label for="nik">NIK</label>
      <input type="text" id="nik" name="nik" required placeholder="Masukkan NIK sesuai data warga">

      <button type="submit">Masuk &amp; Lihat Tagihan</button>
      <div class="info">
        * Fitur ini hanya menampilkan tagihan Anda sendiri, bukan warga lain.
      </div>
    </form>
  </div>
</body>
</html>
