<?php
// ===== BOILERPLATE =====
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';
require_login();

$pdo   = db();
$jenis = strtolower(trim($_GET['jenis'] ?? ''));
$y     = (int)($_GET['tahun'] ?? date('Y'));

// ===== Helper: output CSV aman untuk Excel =====
function output_csv(string $filename, array $header, array $rows): void {
  // Header download
  header('Content-Type: text/csv; charset=UTF-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Pragma: no-cache');
  header('Expires: 0');

  $out = fopen('php://output', 'w');

  // BOM UTF-8 biar Excel Windows baca benar
  fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

  // Tulis header
  fputcsv($out, $header);

  // Tulis rows
  foreach ($rows as $r) {
    fputcsv($out, $r);
  }
  fclose($out);
  exit;
}

if ($jenis === 'bulanan') {
  $m = (int)($_GET['bulan'] ?? date('n'));

  // Ambil daftar warga + status & jumlah bulan tsb (seperti halaman laporan bulanan)
  $sql = "SELECT w.nama,
                 IF(p.id IS NULL, 0, 1) AS lunas,
                 COALESCE(p.jumlah, 0) AS jumlah
          FROM warga w
          LEFT JOIN pembayaran p
            ON p.warga_id = w.id AND p.tahun = ? AND p.bulan = ?
          WHERE w.aktif = 1
          ORDER BY w.nama";
  $st = $pdo->prepare($sql);
  $st->execute([$y, $m]);
  $rows = $st->fetchAll();

  $csvHeader = ['Nama', 'Status', 'Jumlah'];
  $csvRows   = [];
  foreach ($rows as $r) {
    $csvRows[] = [
      $r['nama'],
      $r['lunas'] ? 'LUNAS' : 'Belum',
      (string) (int) $r['jumlah'], // numeric raw (biar gampang di-sum di Excel)
    ];
  }

  $filename = sprintf('laporan-bulanan-%02d-%d.csv', $m, $y);
  output_csv($filename, $csvHeader, $csvRows);

} elseif ($jenis === 'tahunan') {

  // Total per bulan untuk tahun y (seperti halaman laporan tahunan)
  $sql = "SELECT bulan, SUM(jumlah) AS total
          FROM pembayaran
          WHERE tahun = ?
          GROUP BY bulan
          ORDER BY bulan";
  $st = $pdo->prepare($sql);
  $st->execute([$y]);
  $rows = $st->fetchAll();

  // Map bulan ke nama
  $nama_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

  $csvHeader = ['Bulan', 'Total'];
  $csvRows   = [];
  foreach ($rows as $r) {
    $bulanNum = (int)$r['bulan'];
    $csvRows[] = [
      $nama_bulan[$bulanNum] ?? (string)$bulanNum,
      (string) (int) $r['total'],
    ];
  }

  $filename = sprintf('laporan-tahunan-%d.csv', $y);
  output_csv($filename, $csvHeader, $csvRows);

} else {
  // Jenis tidak dikenal
  http_response_code(400);
  echo 'Parameter "jenis" harus salah satu dari: bulanan atau tahunan.';
  exit;
}
