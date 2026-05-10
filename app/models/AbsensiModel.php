<?php
// app/models/AbsensiModel.php

class AbsensiModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll(): array {
        return $this->db->query("
            SELECT a.*, k.nama AS nama_karyawan, k.jabatan, p.nama_proyek
            FROM absensi a
            JOIN karyawan k ON k.id = a.id_karyawan
            JOIN proyek p ON p.id = a.id_proyek
            ORDER BY a.tanggal DESC, a.id DESC
            LIMIT 100
        ")->fetchAll();
    }

    public function getByProyek(int $idProyek): array {
        $stmt = $this->db->prepare("
            SELECT a.*, k.nama AS nama_karyawan, k.jabatan
            FROM absensi a JOIN karyawan k ON k.id = a.id_karyawan
            WHERE a.id_proyek = ?
            ORDER BY a.tanggal DESC
        ");
        $stmt->execute([$idProyek]);
        return $stmt->fetchAll();
    }

    public function getKaryawan(): array {
        return $this->db->query("SELECT * FROM karyawan ORDER BY nama")->fetchAll();
    }

    public function getProyek(): array {
        return $this->db->query("SELECT id, nama_proyek FROM proyek ORDER BY nama_proyek")->fetchAll();
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO absensi (id_karyawan, id_proyek, tanggal, status, keterangan)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$data['id_karyawan'], $data['id_proyek'], $data['tanggal'], $data['status'], $data['keterangan']]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM absensi WHERE id = ?")->execute([$id]);
    }

    public function getRekapPerProyek(): array {
        return $this->db->query("
            SELECT p.nama_proyek,
                COUNT(a.id) AS total_absensi,
                COUNT(DISTINCT a.id_karyawan) AS total_pekerja,
                SUM(CASE WHEN a.status='hadir' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN a.status='izin' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN a.status='sakit' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN a.status='alpha' THEN 1 ELSE 0 END) AS alpha
            FROM absensi a JOIN proyek p ON p.id = a.id_proyek
            GROUP BY p.id, p.nama_proyek
            ORDER BY total_pekerja DESC
        ")->fetchAll();
    }
}
