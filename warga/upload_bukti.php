<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';

require_warga_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: '.base_url('warga/tagihan_saya.php'));
  exit;
}

$pdo      = db();
$warga_id = (int)$_SESSION['warga_id'];

// ambil nominal default (fallback kalau jumlah kosong)
$nominal = (int)($pdo->query("SELECT nominal_iuran FROM settings WHERE id=1")->fetch()['nominal_iuran'] ?? 0);

$tahun   = (int)($_POST['tahun'] ?? date('Y'));
$bulan   = (int)($_POST['bulan'] ?? date('n'));
$tanggal = $_POST['tanggal_bayar'] ?? date('Y-m-d');
$jumlah  = (int)($_POST['jumlah'] ?? $nominal);

if ($tahun < 2000 || $bulan < 1 || $bulan > 12) {
  die('Data tahun/bulan tidak valid.');
}

if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
  die('Upload bukti gagal.');
}

$file = $_FILES['bukti'];
$allowed = ['image/jpeg','image/png','image/jpg'];

if (!in_array($file['type'], $allowed)) {
  die('Format file harus JPG atau PNG.');
}

// buat folder upload kalau belum ada
$uploadDir = __DIR__ . '/../uploads/bukti/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$namaFile = 'bukti_'.$warga_id.'_'.$tahun.'_'.$bulan.'_'.time().'.'.$ext;
$pathFull = $uploadDir . $namaFile;

// simpan file SEKALI saja
if (!move_uploaded_file($file['tmp_name'], $pathFull)) {
  die('Gagal menyimpan file bukti.');
}

// path yang disimpan ke database (relatif dari root app)
$pathRelatif = 'uploads/bukti/'.$namaFile;

// ====== LOGIKA BARU: SEBAR PEMBAYARAN KE BEBERAPA BULAN ======
if ($nominal <= 0) {
  // kalau setting nominal kacau, fallback ke cara lama (1 bulan)
  $bulan_terbayar = 1;
} else {
  $bulan_terbayar = intdiv($jumlah, $nominal); // berapa kali kelipatan nominal
  if ($bulan_terbayar < 1) {
    $bulan_terbayar = 1; // minimal 1 bulan tercatat
  }
}

$sql = 'INSERT INTO pembayaran (
          warga_id, tahun, bulan, tanggal_bayar, jumlah, metode, keterangan, bukti_path
        )
        VALUES (
          :wid, :th, :bl, :tgl, :jml, "transfer", "Upload via Portal Warga", :bukti
        )
        ON DUPLICATE KEY UPDATE
          tanggal_bayar = VALUES(tanggal_bayar),
          jumlah        = VALUES(jumlah),
          metode        = VALUES(metode),
          keterangan    = VALUES(keterangan),
          bukti_path    = VALUES(bukti_path)';

$st = $pdo->prepare($sql);

// kalau cuma 1 bulan, langsung saja
if ($bulan_terbayar === 1) {
  $st->execute([
    ':wid'   => $warga_id,
    ':th'    => $tahun,
    ':bl'    => $bulan,
    ':tgl'   => $tanggal,
    ':jml'   => $jumlah,       // bisa saja < atau > nominal, nanti RT bisa cek
    ':bukti' => $pathRelatif,
  ]);
} else {
  // kalau lebih dari 1 bulan, sebar per bulan
  // contoh: iuran 10k, bayar 20k -> 2 bulan, masing-masing 10k
  // sisa uang yang tidak genap nominal "numpang" di bulan terakhir
  $sisa = $jumlah;

  for ($i = 0; $i < $bulan_terbayar; $i++) {
    $b = $bulan + $i;
    $t = $tahun;

    if ($b > 12) {
      $b -= 12;
      $t++;
    }

    // hitung jumlah untuk bulan ini
    if ($i < $bulan_terbayar - 1 && $nominal > 0) {
      $jml_bulan_ini = $nominal;
      $sisa -= $nominal;
    } else {
      // bulan terakhir: ambil sisa
      $jml_bulan_ini = $sisa > 0 ? $sisa : $nominal;
    }

    $st->execute([
      ':wid'   => $warga_id,
      ':th'    => $t,
      ':bl'    => $b,
      ':tgl'   => $tanggal,
      ':jml'   => $jml_bulan_ini,
      ':bukti' => $pathRelatif,
    ]);
  }
}

header('Location: '.base_url('warga/tagihan_saya.php'));
exit;
