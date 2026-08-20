<?php
require_once __DIR__ . '/config/database.php';
$db = getDB();

// Test query baru total gaji dari laporan_keuangan
echo "=== TOTAL GAJI dari laporan_keuangan (bulan ini, semua proyek) ===\n";
$rows = $db->query("
    SELECT id_proyek, COALESCE(SUM(jumlah), 0) AS total
    FROM laporan_keuangan
    WHERE tipe = 'pengeluaran'
      AND kategori = 'Gaji'
    GROUP BY id_proyek
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  proyek {$r['id_proyek']}: Rp " . number_format($r['total'], 0, ',', '.') . "\n";
if (empty($rows)) echo "  (tidak ada entry kategori Gaji)\n";

echo "\n=== ALL pengeluaran kategori (cek apakah ada data) ===\n";
$rows = $db->query("
    SELECT kategori, COUNT(*) as jml, SUM(jumlah) as total
    FROM laporan_keuangan
    WHERE tipe = 'pengeluaran'
    GROUP BY kategori
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  kategori=" . ($r['kategori'] ?? 'NULL') . " jml={$r['jml']} total=" . number_format($r['total'],0,',','.') . "\n";

echo "\nDone.\n";
