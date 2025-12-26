<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';

if ($_SERVER['REQUEST_METHOD']==='POST'){
  $u = trim($_POST['username']??'');
  $p = trim($_POST['password']??'');
  $stmt = db()->prepare('SELECT * FROM users WHERE username=? LIMIT 1');
  $stmt->execute([$u]);
  $user = $stmt->fetch();
  if($user && password_verify($p, $user['password_hash'])){
    $_SESSION['user_id']=$user['id'];
    $_SESSION['nama']=$user['nama'];
    header('Location: '.base_url('index.php')); exit;
  }
  flash('error','Username atau password salah');
}
$err = flash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Login</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap');

:root{
  --bg:#f8fafc; --text:#1e293b; --accent:#93c5fd; --hover:#dbeafe;
  --card-bg:#ffffffcc; --shadow:rgba(0,0,0,0.08);
}
.dark{
  --bg:#0f172a; --text:#e2e8f0; --accent:#60a5fa; --hover:#1e3a8a;
  --card-bg:#1e293bcc; --shadow:rgba(255,255,255,0.05);
}

body{
  margin:0; height:100vh;
  display:flex; flex-direction:column; justify-content:center; align-items:center;
  font-family:"Quicksand",sans-serif;
  background:var(--bg); color:var(--text);
  transition:background .4s,color .4s;
}

/* CARD LOGIN */
.container.small{
  background:var(--card-bg);
  backdrop-filter:blur(12px);
  padding:40px 45px;
  border-radius:20px;
  box-shadow:0 6px 25px var(--shadow);
  width:350px;
  text-align:center;
}
h2{
  margin-bottom:25px;
  font-size:24px;
  font-weight:700;
  letter-spacing:.5px;
}
form{
  display:flex;
  flex-direction:column;
  gap:14px;
}
label{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  font-weight:600;
  font-size:15px;
}
input{
  width:100%;
  padding:10px 12px;
  border:1px solid #cbd5e1;
  border-radius:10px;
  font-size:14px;
  transition:border .2s, box-shadow .2s;
}
input:focus{
  border-color:var(--accent);
  outline:none;
  box-shadow:0 0 0 2px var(--accent);
}
button{
  margin-top:10px;
  background:var(--accent);
  color:#fff;
  border:none;
  padding:10px 18px;
  border-radius:25px;
  font-weight:700;
  cursor:pointer;
  transition:all .25s;
}
button:hover{
  background:var(--hover);
  color:var(--text);
  transform:translateY(-2px);
}

/* ALERT */
.alert{
  border-radius:10px;
  padding:10px 12px;
  margin-bottom:14px;
  border:1px solid #f87171;
  background:rgba(248,113,113,.1);
  color:#b91c1c;
  font-size:14px;
}

/* DARK MODE TOGGLE */
.toggle{
  position:absolute;
  top:20px; right:25px;
  border:none;
  background:var(--accent);
  color:#fff;
  padding:8px 14px;
  border-radius:20px;
  font-size:13px;
  font-weight:600;
  cursor:pointer;
  box-shadow:0 2px 6px var(--shadow);
  transition:all .3s;
}
.toggle:hover{opacity:.9; transform:scale(1.05);}
</style>
</head>
<body>

<button class="toggle" id="themeToggle">🌙 Dark</button>

<div class="container small">
  <h2>Login</h2>

  <?php if($err): ?>
    <div class="alert"><?=esc($err)?></div>
  <?php endif; ?>

  <form method="post">
    <label>Username
      <input name="username" required>
    </label>
    <label>Password
      <input type="password" name="password" required>
    </label>
    <button type="submit">Masuk</button>

    <a href="<?=base_url('warga_login.php')?>"
   class="btn-warga"
   style="
      display:block;
      margin-top:12px;
      padding:10px 18px;
      border-radius:25px;
      background:#38bdf8;
      color:#0f172a;
      font-weight:700;
      text-decoration:none;
      transition:0.25s;
   "
   onmouseover="this.style.transform='translateY(-2px)'; this.style.background='#dbeafe';"
   onmouseout="this.style.transform='translateY(0)'; this.style.background='#38bdf8';"
>
  Masuk Warga
</a>

  </form>
</div>

<script>
const toggle=document.getElementById('themeToggle');
const body=document.body;
if(localStorage.getItem('theme')==='dark'){
  body.classList.add('dark');
  toggle.textContent='☀️ Light';
}
toggle.addEventListener('click',()=>{
  body.classList.toggle('dark');
  const isDark=body.classList.contains('dark');
  toggle.textContent=isDark?'☀️ Light':'🌙 Dark';
  localStorage.setItem('theme',isDark?'dark':'light');
});
</script>

</body>
</html>
