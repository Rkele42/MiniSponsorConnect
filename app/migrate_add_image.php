<?php
require __DIR__.'/db.php';
$pdo = rkd_pdo();
$cols = $pdo->query("PRAGMA table_info(listings)")->fetchAll();
$names = array_column($cols, 'name');
if (!in_array('image', $names, true)) {
  $pdo->exec("ALTER TABLE listings ADD COLUMN image TEXT DEFAULT NULL");
  echo "Added listings.image\n";
} else {
  echo "listings.image already exists\n";
}
