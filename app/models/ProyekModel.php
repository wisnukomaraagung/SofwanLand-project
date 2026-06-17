<?php
// app/models/ProyekModel.php

class ProyekModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        $sql = "
            SELECT
                p.id, p.nama_proyek, p.lokasi, p.status,
                p.tanggal_mulai, p.tanggal_selesai, p.nilai_kontrak,
                COALESCE(pr.persentase, 0) AS progress_terbaru,
                COUNT(DISTINCT a.id_karyawan) AS total_pekerja,
                COALESCE(SUM(DISTINCT bk_sum.total_bk), 0) AS total_barang_keluar,
                COALESCE(lk_sum.total_biaya, 0) AS total_biaya
            FROM proyek p
            LEFT JOIN (
                SELECT id_proyek, persentase FROM progress
                WHERE (id_proyek, tanggal) IN (
                    SELECT id_proyek, MAX(tanggal) FROM progress GROUP BY id_proyek
                )
            ) pr ON pr.id_proyek = p.id
            LEFT JOIN absensi a ON a.id_proyek = p.id
            LEFT JOIN (
                SELECT id_proyek, SUM(jumlah) AS total_bk FROM barang_keluar GROUP BY id_proyek
            ) bk_sum ON bk_sum.id_proyek = p.id
            LEFT JOIN (
                SELECT id_proyek, SUM(jumlah) AS total_biaya FROM laporan_keuangan
                WHERE tipe = 'pengeluaran' GROUP BY id_proyek
            ) lk_sum ON lk_sum.id_proyek = p.id
            GROUP BY p.id, p.nama_proyek, p.lokasi, p.status,
                     p.tanggal_mulai, p.tanggal_selesai, p.nilai_kontrak,
                     pr.persentase, lk_sum.total_biaya
            ORDER BY p.created_at DESC
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM proyek WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getDetail(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT
                p.*,
                COALESCE(pr.persentase, 0) AS progress_terbaru,
                COALESCE(pr.keterangan, '') AS progress_ket,
                COALESCE(pr.tanggal, '') AS progress_tgl,
                COUNT(DISTINCT a.id_karyawan) AS total_pekerja,
                COALESCE(SUM(bk.jumlah), 0) AS total_barang_keluar,
                COALESCE(lk_sum.total_biaya, 0) AS total_biaya,
                COALESCE(lk_sum.total_pemasukan, 0) AS total_pemasukan
            FROM proyek p
            LEFT JOIN (
                SELECT id_proyek, persentase, keterangan, tanggal FROM progress
                WHERE (id_proyek, tanggal) IN (
                    SELECT id_proyek, MAX(tanggal) FROM progress GROUP BY id_proyek
                )
            ) pr ON pr.id_proyek = p.id
            LEFT JOIN absensi a ON a.id_proyek = p.id
            LEFT JOIN barang_keluar bk ON bk.id_proyek = p.id
            LEFT JOIN (
                SELECT id_proyek,
                    SUM(CASE WHEN tipe = 'pengeluaran' THEN jumlah ELSE 0 END) AS total_biaya,
                    SUM(CASE WHEN tipe = 'pemasukan' THEN jumlah ELSE 0 END) AS total_pemasukan
                FROM laporan_keuangan GROUP BY id_proyek
            ) lk_sum ON lk_sum.id_proyek = p.id
            WHERE p.id = ?
            GROUP BY p.id, pr.persentase, pr.keterangan, pr.tanggal,
                     lk_sum.total_biaya, lk_sum.total_pemasukan
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getPekerjaanProyek($id_proyek)
    {
        $stmt = $this->db->prepare("
        SELECT *
        FROM pekerjaan_proyek
        WHERE id_proyek = ?
        ORDER BY id ASC
        ");
        
        $stmt->execute([$id_proyek]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProgressHistory(int $id): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM progress WHERE id_proyek = ? ORDER BY tanggal DESC LIMIT 10"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getKeuanganHistory(int $id): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM laporan_keuangan WHERE id_proyek = ? ORDER BY tanggal DESC LIMIT 10"
        );
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getBarangKeluar(int $id): array {
        $stmt = $this->db->prepare("
            SELECT bk.*, b.nama_barang, b.satuan
            FROM barang_keluar bk
            JOIN barang b ON b.id = bk.id_barang
            WHERE bk.id_proyek = ?
            ORDER BY bk.tanggal DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function getDokumentasi(int $id): array {
        $stmt = $this->db->prepare("
            SELECT *
            FROM dokumentasi
            WHERE id_proyek = ?
            ORDER BY tanggal DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
    
    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO proyek (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, nilai_kontrak, status, deskripsi)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['nama_proyek'], $data['lokasi'],
            $data['tanggal_mulai'], $data['tanggal_selesai'],
            $data['nilai_kontrak'], $data['status'], $data['deskripsi']
        ]);
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE proyek SET nama_proyek=?, lokasi=?, tanggal_mulai=?, tanggal_selesai=?,
            nilai_kontrak=?, status=?, deskripsi=? WHERE id=?
        ");
        return $stmt->execute([
            $data['nama_proyek'], $data['lokasi'],
            $data['tanggal_mulai'], $data['tanggal_selesai'],
            $data['nilai_kontrak'], $data['status'], $data['deskripsi'], $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM proyek WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
