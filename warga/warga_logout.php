<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/config/functions.php';

unset($_SESSION['warga_id'], $_SESSION['warga_nama']);

header('Location: '.base_url('login.php'));
exit;
