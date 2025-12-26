<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$q = trim($_GET['q']??'');
if($q){
  $st = db()->prepare("SELECT * FROM warga WHERE (nama LIKE ? OR nik LIKE ?) ORDER BY nama ASC");
  $st->execute(['%'.$q.'%','%'.$q.'%']);
} else {
  $st = db()->query("SELECT * FROM warga ORDER BY nama ASC");
}
$rows = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Data Warga</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* ====== Theme (match dashboard/navbar) ====== */
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

:root{
  --bg:#f8fafc; --text:#1e293b; --accent:#93c5fd; --hover:#dbeafe;
  --card-bg:#ffffff; --line:#e2e8f0; --muted:#64748b;
  --shadow:rgba(0,0,0,0.08);
}
.dark{
  --bg:#0f172a; --text:#e2e8f0; --accent:#60a5fa; --hover:#1e3a8a;
  --card-bg:#111827; --line:#334155; --muted:#94a3b8;
  --shadow:rgba(255,255,255,0.06);
}

html,body{height:100%}
body{
  margin:0; font-family:"Quicksand",sans-serif;
  background:var(--bg); color:var(--text);
  transition:background .35s,color .35s;
}

/* ====== Container ====== */
.container{ max-width:1080px; margin:24px auto; padding:0 20px; }
h2{ margin:6px 0 14px; font-size:24px; }

/* ====== Search bar + actions ====== */
form.inline{
  display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin:10px 0 14px;
}
form.inline input[name="q"]{
  flex:1 1 260px; min-width:220px;
  padding:10px 12px; border:1px solid var(--line); border-radius:12px; background:var(--card-bg);
  color:var(--text); transition:border .2s, box-shadow .2s;
}
form.inline input[name="q"]:focus{
  border-color:var(--accent); box-shadow:0 0 0 2px var(--accent);
  outline:none;
}
form.inline button{
  padding:10px 16px; border-radius:12px; border:0; cursor:pointer;
  background:var(--accent); color:#fff; font-weight:700; transition:transform .15s, opacity .2s;
}
form.inline button:hover{ transform:translateY(-1px); opacity:.95; }
form.inline .btn{
  padding:10px 14px; border-radius:12px; background:linear-gradient(180deg,var(--accent),#6eaaf8);
  color:#fff; font-weight:700; text-decoration:none; border:0; display:inline-block;
  box-shadow:0 6px 16px var(--shadow);
}
form.inline .btn:hover{ opacity:.95; transform:translateY(-1px); }

/* ====== Table ====== */
.tablewrap{ overflow:auto; border-radius:14px; box-shadow:0 8px 18px var(--shadow); }
table{ width:100%; border-collapse:collapse; background:var(--card-bg); border:1px solid var(--line); }
th, td{ padding:12px 14px; border-bottom:1px solid var(--line); text-align:left; }
th{ font-weight:700; font-size:14px; }
tbody tr:hover{ background:rgba(0,0,0,0.02); }
.dark tbody tr:hover{ background:rgba(255,255,255,0.03); }

/* ====== Actions (Edit/Hapus) ====== */
.actions{ display:flex; gap:8px; flex-wrap:wrap; }
.action{
  display:inline-block; padding:8px 12px; border-radius:999px; font-size:13px; font-weight:700;
  text-decoration:none; border:1px solid var(--line); background:var(--bg); color:var(--text);
  transition:all .2s;
}
.action.edit{ border-color:var(--accent); }
.action.delete{ border-color:#fca5a5; color:#b91c1c; }
.action:hover{ transform:translateY(-1px); background:var(--card-bg); }

/* ====== Muted text (alamat panjang) ====== */
td .muted{ color:var(--muted); }

/* Focus ring */
*:focus{ outline:2px solid var(--accent); outline-offset:2px; border-radius:8px; }
</style>
</head>
<body>

<?php include __DIR__.'/../partials/nav.php'; ?>

<div class="container">
  <h2>Data Warga</h2>

  <form method="get" class="inline">
    <input name="q" value="<?=esc($q)?>" placeholder="Cari nama/NIK…">
    <button type="submit">Cari</button>
    <a class="btn" href="form.php">+ Tambah Warga</a>
  </form>

  <div class="tablewrap">
    <table>
      <thead>
        <tr>
          <th style="width:160px">NIK</th>
          <th style="width:220px">Nama</th>
          <th style="width:140px">No HP</th>
          <th>Alamat</th>
          <th style="width:160px">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php if(empty($rows)): ?>
        <tr>
          <td colspan="5" style="text-align:center; color:var(--muted); padding:18px;">
            Belum ada data. Klik <strong>+ Tambah Warga</strong> untuk menambahkan.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><?=esc($r['nik'])?:'<span class="muted">-</span>'?></td>
          <td><?=esc($r['nama'])?></td>
          <td><?=esc($r['no_hp'])?:'<span class="muted">-</span>'?></td>
          <td><?=esc($r['alamat'])?:'<span class="muted">-</span>'?></td>
          <td>
            <div class="actions">
              <a class="action edit" href="form.php?id=<?=$r['id']?>">Edit</a>
              <a class="action delete" href="delete.php?id=<?=$r['id']?>" onclick="return confirm('Hapus warga ini?')">Hapus</a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
