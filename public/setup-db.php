<?php
/**
 * Setup & Import Database
 * Jalankan di browser: http://localhost/SofwanLand-project/public/setup-db.php
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDB();
    echo "<h2>🔧 Database Setup Started...</h2>";

    // 1. Create database if not exists
    $db->exec("CREATE DATABASE IF NOT EXISTS kontraktor_db;");
    $db->exec("USE kontraktor_db;");
    echo "✅ Database selected/created<br>";

    // 2. Drop existing tables (optional - uncomment if you want fresh start)
    // $db->exec("DROP TABLE IF EXISTS dokumentasi, progress, laporan_keuangan, barang_keluar, barang_masuk, barang, absensi, karyawan, proyek, users;");

    // 3. Create all tables
    $sql = "
    -- Tabel Users
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','manager','owner','user') DEFAULT 'user',
        status ENUM('aktif','nonaktif') DEFAULT 'aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS proyek (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_proyek VARCHAR(255) NOT NULL,
        lokasi VARCHAR(255) NOT NULL,
        tanggal_mulai DATE,
        tanggal_selesai DATE,
        nilai_kontrak DECIMAL(15,2) DEFAULT 0,
        progress_total DECIMAL(5,2) DEFAULT 0,
        status ENUM('aktif','selesai','pending') DEFAULT 'aktif',
        deskripsi TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS karyawan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        jabatan VARCHAR(100),
        no_telp VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS absensi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_karyawan INT NOT NULL,
        id_proyek INT NOT NULL,
        tanggal DATE NOT NULL,
        status ENUM('hadir','izin','sakit','alpha') DEFAULT 'hadir',
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_karyawan) REFERENCES karyawan(id),
        FOREIGN KEY (id_proyek) REFERENCES proyek(id)
    );

    CREATE TABLE IF NOT EXISTS barang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_barang VARCHAR(255) NOT NULL,
        satuan VARCHAR(50),
        stok INT DEFAULT 0,
        harga_satuan DECIMAL(15,2) DEFAULT 0,
        id_proyek INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS barang_masuk (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_barang INT NOT NULL,
        jumlah INT NOT NULL,
        tanggal DATE NOT NULL,
        harga_satuan DECIMAL(15,2) DEFAULT 0,
        supplier VARCHAR(255),
        no_kuitansi VARCHAR(100),
        foto_kuitansi VARCHAR(255),
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_barang) REFERENCES barang(id)
    );

    CREATE TABLE IF NOT EXISTS barang_keluar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_barang INT NOT NULL,
        id_proyek INT NOT NULL,
        jumlah INT NOT NULL,
        tanggal DATE NOT NULL,
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_barang) REFERENCES barang(id),
        FOREIGN KEY (id_proyek) REFERENCES proyek(id)
    );

    CREATE TABLE IF NOT EXISTS laporan_keuangan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_proyek INT NOT NULL,
        tipe ENUM('pemasukan','pengeluaran') NOT NULL,
        jumlah DECIMAL(15,2) NOT NULL,
        sumber VARCHAR(255) DEFAULT NULL,
        keterangan TEXT,
        tanggal DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_proyek) REFERENCES proyek(id)
    );

    CREATE TABLE IF NOT EXISTS progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_proyek INT NOT NULL,
        persentase INT NOT NULL DEFAULT 0,
        keterangan TEXT,
        tanggal DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_proyek) REFERENCES proyek(id)
    );

    CREATE TABLE IF NOT EXISTS dokumentasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_proyek INT NOT NULL,
        judul VARCHAR(255),
        file_path VARCHAR(500),
        keterangan TEXT,
        tanggal DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_proyek) REFERENCES proyek(id)
    );

    CREATE TABLE IF NOT EXISTS import_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_proyek INT NOT NULL,
        jenis VARCHAR(50) NOT NULL,
        nama_file VARCHAR(255) NOT NULL,
        jumlah_data INT DEFAULT 0,
        status ENUM('berhasil','gagal') NOT NULL,
        pesan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_proyek) REFERENCES proyek(id) ON DELETE CASCADE
    );
    ";

    // Execute each statement
    foreach (explode(";", $sql) as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    $db->exec("ALTER TABLE barang ADD COLUMN IF NOT EXISTS id_proyek INT NULL");
    echo "✅ All tables created<br><br>";

    // 4. Clear and insert sample data
    $db->exec("DELETE FROM users;");
    $stmt = $db->prepare("INSERT INTO users (nama, email, password, role, status) VALUES (?, ?, ?, ?, 'aktif')");
    $stmt->execute(['Admin Kontraktor', 'admin@sofwan.com', 'admin123', 'admin']);
    $stmt->execute(['Manajer Proyek', 'manager@sofwan.com', 'manager123', 'manager']);
    $stmt->execute(['Owner Sofwan Land', 'owner@sofwan.com', 'owner123', 'owner']);
    $stmt->execute(['Pekerja', 'user@sofwan.com', 'user123', 'user']);
    echo "✅ User data inserted<br>";

    // 5. Insert proyek data
    $db->exec("DELETE FROM proyek;");
    $projects = [
        ['Pembangunan Gedung Perkantoran A', 'Jl. Sudirman No. 45, Jakarta', '2024-01-15', '2024-12-31', 2500000000, 'aktif', 'Pembangunan gedung 5 lantai untuk perkantoran'],
        ['Renovasi Jembatan Citarum', 'Kabupaten Bandung, Jawa Barat', '2024-03-01', '2024-09-30', 1800000000, 'aktif', 'Renovasi total jembatan sepanjang 120m'],
        ['Perumahan Griya Asri Cluster B', 'Bogor Selatan, Jawa Barat', '2024-02-10', '2025-02-10', 5200000000, 'aktif', 'Pembangunan 48 unit rumah tipe 45'],
        ['Perbaikan Jalan Provinsi', 'Sukabumi - Cianjur, Jawa Barat', '2023-11-01', '2024-04-30', 900000000, 'selesai', 'Perbaikan dan pengaspalan jalan sepanjang 12km'],
        ['Gedung Sekolah SD Negeri 01', 'Depok, Jawa Barat', '2024-04-01', '2024-10-31', 650000000, 'aktif', 'Pembangunan 6 ruang kelas baru'],
    ];
    $stmt = $db->prepare("INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, nilai_kontrak, status, deskripsi) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($projects as $p) {
        $stmt->execute($p);
    }
    echo "✅ Project data inserted<br>";

    // 6. Insert karyawan data
    $db->exec("DELETE FROM karyawan;");
    $employees = [
        ['Budi Santoso', 'Mandor', '081234567890'],
        ['Agus Purnomo', 'Tukang Batu', '081234567891'],
        ['Slamet Riyadi', 'Tukang Kayu', '081234567892'],
        ['Hendra Wijaya', 'Operator Alat Berat', '081234567893'],
        ['Dedi Kurniawan', 'Pekerja Umum', '081234567894'],
        ['Rohmat Hidayat', 'Tukang Besi', '081234567895'],
        ['Joko Susilo', 'Pekerja Umum', '081234567896'],
        ['Wahyu Setiawan', 'Mandor', '081234567897'],
        ['Eko Prasetyo', 'Tukang Batu', '081234567898'],
        ['Mulyono', 'Pekerja Umum', '081234567899'],
    ];
    $stmt = $db->prepare("INSERT INTO karyawan (nama, jabatan, no_telp) VALUES (?, ?, ?)");
    foreach ($employees as $e) {
        $stmt->execute($e);
    }
    echo "✅ Employee data inserted<br>";

    // 7. Insert barang data
    $db->exec("DELETE FROM barang;");
    $items = [
        ['Semen Portland', 'Sak', 500, 75000],
        ['Batu Bata Merah', 'Buah', 10000, 1200],
        ['Pasir Bangunan', 'M3', 200, 250000],
        ['Besi Beton 10mm', 'Batang', 1500, 85000],
        ['Besi Beton 12mm', 'Batang', 1200, 110000],
        ['Kayu Meranti 5x10', 'Batang', 300, 95000],
        ['Cat Tembok Putih', 'Kaleng', 150, 185000],
        ['Keramik 40x40', 'Dus', 200, 145000],
        ['Genteng Beton', 'Buah', 5000, 8500],
        ['Paku 10cm', 'Kg', 100, 25000],
    ];
    $stmt = $db->prepare("INSERT INTO barang (nama_barang, satuan, stok, harga_satuan) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmt->execute($item);
    }
    echo "✅ Barang data inserted<br>";

    echo "<br><h3>✅ Setup Selesai!</h3>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@sofwan.com / admin123</li>";
    echo "<li><strong>Manager:</strong> manager@sofwan.com / manager123</li>";
    echo "<li><strong>User (Pekerja):</strong> user@sofwan.com / user123</li>";
    echo "</ul>";
    echo "<p><a href='/?page=login'>➜ Go to Login Page</a></p>";

} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Database Configuration:</p>";
    echo "<pre>";
    echo "Host: " . DB_HOST . "\n";
    echo "User: " . DB_USER . "\n";
    echo "Database: " . DB_NAME . "\n";
    echo "</pre>";
    echo "<p><a href='javascript:history.back()'>← Back</a></p>";
}
?>
