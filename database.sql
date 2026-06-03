-- =============================================
-- DATABASE: kontraktor_db
-- =============================================


-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') DEFAULT 'staff',
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Proyek
CREATE TABLE IF NOT EXISTS proyek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_proyek VARCHAR(255) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    tanggal_mulai DATE,
    tanggal_selesai DATE,
    nilai_kontrak DECIMAL(15,2) DEFAULT 0,
    status ENUM('aktif','selesai','pending') DEFAULT 'aktif',
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Karyawan
CREATE TABLE IF NOT EXISTS karyawan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    jabatan VARCHAR(100),
    no_telp VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Absensi
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

-- Tabel Barang
CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(255) NOT NULL,
    satuan VARCHAR(50),
    stok INT DEFAULT 0,
    harga_satuan DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang Masuk
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

-- Tabel Barang Keluar
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

-- Tabel Laporan Keuangan
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

-- Tabel Progress
CREATE TABLE IF NOT EXISTS progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_proyek INT NOT NULL,
    persentase INT NOT NULL DEFAULT 0,
    keterangan TEXT,
    tanggal DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_proyek) REFERENCES proyek(id)
);

-- Tabel Dokumentasi
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

-- =============================================
-- DATA SAMPLE
-- =============================================
INSERT INTO users (nama, email, password, role, status) VALUES
('Admin Kontraktor', 'admin@sofwan.com', 'admin123', 'admin', 'aktif'),
('Manajer Proyek', 'manager@sofwan.com', 'manager123', 'manager', 'aktif'),
('Staff Keuangan', 'staff@sofwan.com', 'staff123', 'staff', 'aktif');

INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, nilai_kontrak, status, deskripsi) VALUES
('Pembangunan Gedung Perkantoran A', 'Jl. Sudirman No. 45, Jakarta', '2024-01-15', '2024-12-31', 2500000000, 'aktif', 'Pembangunan gedung 5 lantai untuk perkantoran'),
('Renovasi Jembatan Citarum', 'Kabupaten Bandung, Jawa Barat', '2024-03-01', '2024-09-30', 1800000000, 'aktif', 'Renovasi total jembatan sepanjang 120m'),
('Perumahan Griya Asri Cluster B', 'Bogor Selatan, Jawa Barat', '2024-02-10', '2025-02-10', 5200000000, 'aktif', 'Pembangunan 48 unit rumah tipe 45'),
('Perbaikan Jalan Provinsi', 'Sukabumi - Cianjur, Jawa Barat', '2023-11-01', '2024-04-30', 900000000, 'selesai', 'Perbaikan dan pengaspalan jalan sepanjang 12km'),
('Gedung Sekolah SD Negeri 01', 'Depok, Jawa Barat', '2024-04-01', '2024-10-31', 650000000, 'aktif', 'Pembangunan 6 ruang kelas baru');

INSERT INTO karyawan (nama, jabatan, no_telp) VALUES
('Budi Santoso', 'Mandor', '081234567890'),
('Agus Purnomo', 'Tukang Batu', '081234567891'),
('Slamet Riyadi', 'Tukang Kayu', '081234567892'),
('Hendra Wijaya', 'Operator Alat Berat', '081234567893'),
('Dedi Kurniawan', 'Pekerja Umum', '081234567894'),
('Rohmat Hidayat', 'Tukang Besi', '081234567895'),
('Joko Susilo', 'Pekerja Umum', '081234567896'),
('Wahyu Setiawan', 'Mandor', '081234567897'),
('Eko Prasetyo', 'Tukang Batu', '081234567898'),
('Mulyono', 'Pekerja Umum', '081234567899');

INSERT INTO absensi (id_karyawan, id_proyek, tanggal, status) VALUES
(1,1,'2024-05-01','hadir'),(2,1,'2024-05-01','hadir'),(3,1,'2024-05-01','izin'),
(4,2,'2024-05-01','hadir'),(5,2,'2024-05-01','hadir'),(6,2,'2024-05-02','hadir'),
(1,1,'2024-05-02','hadir'),(2,1,'2024-05-02','sakit'),(7,3,'2024-05-01','hadir'),
(8,3,'2024-05-01','hadir'),(9,3,'2024-05-02','hadir'),(10,4,'2024-05-01','hadir'),
(1,1,'2024-05-03','hadir'),(3,1,'2024-05-03','hadir'),(4,2,'2024-05-03','hadir'),
(5,5,'2024-05-01','hadir'),(6,5,'2024-05-02','hadir'),(7,5,'2024-05-03','hadir');

INSERT INTO barang (nama_barang, satuan, stok, harga_satuan) VALUES
('Semen Portland', 'Sak', 500, 75000),
('Batu Bata Merah', 'Buah', 10000, 1200),
('Pasir Bangunan', 'M3', 200, 250000),
('Besi Beton 10mm', 'Batang', 1500, 85000),
('Besi Beton 12mm', 'Batang', 1200, 110000),
('Kayu Meranti 5x10', 'Batang', 300, 95000),
('Cat Tembok Putih', 'Kaleng', 150, 185000),
('Keramik 40x40', 'Dus', 200, 145000),
('Genteng Beton', 'Buah', 5000, 8500),
('Paku 10cm', 'Kg', 100, 25000);

