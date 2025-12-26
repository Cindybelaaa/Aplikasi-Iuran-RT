<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
require_login();
$id = (int)($_GET['id']??0);
if($id){ $st=db()->prepare('DELETE FROM warga WHERE id=?'); $st->execute([$id]); }
header('Location: dashboard.php');