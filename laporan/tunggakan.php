<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$y = (int)($_GET['tahun'] ?? date('Y'));
$m = (int)($_GET['bulan'] ?? date('n'));

$pdo = db();

// ambil nominal dari settings
$nominal = (int)($pdo->query("SELECT nominal_iuran FROM settings WHERE id=1")->fetch()['nominal_iuran'] ?? 0);

// --- Tunggakan bulan ini: tidak ada pembayaran ATAU jumlah < nominal
$sqlBulan = "
SELECT w.id, w.nama, COALESCE(p.jumlah,0) as jumlah_bayar
FROM warga w
LEFT JOIN pembayaran p 
  ON p.warga_id=w.id AND p.tahun=? AND p.bulan=?
WHERE w.aktif=1
  AND (p.id IS NULL OR COALESCE(p.jumlah,0) < ?)
ORDER BY w.nama";
$st = $pdo->prepare($sqlBulan);
$st->execute([$y,$m,$nominal]);
$belumBulanIni = $st->fetchAll();

// --- Akumulasi tunggakan (berapa bulan dalam 1 tahun yang belum lunas)
$sqlAkun = "
WITH bulan AS (
  SELECT 1 b UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
  UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
)
SELECT w.id, w.nama,
       SUM( CASE WHEN p.id IS NULL OR p.jumlah < ? THEN 1 ELSE 0 END ) AS bulan_tunggak
FROM warga w
CROSS JOIN bulan b
LEFT JOIN pembayaran p
  ON p.warga_id=w.id AND p.tahun=? AND p.bulan=b.b
WHERE w.aktif=1
GROUP BY w.id, w.nama
HAVING bulan_tunggak > 0
ORDER BY bulan_tunggak DESC, w.nama";
$st2 = $pdo->prepare($sqlAkun);
$st2->execute([$nominal,$y]);
$akumulasi = $st2->fetchAll();
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="utf-8"><title>Laporan Tunggakan</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
:root{--bg:#f8fafc;--text:#1e293b;--accent:#93c5fd;--line:#e2e8f0;--card:#fff}
.dark{--bg:#0f172a;--text:#e2e8f0;--accent:#60a5fa;--line:#334155;--card:#111827}
body{background:var(--bg);color:var(--text);font-family:system-ui,Segoe UI,Roboto,Inter}
.container{max-width:1080px;margin:30px auto;padding:0 24px}
h2{margin:6px 0 14px}
.card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px;margin-bottom:18px}
.inline{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.inline select,.inline input{padding:8px 10px;border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--text)}
.inline button,.btn{background:var(--accent);color:#fff;border:0;border-radius:10px;padding:9px 14px;font-weight:700;cursor:pointer}
.table{width:100%;border-collapse:collapse}
.table th,.table td{padding:10px 12px;border-bottom:1px solid var(--line)}
.badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700}
.badge.warn{background:#fde68a;color:#7c2d12}
.actions a{text-decoration:none}
</style>
</head><body>
<?php include __DIR__.'/../partials/nav.php'; ?>
<div class="container">
  <h2>Tunggakan Iuran</h2>

  <form method="get" class="inline">
    <select name="bulan"><?php for($i=1;$i<=12;$i++): ?>
      <option value="<?=$i?>" <?=$i===$m?'selected':''?>><?=$i?></option>
    <?php endfor;?></select>
    <input type="number" name="tahun" value="<?=$y?>" style="width:110px">
    <button type="submit">Tampilkan</button>
    <a class="btn" href="export_csv.php?jenis=bulanan&bulan=<?=$m?>&tahun=<?=$y?>">Export CSV Bulan Ini</a>
  </form>

  <div class="card">
    <h3 style="margin:0 0 10px">Belum Lunas Bulan <?=$m?>/<?=$y?></h3>
    <table class="table">
      <thead><tr><th>Nama</th><th>Status</th><th>Kurang</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(empty($belumBulanIni)): ?>
        <tr><td colspan="4" style="text-align:center;color:#64748b">Semua warga sudah lunas bulan ini 🎉</td></tr>
      <?php else: foreach($belumBulanIni as $r):
        $kurang = max(0, $nominal - (int)$r['jumlah_bayar']);
        $nama = $r['nama'];
        $wa = "Halo%20$nama,%0AKami%20dari%20pengurus%20RT%2003.%0AIni%20pengingat%20iuran%20bulan%20$m/%20$y%20sebesar%20Rp%20".number_format($nominal,0,',','.').".%0A".
              "Mohon%20dibayarkan%20jika%20berkenan.%20Terima%20kasih.%20🙏";
        ?>
        <tr>
          <td><?=esc($nama)?></td>
          <td><span class="badge warn">Belum</span></td>
          <td>Rp <?=number_format($kurang,0,',','.')?></td>
          <td class="actions">
            <!-- kirim WA (isi no hp bisa kamu ambil dari tabel bila ada) -->
            <a class="btn" style="padding:6px 10px" target="_blank" href="https://wa.me/?text=<?=$wa?>">Kirim WA</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h3 style="margin:0 0 10px">Akumulasi Tunggakan Tahun <?=$y?></h3>
    <table class="table">
      <thead><tr><th>Nama</th><th>Jumlah Bulan Belum Lunas</th></tr></thead>
      <tbody>
      <?php if(empty($akumulasi)): ?>
        <tr><td colspan="2" style="text-align:center;color:#64748b">Tidak ada tunggakan di tahun ini 🎉</td></tr>
      <?php else: foreach($akumulasi as $r): ?>
        <tr>
          <td><?=esc($r['nama'])?></td>
          <td><?= (int)$r['bulan_tunggak'] ?> bulan</td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
