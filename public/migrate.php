<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $queries = [
        "ALTER TABLE barang_masuk ADD COLUMN harga_satuan DECIMAL(15,2) DEFAULT 0;",
        "ALTER TABLE barang_masuk ADD COLUMN supplier VARCHAR(255) NULL;",
        "ALTER TABLE barang_masuk ADD COLUMN no_kuitansi VARCHAR(100) NULL;",
        "ALTER TABLE barang_masuk ADD COLUMN foto_kuitansi VARCHAR(255) NULL;"
    ];

    foreach ($queries as $q) {
        try {
            $db->exec($q);
            echo "Success: $q<br>";
        } catch (Exception $e) {
            echo "Skipped (or error): " . $e->getMessage() . "<br>";
        }
    }
    echo "Migration completed successfully!";
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage();
}
