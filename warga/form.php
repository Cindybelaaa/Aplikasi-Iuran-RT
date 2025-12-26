<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();

$id = (int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $id    = (int)($_POST['id']??0);
  $nik   = trim($_POST['nik']??'');
  $nama  = trim($_POST['nama']??'');
  $nohp  = trim($_POST['no_hp']??'');
  $alamat= trim($_POST['alamat']??'');

  if($id){
    $st = db()->prepare('UPDATE warga SET nik=?, nama=?, no_hp=?, alamat=? WHERE id=?');
    $st->execute([$nik,$nama,$nohp,$alamat,$id]);
    flash('ok','Data warga diperbarui');
  }else{
    $st = db()->prepare('INSERT INTO warga(nik,nama,no_hp,alamat) VALUES(?,?,?,?)');
    $st->execute([$nik,$nama,$nohp,$alamat]);
    flash('ok','Warga ditambahkan');
  }
  header('Location: dashboard.php'); exit;
}

$row = ['nik'=>'','nama'=>'','no_hp'=>'','alamat'=>''];
if($id){
  $st = db()->prepare('SELECT * FROM warga WHERE id=?');
  $st->execute([$id]);
  $row = $st->fetch() ?: $row;
}
$msg = flash('ok');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Form Warga</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
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

body{
  margin:0;
  font-family:"Quicksand",sans-serif;
  background:var(--bg);
  color:var(--text);
  transition:background .35s,color .35s;
}

/* ===== Container ===== */
.container.small{
  max-width:780px;
  margin:50px auto;
  padding:0 25px;
}

.form-card{
  background:var(--card-bg);
  border:1px solid var(--line);
  border-radius:20px;
  padding:40px 35px;
  box-shadow:0 8px 22px var(--shadow);
}

h2{margin-bottom:25px;font-size:26px;font-weight:700}

/* ===== Alert ===== */
.alert.success{
  border:1px solid #22c55e;
  background:linear-gradient(180deg,rgba(34,197,94,.10),rgba(34,197,94,.06));
  color:#166534;
  border-radius:12px;
  padding:12px 14px;
  margin-bottom:18px;
  box-shadow:0 4px 14px var(--shadow);
}

/* ===== Form ===== */
form{
  display:flex;
  flex-direction:column;
  gap:28px; /* jarak antar baris */
}

form .grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  column-gap:36px; /* jarak horizontal antar kolom */
  row-gap:26px;    /* jarak vertikal antar baris */
}
@media(max-width:720px){
  form .grid{grid-template-columns:1fr;}
}

label{
  display:flex;
  flex-direction:column;
  font-weight:600;
  font-size:14px;
  gap:10px;
}

input, textarea{
  width:100%;
  padding:13px 15px;
  font-size:15px;
  border:1px solid var(--line);
  border-radius:12px;
  background:var(--card-bg);
  color:var(--text);
  transition:border .2s, box-shadow .2s;
}
textarea{min-height:110px;resize:vertical}
input:focus, textarea:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 2px var(--accent);
  outline:none;
}

/* ===== Catatan kecil ===== */
.small-note{
  color:var(--muted);
  font-size:13px;
  margin-top:-2px;
}

/* ===== Tombol ===== */
.actions{
  display:flex;
  gap:14px;
  justify-content:flex-end;
  margin-top:10px;
}

.btn{
  display:inline-block;
  padding:12px 22px;
  border-radius:12px;
  font-weight:700;
  border:none;
  cursor:pointer;
  transition:all .2s;
}
.btn.primary{background:var(--accent);color:#fff;}
.btn.light{background:transparent;border:1px solid var(--line);color:var(--text);}
.btn:hover{transform:translateY(-2px);opacity:.9;}

*:focus{outline:2px solid var(--accent);outline-offset:2px;border-radius:8px;}
</style>


</head>
<body>

<?php include __DIR__.'/../partials/nav.php'; ?>

<div class="container small">
  <h2><?= $id? 'Edit' : 'Tambah' ?> Warga</h2>

  <?php if($msg): ?>
    <div class="alert success"><?=esc($msg)?></div>
  <?php endif; ?>

  <div class="form-card">
    <form method="post" autocomplete="off">
      <input type="hidden" name="id" value="<?=$id?>">

      <div class="grid">
        <label>NIK
          <input name="nik" value="<?=esc($row['nik'])?>" maxlength="20" inputmode="numeric" placeholder="Contoh: 3276xxxxxxxxxxxx">
          <span class="small-note">Opsional. Isi angka saja (maks. 20 karakter).</span>
        </label>

        <label>Nama
          <input name="nama" value="<?=esc($row['nama'])?>" required placeholder="Nama lengkap" autofocus>
        </label>

        <label>No HP
          <input name="no_hp" value="<?=esc($row['no_hp'])?>" inputmode="tel" placeholder="08xxxxxxxxxx">
        </label>

        <label>Alamat
          <textarea name="alamat" placeholder="Nama jalan, RT/RW, Kelurahan, Kecamatan"><?=esc($row['alamat'])?></textarea>
        </label>
      </div>

      <div class="actions">
        <a class="btn light" href="dashboard.php">Batal</a>
        <button type="submit" class="btn primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
