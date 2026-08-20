<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    $queries = [
        "ALTER TABLE users MODIFY role ENUM('admin','manager','owner','user') DEFAULT 'user';",
        "INSERT INTO users (nama, email, password, role, status) SELECT 'Owner Sofwan Land', 'owner@sofwan.com', 'owner123', 'owner', 'aktif' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'owner@sofwan.com');",
        "ALTER TABLE proyek ADD COLUMN IF NOT EXISTS progress_total DECIMAL(5,2) DEFAULT 0;",
        "ALTER TABLE barang ADD COLUMN IF NOT EXISTS id_proyek INT NULL;",
        "ALTER TABLE barang_masuk ADD COLUMN harga_satuan DECIMAL(15,2) DEFAULT 0;",
        "ALTER TABLE barang_masuk ADD COLUMN supplier VARCHAR(255) NULL;",
        "ALTER TABLE barang_masuk ADD COLUMN no_kuitansi VARCHAR(100) NULL;",
        "ALTER TABLE barang_masuk ADD COLUMN foto_kuitansi VARCHAR(255) NULL;"
        ,"CREATE TABLE IF NOT EXISTS import_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_proyek INT NOT NULL,
            jenis VARCHAR(50) NOT NULL,
            nama_file VARCHAR(255) NOT NULL,
            jumlah_data INT DEFAULT 0,
            status ENUM('berhasil','gagal') NOT NULL,
            pesan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (id_proyek) REFERENCES proyek(id) ON DELETE CASCADE
        );"
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
