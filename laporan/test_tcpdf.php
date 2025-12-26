<?php
echo "<pre>";
echo "__DIR__           : ", __DIR__, PHP_EOL;
echo "DOCUMENT_ROOT     : ", $_SERVER['DOCUMENT_ROOT'] ?? '(null)', PHP_EOL;

// Cek apakah folder assets/tcpdf ada
$dir = realpath(__DIR__ . '/../assets/tcpdf');
echo "realpath assets/tcpdf : ", ($dir ?: '(NOT FOUND)'), PHP_EOL;

if ($dir && is_dir($dir)) {
  echo "Isi folder tcpdf :\n";
  foreach (scandir($dir) as $f) echo " - $f\n";
}

// Cek langsung file tcpdf.php
$path = realpath(__DIR__ . '/../assets/tcpdf/tcpdf.php');
echo "realpath tcpdf.php    : ", ($path ?: '(NOT FOUND)'), PHP_EOL;

echo "exists (raw path)     : ", (file_exists(__DIR__ . '/../assets/tcpdf/tcpdf.php') ? 'YES' : 'NO'), PHP_EOL;
echo "exists (realpath)     : ", ($path && file_exists($path) ? 'YES' : 'NO'), PHP_EOL;
