<?php
// app/models/ProgressMingguanModel.php

class ProgressMingguanModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all progress mingguan by proyek ID
     */
    public function getByProyekId(int $id_proyek): array {
        $stmt = $this->db->prepare("
            SELECT * FROM progress_mingguan 
            WHERE id_proyek = ? 
            ORDER BY minggu_ke ASC
        ");
        $stmt->execute([$id_proyek]);
        return $stmt->fetchAll();
    }

    /**
     * Get single progress by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM progress_mingguan WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create new progress mingguan
     */
    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO progress_mingguan (id_proyek, minggu_ke, target_rencana, realisasi, tanggal_mulai, tanggal_selesai)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['id_proyek'],
            $data['minggu_ke'],
            $data['target_rencana'],
            $data['realisasi'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai']
        ]);
    }

    /**
     * Update progress mingguan
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE progress_mingguan 
            SET minggu_ke = ?, target_rencana = ?, realisasi = ?, 
                tanggal_mulai = ?, tanggal_selesai = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['minggu_ke'],
            $data['target_rencana'],
            $data['realisasi'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $id
        ]);
    }

    /**
     * Delete progress mingguan
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM progress_mingguan WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get data for Kurva S chart
     */
    public function getKurvaSData(int $id_proyek): array {
        $stmt = $this->db->prepare("
            SELECT 
                minggu_ke,
                target_rencana,
                realisasi,
                (realisasi - target_rencana) as selisih
            FROM progress_mingguan 
            WHERE id_proyek = ? 
            ORDER BY minggu_ke ASC
        ");
        $stmt->execute([$id_proyek]);
        return $stmt->fetchAll();
    }

    /**
     * Get latest progress untuk dashboard
     */
    public function getLatestProgress(int $id_proyek): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM progress_mingguan 
            WHERE id_proyek = ? 
            ORDER BY minggu_ke DESC 
            LIMIT 1
        ");
        $stmt->execute([$id_proyek]);
        return $stmt->fetch() ?: null;
    }
}