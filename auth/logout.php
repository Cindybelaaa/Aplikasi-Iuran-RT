<?php
require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../config/functions.php';
session_destroy();
header('Location: '.base_url('auth/login.php'));