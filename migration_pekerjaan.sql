-- Migration: Tambah tabel pekerjaan_proyek dan progress_mingguan
-- Dijalankan karena commit e0f8f60 tidak menyertakan perubahan database.sql

USE kontraktor_db;

-- Tabel pekerjaan_proyek
CREATE TABLE IF NOT EXISTS pekerjaan_proyek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT NOT NULL,
    nama_pekerjaan VARCHAR(255) NOT NULL,
    bobot DECIMAL(5,2) DEFAULT 0,
    nilai_pekerjaan DECIMAL(15,2) DEFAULT 0,
    progress_pekerjaan DECIMAL(5,2) DEFAULT 0,
    status_pekerjaan ENUM('belum_mulai','dalam_proses','selesai') DEFAULT 'belum_mulai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id) ON DELETE CASCADE
);

-- Tabel progress_mingguan
CREATE TABLE IF NOT EXISTS progress_mingguan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT NOT NULL,
    minggu_ke INT NOT NULL,
    target_rencana DECIMAL(5,2) DEFAULT 0,
    realisasi DECIMAL(5,2) DEFAULT 0,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id) ON DELETE CASCADE
);

-- Tambah kolom progress_total ke tabel proyek jika belum ada
ALTER TABLE proyek 
    ADD COLUMN IF NOT EXISTS progress_total DECIMAL(5,2) DEFAULT 0;

-- Tambah kolom id_proyek ke tabel barang jika belum ada
ALTER TABLE barang
    ADD COLUMN IF NOT EXISTS id_proyek INT NULL;

-- Tambah FK hanya jika belum ada
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'kontraktor_db'
    AND TABLE_NAME = 'barang'
    AND CONSTRAINT_NAME = 'fk_barang_proyek'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE barang ADD CONSTRAINT fk_barang_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id) ON DELETE SET NULL',
    'SELECT ''FK barang sudah ada, skip'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Migration berhasil dijalankan!' AS status;
