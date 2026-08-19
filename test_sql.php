<?php
require 'config/database.php';
try {
    $db = getDB();
    $stmt = $db->prepare('SELECT b.id, b.nama_barang, COALESCE(SUM(bm.jumlah), 0) AS total_masuk FROM barang b LEFT JOIN barang_masuk bm ON bm.id_barang = b.id WHERE b.id_proyek = ? GROUP BY b.id ORDER BY b.nama_barang ASC');
    $stmt->execute([3]);
    print_r($stmt->fetchAll());
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
