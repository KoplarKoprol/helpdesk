<?php

/**
 * Shortcut redirect ke halaman login. Path folder terdeteksi otomatis
 * dari lokasi file ini berada, supaya tetap berfungsi meski nama folder
 * project diubah (misal saat development/testing dengan nama sementara).
 */
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $basePath . '/public/login');
exit;