INSERT INTO barang_masuk (id_barang, jumlah, tanggal, keterangan) VALUES
(1,200,'2024-04-01','Pengiriman pertama'),(2,5000,'2024-04-01','Pengiriman pertama'),
(3,100,'2024-04-05','Pengiriman dari Quarry'),(4,500,'2024-04-10','Pembelian awal'),
(5,400,'2024-04-10','Pembelian awal'),(6,150,'2024-04-15','Stok kayu'),
(1,300,'2024-05-01','Pengiriman tambahan'),(7,80,'2024-04-20','Cat interior'),
(8,100,'2024-04-25','Keramik lantai'),(9,3000,'2024-03-15','Stok genteng');

INSERT INTO barang_keluar (id_barang, id_proyek, jumlah, tanggal, keterangan) VALUES
(1,1,80,'2024-04-05','Pondasi lantai 1'),(2,1,2000,'2024-04-06','Dinding lantai 1'),
(3,1,30,'2024-04-07','Campuran beton'),(4,1,200,'2024-04-10','Tulangan pondasi'),
(1,2,60,'2024-04-08','Perbaikan jembatan'),(3,2,25,'2024-04-09','Pengecoran'),
(5,2,150,'2024-04-11','Tulangan jembatan'),(1,3,100,'2024-04-12','Pondasi cluster B'),
(6,3,80,'2024-04-13','Bekisting'),(8,3,60,'2024-04-14','Lantai unit 1-10'),
(9,3,1500,'2024-04-20','Atap unit 1-20'),(1,4,40,'2024-03-15','Perbaikan jalan'),
(7,5,30,'2024-05-01','Pengecatan kelas'),(2,5,500,'2024-04-25','Dinding kelas');

INSERT INTO laporan_keuangan (id_proyek, tipe, jumlah, keterangan, tanggal) VALUES
(1,'pemasukan',500000000,'Termin 1 - 20%','2024-01-20'),
(1,'pengeluaran',120000000,'Biaya material awal','2024-02-01'),
(1,'pengeluaran',85000000,'Upah pekerja bulan Februari','2024-02-28'),
(1,'pemasukan',625000000,'Termin 2 - 25%','2024-03-15'),
(1,'pengeluaran',200000000,'Biaya material lanjutan','2024-03-20'),
(2,'pemasukan',360000000,'Termin 1 - 20%','2024-03-05'),
(2,'pengeluaran',150000000,'Biaya baja dan material','2024-03-15'),
(2,'pengeluaran',95000000,'Upah pekerja','2024-04-30'),
(3,'pemasukan',1040000000,'Termin 1 - 20%','2024-02-15'),
(3,'pengeluaran',350000000,'Material perumahan','2024-03-01'),
(3,'pengeluaran',280000000,'Upah pekerja bulan Maret','2024-03-31'),
(3,'pengeluaran',320000000,'Material lanjutan','2024-04-15'),
(4,'pemasukan',900000000,'Pembayaran penuh','2023-11-05'),
(4,'pengeluaran',420000000,'Material pengaspalan','2023-11-20'),
(4,'pengeluaran',310000000,'Upah dan alat berat','2024-01-15'),
(5,'pemasukan',325000000,'Termin 1 - 50%','2024-04-05'),
(5,'pengeluaran',180000000,'Material bangunan kelas','2024-04-15'),
(1,'pengeluaran',75000000,'Upah pekerja April','2024-04-30'),
(2,'pengeluaran',80000000,'Biaya alat berat','2024-05-01'),
(3,'pemasukan',520000000,'Termin 2 - 10%','2024-05-01');

INSERT INTO progress (id_proyek, persentase, keterangan, tanggal) VALUES
(1,10,'Persiapan lahan dan pondasi','2024-02-01'),
(1,25,'Struktur lantai 1 selesai','2024-03-15'),
(1,42,'Struktur lantai 2 sedang berjalan','2024-04-30'),
(2,15,'Pembongkaran dan persiapan','2024-03-20'),
(2,35,'Pengerjaan tiang jembatan','2024-04-15'),
(2,55,'Pemasangan girder selesai 60%','2024-05-01'),
(3,20,'Pondasi 12 unit selesai','2024-03-01'),
(3,38,'Struktur 12 unit selesai','2024-04-01'),
(3,52,'Dinding dan atap 20 unit selesai','2024-05-01'),
(4,75,'Pengaspalan 9km selesai','2023-12-15'),
(4,100,'Proyek selesai 100%','2024-04-25'),
(5,10,'Pembersihan lahan','2024-04-15'),
(5,30,'Pondasi 6 ruang kelas','2024-05-01');
