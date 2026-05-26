<?php
// app/models/DashboardModel.php

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getTotalProyek(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM proyek")->fetchColumn();
    }

    public function getTotalPekerja(): int {
        return (int) $this->db->query("SELECT COUNT(DISTINCT id_karyawan) FROM absensi")->fetchColumn();
    }

    public function getTotalBarangKeluar(): int {
        return (int) $this->db->query("SELECT COALESCE(SUM(jumlah), 0) FROM barang_keluar")->fetchColumn();
    }

    public function getTotalBiaya(): float {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(jumlah), 0) FROM laporan_keuangan WHERE tipe = 'pengeluaran'"
        )->fetchColumn();
    }

    public function getDaftarProyek(): array {
        $sql = "
            SELECT
                p.id,
                p.nama_proyek,
                p.lokasi,
                p.status,
                p.nilai_kontrak,
                COALESCE(pr.persentase, 0) AS progress_terbaru,
                COALESCE(SUM(lk.jumlah), 0) AS total_biaya
            FROM proyek p
            LEFT JOIN (
                SELECT id_proyek, persentase
                FROM progress
                WHERE (id_proyek, tanggal) IN (
                    SELECT id_proyek, MAX(tanggal) FROM progress GROUP BY id_proyek
                )
            ) pr ON pr.id_proyek = p.id
            LEFT JOIN laporan_keuangan lk ON lk.id_proyek = p.id AND lk.tipe = 'pengeluaran'
            GROUP BY p.id, p.nama_proyek, p.lokasi, p.status, p.nilai_kontrak, pr.persentase
            ORDER BY p.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getBiayaPerBulan(): array {
        $sql = "
            SELECT
                DATE_FORMAT(tanggal, '%Y-%m') AS bulan,
                SUM(jumlah) AS total
            FROM laporan_keuangan
            WHERE tipe = 'pengeluaran'
            GROUP BY bulan
            ORDER BY bulan ASC
            LIMIT 12
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getTotalKaryawan(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM karyawan")->fetchColumn();
    }

    public function getTotalBarang(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM barang")->fetchColumn();
    }

    public function getAbsensiBulanIni(): int {
        return (int) $this->db->query("
            SELECT COUNT(*) FROM absensi
            WHERE YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) = MONTH(CURDATE())
        ")->fetchColumn();
    }

    public function getBarangStokRendah(int $batas = 10): array {
        $stmt = $this->db->prepare("
            SELECT id, nama_barang, stok, satuan
            FROM barang
            WHERE stok <= ?
            ORDER BY stok ASC
            LIMIT 8
        ");
        $stmt->execute([$batas]);
        return $stmt->fetchAll();
    }

    public function getAbsensiPerStatus(): array {
        return $this->db->query("
            SELECT status, COUNT(*) AS jumlah
            FROM absensi
            GROUP BY status
        ")->fetchAll();
    }

    public function getRekapAbsensiProyek(): array {
        return $this->db->query("
            SELECT p.nama_proyek,
                COUNT(DISTINCT a.id_karyawan) AS total_pekerja,
                SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS alpha
            FROM proyek p
            LEFT JOIN absensi a ON a.id_proyek = p.id
            GROUP BY p.id, p.nama_proyek
            ORDER BY p.nama_proyek ASC
            LIMIT 6
        ")->fetchAll();
    }

    public function getProyekAktif(): int {
        return (int) $this->db->query("SELECT COUNT(*) FROM proyek WHERE status = 'aktif'")->fetchColumn();
    }

    public function getProgressPerProyek(): array {
        $sql = "
            SELECT
                p.nama_proyek,
                COALESCE(pr.persentase, 0) AS persentase
            FROM proyek p
            LEFT JOIN (
                SELECT id_proyek, persentase
                FROM progress
                WHERE (id_proyek, tanggal) IN (
                    SELECT id_proyek, MAX(tanggal) FROM progress GROUP BY id_proyek
                )
            ) pr ON pr.id_proyek = p.id
            ORDER BY p.id ASC
        ";
        return $this->db->query($sql)->fetchAll();
    }
}
