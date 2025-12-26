<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$y = (int)($_GET['tahun'] ?? date('Y'));

$sql = "SELECT bulan, SUM(jumlah) AS total
        FROM pembayaran
        WHERE tahun=?
        GROUP BY bulan
        ORDER BY bulan";
$st = db()->prepare($sql);
$st->execute([$y]);
$data = $st->fetchAll();

$tot = 0;
foreach ($data as $d) { $tot += (int)$d['total']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Tahunan <?=$y?></title>
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
form.inline input[type="number"]{
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

/* Tfoot total */
tfoot th{ background:rgba(0,0,0,.04); }
.dark tfoot th{ background:rgba(255,255,255,.06); }

/* Bulan badge */
.bulan-badge{
  display:inline-block; padding:6px 12px;
  border-radius:10px; background:var(--hover);
  font-weight:600; font-size:14px; color:var(--text);
}
.dark .bulan-badge{ background:#1e3a8a; color:#dbeafe; }

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
  <h2>Laporan Tahunan <?=esc($y)?></h2>

  <form method="get" class="inline">
    <input type="number" name="tahun" value="<?=$y?>" style="width:110px">
    <button type="submit">Tampilkan</button>
    <a class="btn" href="export_csv.php?jenis=tahunan&tahun=<?=$y?>">Export CSV</a>
    <a class="btn" href="export_pdf.php?jenis=tahunan&tahun=<?=$y?>">Export PDF</a>

  </form>

  <div class="tablewrap">
    <table>
      <thead>
        <tr>
          <th>Bulan</th>
          <th class="right">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($data)): ?>
          <tr>
            <td colspan="2" style="text-align:center; color:var(--muted); padding:18px;">
              Belum ada data pembayaran untuk tahun ini.
            </td>
          </tr>
        <?php else: ?>
          <?php 
          $nama_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
          foreach($data as $d): ?>
            <tr>
              <td><span class="bulan-badge"><?= $nama_bulan[(int)$d['bulan']] ?? $d['bulan'] ?></span></td>
              <td class="right">Rp <?= number_format((int)$d['total'], 0, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <th>Total Setahun</th>
          <th class="right">Rp <?= number_format($tot, 0, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
