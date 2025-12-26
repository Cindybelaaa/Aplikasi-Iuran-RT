<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();


$bulan = (int)($_POST['bulan']??date('n'));
$tahun = (int)($_POST['tahun']??date('Y'));
$lunas = $_POST['lunas'] ?? [];
$tgl = $_POST['tgl'] ?? [];
$jml = $_POST['jml'] ?? [];


// ambil nominal default
$nom = (int)(db()->query('SELECT nominal_iuran FROM settings WHERE id=1')->fetch()['nominal_iuran'] ?? 50000);


$ins = db()->prepare('INSERT INTO pembayaran(warga_id,tahun,bulan,tanggal_bayar,jumlah,metode) VALUES(?,?,?,?,?,"tunai")
ON DUPLICATE KEY UPDATE tanggal_bayar=VALUES(tanggal_bayar), jumlah=VALUES(jumlah)');


foreach($lunas as $wid){
$wid = (int)$wid;
$tanggal = !empty($tgl[$wid]) ? $tgl[$wid] : date('Y-m-d');
$jumlah = (int)($jml[$wid] ?? $nom);
$ins->execute([$wid,$tahun,$bulan,$tanggal,$jumlah]);
}


flash('ok','Pembayaran diperbarui.');
header('Location: index.php?bulan='.$bulan.'&tahun='.$tahun);