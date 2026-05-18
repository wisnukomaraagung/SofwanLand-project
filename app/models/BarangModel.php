<?php
// app/models/BarangModel.php

class BarangModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("
            SELECT b.*,
                COALESCE(SUM(bm.jumlah), 0) AS total_masuk,
                COALESCE(SUM(bk.jumlah), 0) AS total_keluar
            FROM barang b
            LEFT JOIN barang_masuk bm ON bm.id_barang = b.id
            LEFT JOIN barang_keluar bk ON bk.id_barang = b.id
            GROUP BY b.id ORDER BY b.nama_barang ASC
        ")->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM barang WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getAllForSelect(): array {
        return $this->db->query("SELECT id, nama_barang, satuan, stok FROM barang ORDER BY nama_barang")->fetchAll();
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO barang (nama_barang, satuan, stok, harga_satuan) VALUES (?,?,?,?)");
        return $stmt->execute([$data['nama_barang'], $data['satuan'], $data['stok'], $data['harga_satuan']]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE barang SET nama_barang=?, satuan=?, harga_satuan=? WHERE id=?");
        return $stmt->execute([$data['nama_barang'], $data['satuan'], $data['harga_satuan'], $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM barang WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getMasuk(): array {
        return $this->db->query("
            SELECT bm.*, b.nama_barang, b.satuan
            FROM barang_masuk bm JOIN barang b ON b.id = bm.id_barang
            ORDER BY bm.tanggal DESC LIMIT 50
        ")->fetchAll();
    }

    public function getDashboardSummary(): array {
        $bulanIni = date('Y-m');
        
        try {
            $pengeluaran = $this->db->query("SELECT COALESCE(SUM(harga_satuan * jumlah), 0) FROM barang_masuk WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulanIni'")->fetchColumn();
        } catch (PDOException $e) {
            // Jika kolom belum ada (belum migrasi), jalankan ALTER TABLE
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $this->db->exec("ALTER TABLE barang_masuk ADD COLUMN harga_satuan DECIMAL(15,2) DEFAULT 0;");
                $this->db->exec("ALTER TABLE barang_masuk ADD COLUMN supplier VARCHAR(255) NULL;");
                $this->db->exec("ALTER TABLE barang_masuk ADD COLUMN no_kuitansi VARCHAR(100) NULL;");
                $this->db->exec("ALTER TABLE barang_masuk ADD COLUMN foto_kuitansi VARCHAR(255) NULL;");
                
                // Retry
                $pengeluaran = $this->db->query("SELECT COALESCE(SUM(harga_satuan * jumlah), 0) FROM barang_masuk WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulanIni'")->fetchColumn();
            } else {
                throw $e;
            }
        }
        
        $jenisBarang = $this->db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
        
        $transaksiMasuk = $this->db->query("SELECT COUNT(*) FROM barang_masuk")->fetchColumn();
        
        $transaksiKeluar = $this->db->query("SELECT COUNT(*) FROM barang_keluar")->fetchColumn();
        
        $totalPengeluaran = $this->db->query("SELECT COALESCE(SUM(harga_satuan * jumlah), 0) FROM barang_masuk")->fetchColumn();
        
        return [
            'pengeluaran_bulan_ini' => $pengeluaran,
            'jenis_barang' => $jenisBarang,
            'transaksi_masuk' => $transaksiMasuk,
            'transaksi_keluar' => $transaksiKeluar,
            'total_pengeluaran' => $totalPengeluaran
        ];
    }

    public function getKeluar(): array {
        return $this->db->query("
            SELECT bk.*, b.nama_barang, b.satuan, p.nama_proyek
            FROM barang_keluar bk
            JOIN barang b ON b.id = bk.id_barang
            JOIN proyek p ON p.id = bk.id_proyek
            ORDER BY bk.tanggal DESC LIMIT 50
        ")->fetchAll();
    }

    public function storeMasuk(array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO barang_masuk (id_barang, jumlah, tanggal, harga_satuan, supplier, no_kuitansi, foto_kuitansi, keterangan) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $data['id_barang'], $data['jumlah'], $data['tanggal'], 
                $data['harga_satuan'] ?? 0, $data['supplier'] ?? null, 
                $data['no_kuitansi'] ?? null, $data['foto_kuitansi'] ?? null, 
                $data['keterangan'] ?? null
            ]);
            $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function storeKeluar(array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO barang_keluar (id_barang, id_proyek, jumlah, tanggal, keterangan) VALUES (?,?,?,?,?)");
            $stmt->execute([$data['id_barang'], $data['id_proyek'], $data['jumlah'], $data['tanggal'], $data['keterangan']]);
            $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function getMasukById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM barang_masuk WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateMasuk(int $id, array $data): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $old = $pdo->query("SELECT id_barang, jumlah FROM barang_masuk WHERE id = $id")->fetch();
            if ($old) {
                $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$old['jumlah'], $old['id_barang']]);
                $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")->execute([$data['jumlah'], $data['id_barang']]);
                
                $stmt = $pdo->prepare("UPDATE barang_masuk SET id_barang=?, jumlah=?, tanggal=?, harga_satuan=?, supplier=?, no_kuitansi=?, keterangan=? WHERE id=?");
                $stmt->execute([
                    $data['id_barang'], $data['jumlah'], $data['tanggal'], 
                    $data['harga_satuan'] ?? 0, $data['supplier'] ?? null, 
                    $data['no_kuitansi'] ?? null, $data['keterangan'] ?? null,
                    $id
                ]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function deleteMasuk(int $id): bool {
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            $masuk = $pdo->query("SELECT id_barang, jumlah FROM barang_masuk WHERE id = $id")->fetch();
            if ($masuk) {
                $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?")->execute([$masuk['jumlah'], $masuk['id_barang']]);
                $pdo->prepare("DELETE FROM barang_masuk WHERE id = ?")->execute([$id]);
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}
