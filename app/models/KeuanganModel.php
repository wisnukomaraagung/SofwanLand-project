<?php
// app/models/KeuanganModel.php

class KeuanganModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("
            SELECT lk.*, p.nama_proyek
            FROM laporan_keuangan lk
            JOIN proyek p ON p.id = lk.id_proyek
            ORDER BY lk.tanggal DESC, lk.id DESC
            LIMIT 100
        ")->fetchAll();
    }

    public function getSummary(): array {
        return $this->db->query("
            SELECT
                SUM(CASE WHEN tipe='pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran,
                COUNT(*) AS total_transaksi
            FROM laporan_keuangan
        ")->fetch();
    }

    public function getSummaryPerProyek(): array {
        return $this->db->query("
            SELECT p.id, p.nama_proyek,
                SUM(CASE WHEN lk.tipe='pemasukan' THEN lk.jumlah ELSE 0 END) AS pemasukan,
                SUM(CASE WHEN lk.tipe='pengeluaran' THEN lk.jumlah ELSE 0 END) AS pengeluaran
            FROM proyek p
            LEFT JOIN laporan_keuangan lk ON lk.id_proyek = p.id
            GROUP BY p.id, p.nama_proyek
            ORDER BY pengeluaran DESC
        ")->fetchAll();
    }

    public function getProyek(): array {
        return $this->db->query("SELECT id, nama_proyek FROM proyek ORDER BY nama_proyek")->fetchAll();
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO laporan_keuangan (id_proyek, tipe, jumlah, keterangan, tanggal)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$data['id_proyek'], $data['tipe'], $data['jumlah'], $data['keterangan'], $data['tanggal']]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM laporan_keuangan WHERE id = ?")->execute([$id]);
    }
}
