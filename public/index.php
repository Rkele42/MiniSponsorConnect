<?php
declare(strict_types=1);

// Çok basit bootstrap
const APP_START = true;

// Autoload (ileride composer eklersen buraya koyarsın)
// require __DIR__ . '/../vendor/autoload.php';

// Basit sayfa
http_response_code(200);
header('Content-Type: text/html; charset=UTF-8');
?><!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>MiniSponsorConnect — Yeni Başlangıç</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;padding:40px;background:#0b1220;color:#e5e7eb} .card{max-width:720px;margin:0 auto;background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:24px} .muted{color:#94a3b8}</style>
</head>
<body>
  <div class="card">
    <h1>Yeni Proje Başladı 🚀</h1>
    <p>Bu, MiniSponsorConnect deposunun temiz bir başlangıç iskeletidir.</p>
    <ul>
      <li>public/index.php — giriş noktası</li>
      <li>src/ — uygulama kodları</li>
      <li>assets/ — statik dosyalar</li>
    </ul>
    <p class="muted">Devam: temel router, auth, veritabanı, admin paneli…</p>
  </div>
</body>
</html>
