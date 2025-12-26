<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';
require_login();

// ==== cari tcpdf.php di beberapa lokasi umum ====
$tcpdf_paths = [
  __DIR__ . '/../assets/tcpdf/tcpdf.php',            // Opsi A (manual)
  __DIR__ . '/../assets/tcpdf_min/tcpdf.php',        // Beberapa rilis pakai tcpdf_min
  __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php', // Composer
];

$tcpdf_found = false;
foreach ($tcpdf_paths as $p) {
  if (is_file($p)) { require_once $p; $tcpdf_found = true; break; }
}
if (!$tcpdf_found) {
  http_response_code(500);
  echo "TCPDF tidak ditemukan.\n".
       "Letakkan library di salah satu path berikut:\n".
       " - assets/tcpdf/tcpdf.php\n".
       " - assets/tcpdf_min/tcpdf.php\n".
       " - vendor/tecnickcom/tcpdf/tcpdf.php (composer)\n";
  exit;
}

$pdo   = db();
$jenis = strtolower($_GET['jenis'] ?? '');
$y     = (int)($_GET['tahun'] ?? date('Y'));
$m     = (int)($_GET['bulan'] ?? date('n'));

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Aplikasi Iuran RT 03');
$pdf->SetAuthor('Admin RT 03');
$pdf->SetTitle('Laporan ' . $jenis . ' RT 03');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

$html = '<h2 style="text-align:center;">Laporan ' . ucfirst($jenis) . ' Iuran RT 03</h2>';

if ($jenis === 'bulanan') {
  $st = $pdo->prepare("SELECT w.nama, IF(p.id IS NULL, 0, 1) AS lunas, p.jumlah
                       FROM warga w
                       LEFT JOIN pembayaran p ON p.warga_id=w.id AND p.tahun=? AND p.bulan=?
                       WHERE w.aktif=1 ORDER BY w.nama");
  $st->execute([$y, $m]);
  $rows = $st->fetchAll();

  $html .= "<p>Bulan: <b>$m/$y</b></p>";
  $html .= '<table border="1" cellpadding="6">
              <thead><tr><th>Nama</th><th>Status</th><th>Jumlah</th></tr></thead><tbody>';
  $total = 0;
  foreach ($rows as $r) {
    $status = $r['lunas'] ? 'LUNAS' : 'Belum';
    $jumlah = $r['lunas'] ? number_format($r['jumlah']) : '-';
    $total += (int)($r['jumlah'] ?? 0);
    $html .= "<tr><td>".htmlspecialchars($r['nama'])."</td><td>$status</td><td align='right'>$jumlah</td></tr>";
  }
  $html .= "<tr><th colspan='2'>Total</th><th align='right'>".number_format($total)."</th></tr></tbody></table>";

} elseif ($jenis === 'tahunan') {
  $st = $pdo->prepare("SELECT bulan, SUM(jumlah) AS total
                       FROM pembayaran WHERE tahun=? GROUP BY bulan ORDER BY bulan");
  $st->execute([$y]);
  $rows = $st->fetchAll();

  $bulanNama = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

  $html .= "<p>Tahun: <b>$y</b></p>";
  $html .= '<table border="1" cellpadding="6">
              <thead><tr><th>Bulan</th><th>Total</th></tr></thead><tbody>';
  $total = 0;
  foreach ($rows as $r) {
    $nama = $bulanNama[(int)$r['bulan']] ?? $r['bulan'];
    $total += (int)$r['total'];
    $html .= "<tr><td>$nama</td><td align='right'>".number_format($r['total'])."</td></tr>";
  }
  $html .= "<tr><th>Total Setahun</th><th align='right'>".number_format($total)."</th></tr></tbody></table>";

} else {
  http_response_code(400);
  echo 'Parameter "jenis" harus bulanan atau tahunan.'; exit;
}

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Laporan-'.$jenis.'-'.$y.'.pdf', 'I'); // I=inline, D=download
