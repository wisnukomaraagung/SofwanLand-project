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

    /** Riwayat transaksi dikelompokkan per proyek */
    public function getRiwayatPerProyek(?int $idProyek = null): array {
        $sql = "
            SELECT p.id, p.nama_proyek,
                COALESCE(SUM(CASE WHEN lk.tipe='pemasukan' THEN lk.jumlah ELSE 0 END), 0) AS pemasukan,
                COALESCE(SUM(CASE WHEN lk.tipe='pengeluaran' THEN lk.jumlah ELSE 0 END), 0) AS pengeluaran,
                COUNT(lk.id) AS jumlah_transaksi
            FROM proyek p
            LEFT JOIN laporan_keuangan lk ON lk.id_proyek = p.id
        ";
        $params = [];
        if ($idProyek !== null && $idProyek > 0) {
            $sql .= " WHERE p.id = ?";
            $params[] = $idProyek;
        }
        $sql .= " GROUP BY p.id, p.nama_proyek ORDER BY p.nama_proyek ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $proyeks = $stmt->fetchAll();

        $result = [];
        $txStmt = $this->db->prepare("
            SELECT lk.*, p.nama_proyek
            FROM laporan_keuangan lk
            JOIN proyek p ON p.id = lk.id_proyek
            WHERE lk.id_proyek = ?
            ORDER BY lk.tanggal DESC, lk.id DESC
        ");

        foreach ($proyeks as $proyek) {
            $txStmt->execute([(int) $proyek['id']]);
            $result[] = [
                'proyek'     => $proyek,
                'transaksi'  => $txStmt->fetchAll(),
            ];
        }

        return $result;
    }

    public function getSummary(?int $idProyek = null): array {
        if ($idProyek !== null && $idProyek > 0) {
            $stmt = $this->db->prepare("
                SELECT
                    SUM(CASE WHEN tipe='pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
                    SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran,
                    COUNT(*) AS total_transaksi
                FROM laporan_keuangan
                WHERE id_proyek = ?
            ");
            $stmt->execute([$idProyek]);
            return $stmt->fetch() ?: [
                'total_pemasukan' => 0,
                'total_pengeluaran' => 0,
                'total_transaksi' => 0
            ];
        }
        return $this->db->query("
            SELECT
                SUM(CASE WHEN tipe='pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan,
                SUM(CASE WHEN tipe='pengeluaran' THEN jumlah ELSE 0 END) AS total_pengeluaran,
                COUNT(*) AS total_transaksi
            FROM laporan_keuangan
        ")->fetch() ?: [
            'total_pemasukan' => 0,
            'total_pengeluaran' => 0,
            'total_transaksi' => 0
        ];
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
            INSERT INTO laporan_keuangan (id_proyek, tipe, jumlah, sumber, keterangan, tanggal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$data['id_proyek'], $data['tipe'], $data['jumlah'], $data['sumber'], $data['keterangan'], $data['tanggal']]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM laporan_keuangan WHERE id = ?")->execute([$id]);
    }
}
