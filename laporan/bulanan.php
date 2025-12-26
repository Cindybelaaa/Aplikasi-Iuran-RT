<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$m = (int)($_GET['bulan'] ?? date('n'));
$y = (int)($_GET['tahun'] ?? date('Y'));

/*
 * Tambahan: ambil juga p.bukti_path
 * Karena key (warga_id, tahun, bulan) unik, 1 row pembayaran per warga per bulan
 */
$sql = "SELECT 
          w.nama,
          IF(p.id IS NULL, 0, 1) AS lunas,
          p.jumlah,
          p.bukti_path
        FROM warga w
        LEFT JOIN pembayaran p
          ON p.warga_id = w.id 
         AND p.tahun = ? 
         AND p.bulan = ?
        WHERE w.aktif = 1
        ORDER BY w.nama";

$st = db()->prepare($sql);
$st->execute([$y, $m]);
$rows = $st->fetchAll();

$total = 0;
foreach ($rows as $r) { $total += (int)($r['jumlah'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Bulanan</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

:root{
  --bg:#f8fafc; --text:#1e293b; --accent:#93c5fd; --hover:#dbeafe;
  --card-bg:#ffffff; --line:#e2e8f0; --muted:#64748b; --shadow:rgba(0,0,0,0.08);
}
.dark{
  --bg:#0f172a; --text:#e2e8f0; --accent:#60a5fa; --hover:#1e3a8a;
  --card-bg:#111827; --line:#334155; --muted:#94a3b8; --shadow:rgba(255,255,255,0.06);
}

html,body{height:100%}
body{
  margin:0; font-family:"Quicksand",sans-serif;
  background:var(--bg); color:var(--text);
  transition:background .3s,color .3s;
}

.container{ max-width:1080px; margin:30px auto; padding:0 24px; }
h2{ margin:8px 0 18px; font-size:24px; font-weight:700; }

/* Filter form */
form.inline{
  display:flex; flex-wrap:wrap; align-items:center; gap:10px;
  margin-bottom:18px;
}
form.inline select, form.inline input[type="number"]{
  padding:9px 12px; border:1px solid var(--line); border-radius:10px;
  background:var(--card-bg); color:var(--text); font-size:14px;
}
form.inline button{
  background:var(--accent); color:#fff; border:none; border-radius:10px;
  font-weight:700; padding:9px 16px; cursor:pointer; transition:all .2s;
}
form.inline button:hover{ transform:translateY(-1px); opacity:.9; }
form.inline .btn{
  display:inline-block; text-decoration:none; border-radius:10px;
  background:linear-gradient(180deg,var(--accent),#6eaaf8); color:#fff;
  padding:9px 14px; font-weight:700; box-shadow:0 6px 16px var(--shadow);
}
form.inline .btn:hover{ opacity:.95; transform:translateY(-1px); }

/* Table */
.tablewrap{
  border-radius:14px; overflow:hidden;
  background:var(--card-bg); box-shadow:0 6px 18px var(--shadow);
}
table{ width:100%; border-collapse:collapse; }
th, td{ padding:12px 14px; border-bottom:1px solid var(--line); font-size:15px; }
th{ text-align:left; font-weight:700; background:rgba(0,0,0,.03); }
.dark th{ background:rgba(255,255,255,.05); }

tbody tr:nth-child(odd){ background:rgba(0,0,0,.015); }
.dark tbody tr:nth-child(odd){ background:rgba(255,255,255,.02); }
tbody tr:hover{ background:rgba(0,0,0,.03); }
.dark tbody tr:hover{ background:rgba(255,255,255,.04); }

td.right, th.right{ text-align:right; }

/* Badges */
.badge{
  display:inline-block; padding:5px 10px; border-radius:20px;
  font-size:13px; font-weight:700;
}
.badge.lunas{ background:#bbf7d0; color:#166534; }
.badge.belum{ background:#fde68a; color:#7c2d12; }
.dark .badge.lunas{ background:#166534; color:#bbf7d0; }
.dark .badge.belum{ background:#7c2d12; color:#fde68a; }

/* Link bukti */
.link-bukti{
  font-size:12px;
  margin-left:8px;
  color:#38bdf8;
  text-decoration:none;
}
.link-bukti:hover{
  text-decoration:underline;
}

/* Tfoot total */
tfoot th{ background:rgba(0,0,0,.04); }
.dark tfoot th{ background:rgba(255,255,255,.06); }

/* Responsive */
@media (max-width:820px){
  th, td{ font-size:14px; }
  .container{ padding:0 16px; }
}
</style>
</head>
<body>

<?php include __DIR__.'/../partials/nav.php'; ?>

<div class="container">
  <h2>Laporan Bulan <?=esc($m)?> / <?=esc($y)?></h2>

  <form method="get" class="inline">
    <select name="bulan">
      <?php for($i=1;$i<=12;$i++): ?>
        <option value="<?=$i?>" <?= $i===$m?'selected':''?>><?=$i?></option>
      <?php endfor; ?>
    </select>
    <input type="number" name="tahun" value="<?=$y?>" style="width:110px">
    <button type="submit">Tampilkan</button>
    <a class="btn" href="export_csv.php?jenis=bulanan&bulan=<?=$m?>&tahun=<?=$y?>">Export CSV</a>
    <a class="btn" href="export_pdf.php?jenis=bulanan&bulan=<?=$m?>&tahun=<?=$y?>">Export PDF</a>
  </form>

  <div class="tablewrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th style="width:160px">Status</th>
          <th class="right" style="width:220px">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="3" style="text-align:center; color:var(--muted); padding:18px;">
              Belum ada data warga aktif.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?= esc($r['nama']) ?></td>
              <td>
                <?php if ($r['lunas']): ?>
                  <span class="badge lunas">LUNAS</span>
                <?php else: ?>
                  <span class="badge belum">Belum</span>
                <?php endif; ?>
              </td>
              <td class="right">
                <?php if ($r['lunas']): ?>
                  Rp <?= number_format((int)$r['jumlah'], 0, ',', '.') ?>
                  <?php if (!empty($r['bukti_path'])): ?>
                    <a href="<?= base_url($r['bukti_path']) ?>" 
                       target="_blank" 
                       class="link-bukti">
                      Lihat bukti
                    </a>
                  <?php endif; ?>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="2">Total Masuk</th>
          <th class="right">Rp <?= number_format($total, 0, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
