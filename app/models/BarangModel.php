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
            $stmt = $pdo->prepare("INSERT INTO barang_masuk (id_barang, jumlah, tanggal, keterangan) VALUES (?,?,?,?)");
            $stmt->execute([$data['id_barang'], $data['jumlah'], $data['tanggal'], $data['keterangan']]);
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
}
