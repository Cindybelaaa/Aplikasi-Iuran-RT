<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$y = (int)($_GET['tahun'] ?? date('Y'));
$m = (int)($_GET['bulan'] ?? date('n'));

$sql = "SELECT w.*, IF(p.id IS NULL, 0, 1) AS lunas, p.tanggal_bayar, p.jumlah
        FROM warga w
        LEFT JOIN pembayaran p
           ON p.warga_id = w.id AND p.tahun = ? AND p.bulan = ?
        WHERE w.aktif = 1
        ORDER BY w.nama ASC";
$st = db()->prepare($sql);
$st->execute([$y, $m]);
$rows = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Input Pembayaran</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

:root {
  --bg:#f8fafc; --text:#1e293b; --accent:#93c5fd; --hover:#dbeafe;
  --card-bg:#ffffff; --line:#e2e8f0; --muted:#64748b; --shadow:rgba(0,0,0,0.08);
}
.dark {
  --bg:#0f172a; --text:#e2e8f0; --accent:#60a5fa; --hover:#1e3a8a;
  --card-bg:#111827; --line:#334155; --muted:#94a3b8; --shadow:rgba(255,255,255,0.06);
}

html,body{height:100%}
body {
  margin:0; font-family:"Quicksand",sans-serif;
  background:var(--bg); color:var(--text);
  transition:background .3s,color .3s;
}

.container {
  max-width:1080px;
  margin:30px auto;
  padding:0 24px;
}
h2 {
  margin:8px 0 18px;
  font-size:24px;
  font-weight:700;
}

/* ===== Filter Form (bulan/tahun) ===== */
form.inline {
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:10px;
  margin-bottom:18px;
}
form.inline select,
form.inline input[type="number"] {
  padding:9px 12px;
  border:1px solid var(--line);
  border-radius:10px;
  background:var(--card-bg);
  font-size:14px;
  color:var(--text);
}
form.inline button {
  background:var(--accent);
  border:none;
  color:#fff;
  font-weight:700;
  border-radius:10px;
  padding:9px 16px;
  cursor:pointer;
  transition:all .2s;
}
form.inline button:hover { transform:translateY(-1px); opacity:.9; }

/* ===== Table ===== */
.tablewrap{
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 6px 18px var(--shadow);
  background:var(--card-bg);
}
table {
  width:100%;
  border-collapse:collapse;
}
th, td {
  padding:12px 14px;
  border-bottom:1px solid var(--line);
  font-size:15px;
}
th {
  text-align:left;
  font-weight:700;
  background:rgba(0,0,0,0.03);
}
.dark th { background:rgba(255,255,255,0.05); }

tbody tr:nth-child(odd){ background:rgba(0,0,0,0.015); }
.dark tbody tr:nth-child(odd){ background:rgba(255,255,255,0.02); }

tbody tr:hover {
  background:rgba(0,0,0,0.03);
}
.dark tbody tr:hover {
  background:rgba(255,255,255,0.04);
}

input[type="date"],
input[type="number"] {
  width:100%;
  padding:7px 10px;
  border:1px solid var(--line);
  border-radius:8px;
  background:var(--card-bg);
  color:var(--text);
  font-size:14px;
  transition:border .2s, box-shadow .2s;
}
input[type="date"]:focus,
input[type="number"]:focus {
  border-color:var(--accent);
  box-shadow:0 0 0 2px var(--accent);
  outline:none;
}
input[type="checkbox"] {
  width:18px; height:18px;
  cursor:pointer;
}

/* ===== Badge ===== */
.badge {
  display:inline-block;
  padding:5px 10px;
  border-radius:20px;
  font-size:13px;
  font-weight:600;
  color:var(--text);
  background:rgba(0,0,0,0.05);
}
.badge.success {
  background:#bbf7d0;
  color:#166534;
}
.dark .badge.success {
  background:#166534;
  color:#bbf7d0;
}

/* ===== Submit Button ===== */
.actions {
  display:flex;
  justify-content:flex-end;
}
button[type="submit"] {
  margin-top:20px;
  background:var(--accent);
  color:#fff;
  border:none;
  border-radius:12px;
  font-weight:700;
  padding:12px 20px;
  font-size:16px;
  cursor:pointer;
  transition:all .2s;
}
button[type="submit"]:hover {
  transform:translateY(-2px);
  opacity:.9;
}

/* ===== Responsive ===== */
@media (max-width:820px) {
  th, td { font-size:14px; }
  input[type="number"], input[type="date"] { font-size:13px; }
  .container { padding:0 16px; }
}
</style>
</head>
<body>

<?php include __DIR__.'/../partials/nav.php'; ?>

<div class="container">
  <h2>Pembayaran Bulan <?=esc($m)?> / <?=esc($y)?></h2>

  <!-- Filter bulan/tahun -->
  <form class="inline" method="get">
    <select name="bulan">
      <?php for($i=1;$i<=12;$i++): ?>
        <option value="<?=$i?>" <?= $i===$m?'selected':''?>><?=$i?></option>
      <?php endfor; ?>
    </select>
    <input type="number" name="tahun" value="<?=$y?>" style="width:110px">
    <button type="submit">Go</button>
  </form>

  <!-- Form simpan pembayaran -->
  <form method="post" action="save.php">
    <input type="hidden" name="bulan" value="<?=$m?>">
    <input type="hidden" name="tahun" value="<?=$y?>">

    <div class="tablewrap">
      <table>
        <thead>
          <tr>
            <th style="min-width:220px">Nama</th>
            <th style="width:120px">Status</th>
            <th style="width:170px">Tanggal Bayar</th>
            <th style="width:180px">Jumlah</th>
            <th style="width:130px; text-align:center">Centang Lunas</th>
          </tr>
        </thead>
        <tbody>
        <?php if(empty($rows)): ?>
          <tr>
            <td colspan="5" style="text-align:center; color:var(--muted); padding:18px;">
              Belum ada data warga aktif.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach($rows as $r): ?>
          <tr>
            <td><?=esc($r['nama'])?></td>
            <td><?= $r['lunas'] ? '<span class="badge success">LUNAS</span>' : '<span class="badge">Belum</span>' ?></td>
            <td><input type="date"   name="tgl[<?=$r['id']?>]" value="<?= $r['tanggal_bayar'] ?? '' ?>"></td>
            <td><input type="number" name="jml[<?=$r['id']?>]" placeholder="kosong=pakai nominal settings" value="<?= $r['jumlah'] ?? '' ?>"></td>
            <td style="text-align:center">
              <input type="checkbox" name="lunas[]" value="<?=$r['id']?>" <?= $r['lunas'] ? 'checked' : '' ?>>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="actions">
      <button type="submit">Simpan Pembayaran</button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
