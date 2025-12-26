<?php
// Sesuaikan kredensial MySQL
const DB_HOST = '127.0.0.1';
const DB_NAME = 'iuran_rt';
const DB_USER = 'root';
const DB_PASS = '';

// Hitung base path app relatif terhadap document root, stabil walau dipanggil dari subfolder
function app_base_path() {
  static $base = null;
  if ($base !== null) return $base;
  $doc = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
  // __DIR__ = .../iuran-rt/config  → project root = satu level di atas
  $app = str_replace('\\','/', rtrim(realpath(__DIR__.'/..'), '/\\'));
  $base = str_replace($doc, '', $app);           // contoh hasil: "/iuran-rt"
  if ($base === '') $base = '/';                 // kalau app dipasang di root
  return $base;
}
function base_url($path = '') {
  $base = rtrim(app_base_path(), '/');
  return ($base === '' ? '' : $base) . '/' . ltrim($path, '/');
}