<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/functions.php';
require_login();

$pdo  = db();
$nowY = (int)date('Y');
$nowM = (int)date('n');

// Helper: cek apakah tabel ada
function table_exists(PDO $pdo, string $name): bool {
  $st = $pdo->prepare("SELECT COUNT(*) c
                         FROM information_schema.TABLES
                        WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = ?");
  $st->execute([$name]);
  return ((int)$st->fetch()['c']) === 1;
}

// 1) Jumlah warga (aman: kalau tabel belum ada → 0)
$jumlah_warga = 0;
if (table_exists($pdo, 'warga')) {
  $jumlah_warga = (int)($pdo->query('SELECT COUNT(*) c FROM warga WHERE aktif=1')->fetch()['c'] ?? 0);
}

// 2) Lunas bulan ini (aman: kalau tabel belum ada → 0)
$lunas_bulan_ini = 0;
if (table_exists($pdo, 'pembayaran')) {
  $st = $pdo->prepare('SELECT COUNT(DISTINCT warga_id) c FROM pembayaran WHERE tahun=? AND bulan=?');
  $st->execute([$nowY, $nowM]);
  $lunas_bulan_ini = (int)($st->fetch()['c'] ?? 0);
} else {
  // kasih info lembut biar ingat migrate tabel
  flash('error', 'Tabel pembayaran belum dibuat. Jalankan SQL migrasi untuk tabel pembayaran.');
}

$err = flash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Iuran RT</title>
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
  /* ====== Global Theme (soft pastel + dark mode) ====== */
  @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

  :root{
    --bg:#f8fafc; --text:#1e293b; --nav-bg:#ffffffcc; --nav-border:#e2e8f0;
    --accent:#93c5fd; --hover:#dbeafe; --shadow:rgba(0,0,0,0.08);
    --card-bg:#ffffff; --line:#e2e8f0; --muted:#64748b;
  }
  .dark{
    --bg:#0f172a; --text:#e2e8f0; --nav-bg:#1e293bcc; --nav-border:#334155;
    --accent:#60a5fa; --hover:#1e3a8a; --shadow:rgba(255,255,255,0.06);
    --card-bg:#111827; --line:#334155; --muted:#94a3b8;
  }

  html,body{height:100%}
  body{
    margin:0; font-family:"Quicksand",sans-serif;
    background:var(--bg); color:var(--text);
    transition:background .35s ease, color .35s ease;
  }

  /* ====== NAVBAR (brand kiri, menu kanan) ====== */
  .nav{
    display:flex; justify-content:space-between; align-items:center;
    padding:18px 40px; background:var(--nav-bg); border-bottom:1px solid var(--nav-border);
    box-shadow:0 4px 12px var(--shadow); backdrop-filter:blur(12px);
    position:sticky; top:0; z-index:999;
  }
  .nav .brand{
    font-weight:700; font-size:22px; color:var(--text); letter-spacing:.7px;
    display:flex; align-items:center; gap:8px; text-shadow:0 1px 2px rgba(0,0,0,.15);
  }
  .nav .brand span{ font-size:26px; }
  .nav .menu{ display:flex; align-items:center; gap:14px; }
  .nav a{
    color:var(--text); text-decoration:none; font-weight:600; font-size:16px;
    padding:10px 20px; border-radius:30px; transition:all .3s;
  }
  .nav a:hover{ background:var(--hover); transform:translateY(-2px); }
  .nav a.active{ background:var(--accent); color:#fff; box-shadow:0 0 10px var(--accent); }
  .toggle{
    margin-left:10px; cursor:pointer; border:0; background:var(--accent); color:#fff;
    padding:10px 18px; border-radius:25px; font-size:15px; font-weight:600;
    transition:all .3s; box-shadow:0 2px 8px var(--shadow);
  }
  .toggle:hover{ opacity:.9; transform:scale(1.08); }

  /* ====== Container & Cards (dashboard) ====== */
  .container{ max-width:1080px; margin:24px auto; padding:0 20px; }
  .cards{
    display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px; margin:18px 0 10px;
  }
  .card{
    background:var(--card-bg); border:1px solid var(--line); border-radius:16px;
    padding:18px; box-shadow:0 6px 18px var(--shadow);
  }
  .card .num{ font-size:36px; font-weight:700; line-height:1.1; margin-bottom:6px; }
  .card .label{ color:var(--muted); font-size:14px; }
  h2{ margin:6px 0 6px; font-size:24px; }

  /* ====== Alerts ====== */
  .alert{
    border-radius:12px; padding:12px 14px; border:1px solid var(--line);
    margin:10px 0 16px; background:var(--card-bg); box-shadow:0 4px 14px var(--shadow);
  }
  .alert.error{
    border-color:#ef4444;
    background:linear-gradient(180deg,rgba(239,68,68,.10),rgba(239,68,68,.06));
    color:#b91c1c;
  }
  .alert.success{
    border-color:#22c55e;
    background:linear-gradient(180deg,rgba(34,197,94,.10),rgba(34,197,94,.06));
    color:#166534;
  }

  /* Focus ring */
  *:focus{ outline:2px solid var(--accent); outline-offset:2px; border-radius:8px; }
  </style>
</head>

<body>

<?php
  // Navbar aman: pakai partial kalau ada, kalau tidak pakai fallback (brand kiri + menu kanan + toggle)
  $nav = __DIR__ . '/partials/nav.php';
  
  if (is_file($nav)) {
    include $nav;
  } else {
    $page = basename($_SERVER['PHP_SELF']);
    ?>
    <div class="nav">
      <div class="brand"><span>🏘️</span> Aplikasi Iuran RT 03</div>
      <div class="menu">
        <a href="<?=base_url('index.php')?>" class="<?=$page==='index.php'?'active':''?>">Dashboard</a>
        <a href="<?=base_url('warga/index.php')?>" class="<?=$page==='warga/index.php'?'active':''?>">Warga</a>
        <a href="<?=base_url('pembayaran/index.php')?>" class="<?=$page==='pembayaran/index.php'?'active':''?>">Pembayaran</a>
        <a href="<?=base_url('laporan/bulanan.php')?>" class="<?=$page==='bulanan.php'?'active':''?>">Laporan Bulanan</a>
        <a href="<?=base_url('laporan/tahunan.php')?>" class="<?=$page==='tahunan.php'?'active':''?>">Laporan Tahunan</a>
        <a href="<?=base_url('auth/logout.php')?>">Logout</a>
        <button class="toggle" id="themeToggle">🌙 Dark</button>
      </div>
    </div>

    <script>
    const toggle=document.getElementById('themeToggle'); const body=document.body;
    if(localStorage.getItem('theme')==='dark'){ body.classList.add('dark'); toggle.textContent='☀️ Light'; }
    toggle?.addEventListener('click', ()=>{
      body.classList.toggle('dark');
      const isDark=body.classList.contains('dark');
      toggle.textContent=isDark?'☀️ Light':'🌙 Dark';
      localStorage.setItem('theme', isDark?'dark':'light');
    });
    </script>
    <?php
  }
?>

<div class="container">
  <h2>Dashboard</h2>

  <?php if(!empty($err)): ?>
    <div class="alert error"><?=esc($err)?></div>
  <?php endif; ?>

  <div class="cards">
    <div class="card">
      <div class="num"><?=$jumlah_warga?></div>
      <div class="label">Total Warga Aktif</div>
    </div>
    <div class="card">
      <div class="num"><?=$lunas_bulan_ini?></div>
      <div class="label">Lunas Bulan Ini (<?=$nowM?>/<?=$nowY?>)</div>
    </div>
  </div>

  <p>Gunakan menu untuk mengelola data warga, input pembayaran, dan lihat laporan.</p>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
