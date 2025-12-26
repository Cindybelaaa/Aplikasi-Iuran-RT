<style>
/* 🌤️🌙 Soft Pastel Navbar (Compact, Responsive, Quicksand Font) */
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

:root {
  --bg: #f8fafc;
  --text: #1e293b;
  --nav-bg: #ffffffcc;
  --nav-border: #e2e8f0;
  --accent: #93c5fd;
  --hover: #dbeafe;
  --shadow: rgba(0,0,0,0.08);
}

.dark {
  --bg: #0f172a;
  --text: #e2e8f0;
  --nav-bg: #1e293bcc;
  --nav-border: #334155;
  --accent: #60a5fa;
  --hover: #1e3a8a;
  --shadow: rgba(255,255,255,0.05);
}

/* BODY */
body {
  margin: 0;
  font-family: "Quicksand", sans-serif;
  background: var(--bg);
  color: var(--text);
  transition: background 0.4s, color 0.4s;
}

/* NAVBAR */
.nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 28px;
  background: var(--nav-bg);
  border-bottom: 1px solid var(--nav-border);
  box-shadow: 0 2px 8px var(--shadow);
  backdrop-filter: blur(10px);
  position: sticky;
  top: 0;
  z-index: 999;
}

/* BRAND (KIRI) */
.nav .brand {
  font-weight: 700;
  font-size: 18px;
  color: var(--text);
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  gap: 6px;
  text-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.nav .brand span {
  font-size: 22px;
}

/* MENU (KANAN) */
.nav .menu {
  display: flex;
  align-items: center;
  gap: 10px;
}

.nav a {
  color: var(--text);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  padding: 8px 14px;
  border-radius: 20px;
  transition: all 0.25s ease;
}

.nav a:hover {
  background: var(--hover);
  transform: translateY(-1px);
}

.nav a.active {
  background: var(--accent);
  color: #fff;
  box-shadow: 0 0 6px var(--accent);
}

/* TOGGLE (DARK MODE) */
.toggle {
  margin-left: 8px;
  cursor: pointer;
  border: none;
  background: var(--accent);
  color: #fff;
  padding: 8px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px var(--shadow);
}

.toggle:hover {
  opacity: 0.9;
  transform: scale(1.05);
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .nav {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 20px;
  }
  .nav .menu {
    flex-wrap: wrap;
    gap: 8px;
  }
  .nav a {
    font-size: 13px;
    padding: 6px 10px;
  }
  .toggle {
    padding: 6px 10px;
    font-size: 12px;
  }
}
</style>

<!-- 🌸 NAVBAR HTML -->
<div class="nav">
  <div class="brand">
    <span>🏘️</span> Aplikasi Iuran RT 03
  </div>

  <div class="menu">
    <?php $page = basename($_SERVER['PHP_SELF']); ?>
    <a href="<?=base_url('index.php')?>" class="<?=$page==='index.php'?'active':''?>">Dashboard</a>
    <a href="<?=base_url('warga/dashboard.php')?>" class="<?=$page==='warga/dashboard.php'?'active':''?>">Warga</a>
    <a href="<?=base_url('pembayaran/index.php')?>" class="<?=$page==='pembayaran/index.php'?'active':''?>">Pembayaran</a>
    <a href="<?=base_url('laporan/bulanan.php')?>" class="<?=$page==='bulanan.php'?'active':''?>">Laporan Bulanan</a>
    <a href="<?=base_url('laporan/tahunan.php')?>" class="<?=$page==='tahunan.php'?'active':''?>">Laporan Tahunan</a>
    <a href="<?=base_url('laporan/tunggakan.php')?>" class="<?=$page==='tunggakan.php'?'active':''?>">Tunggakan</a>
    <a href="<?=base_url('auth/logout.php')?>">Logout</a>
    <button class="toggle" id="themeToggle">🌙 Dark</button>
  </div>
</div>

<script>
const toggle = document.getElementById('themeToggle');
const body = document.body;

if (localStorage.getItem('theme') === 'dark') {
  body.classList.add('dark');
  toggle.textContent = '☀️ Light';
}

toggle.addEventListener('click', () => {
  body.classList.toggle('dark');
  const isDark = body.classList.contains('dark');
  toggle.textContent = isDark ? '☀️ Light' : '🌙 Dark';
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
});
</script>
