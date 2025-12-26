<?php
session_start();


function db(){
static $pdo;
if(!$pdo){
$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
$pdo = new PDO($dsn, DB_USER, DB_PASS, [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
}
return $pdo;
}


function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }


function is_logged_in(){ return !empty($_SESSION['user_id']); }
function require_login(){ if(!is_logged_in()){ header('Location: '.base_url('auth/login.php')); exit; } }


function flash($key, $val=null){
if($val!==null){ $_SESSION['flash'][$key]=$val; return; }
if(isset($_SESSION['flash'][$key])){ $v=$_SESSION['flash'][$key]; unset($_SESSION['flash'][$key]); return $v; }
return null;
}

function is_warga_logged_in(){
  return !empty($_SESSION['warga_id']);
}

function require_warga_login(){
  if(!is_warga_logged_in()){
    header('Location: '.base_url('warga_login.php'));
    exit;
  }
}
