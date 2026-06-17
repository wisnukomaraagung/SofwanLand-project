<?php
// app/models/PekerjaanModel.php

class PekerjaanModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all pekerjaan by proyek ID
     */
    public function getByProyekId(int $id_proyek): array {
        $stmt = $this->db->prepare("
            SELECT * FROM pekerjaan_proyek 
            WHERE id_proyek = ? 
            ORDER BY created_at ASC
        ");
        $stmt->execute([$id_proyek]);
        return $stmt->fetchAll();
    }

    /**
     * Get single pekerjaan by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM pekerjaan_proyek WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create new pekerjaan
     */
    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO pekerjaan_proyek (id_proyek, nama_pekerjaan, bobot, nilai_pekerjaan, progress_pekerjaan, status_pekerjaan)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['id_proyek'],
            $data['nama_pekerjaan'],
            $data['bobot'],
            $data['nilai_pekerjaan'],
            $data['progress_pekerjaan'] ?? 0,
            $data['status_pekerjaan'] ?? 'belum_mulai'
        ]);
    }

    /**
     * Update pekerjaan
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE pekerjaan_proyek 
            SET nama_pekerjaan = ?, bobot = ?, nilai_pekerjaan = ?, 
                progress_pekerjaan = ?, status_pekerjaan = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nama_pekerjaan'],
            $data['bobot'],
            $data['nilai_pekerjaan'],
            $data['progress_pekerjaan'],
            $data['status_pekerjaan'],
            $id
        ]);
    }

    /**
     * Delete pekerjaan
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM pekerjaan_proyek WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Update progress pekerjaan saja
     */
    public function updateProgress(int $id, float $progress): bool {
        $status = 'belum_mulai';
        if ($progress >= 100) {
            $status = 'selesai';
        } elseif ($progress > 0) {
            $status = 'dalam_proses';
        }
        
        $stmt = $this->db->prepare("
            UPDATE pekerjaan_proyek 
            SET progress_pekerjaan = ?, status_pekerjaan = ?
            WHERE id = ?
        ");
        return $stmt->execute([$progress, $status, $id]);
    }

    /**
     * Calculate total progress proyek (based on bobot)
     */
    public function calculateTotalProgress(int $id_proyek): float {
        $stmt = $this->db->prepare("
            SELECT SUM(bobot * progress_pekerjaan / 100) as total_progress
            FROM pekerjaan_proyek
            WHERE id_proyek = ?
        ");
        $stmt->execute([$id_proyek]);
        $result = $stmt->fetch();
        
        // Update proyek's progress_total
        $totalProgress = round($result['total_progress'] ?? 0, 2);
        $updateStmt = $this->db->prepare("UPDATE proyek SET progress_total = ? WHERE id = ?");
        $updateStmt->execute([$totalProgress, $id_proyek]);
        
        return $totalProgress;
    }

    /**
     * Get summary statistik untuk dashboard
     */
    public function getSummary(int $id_proyek): array {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_pekerjaan,
                SUM(CASE WHEN status_pekerjaan = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status_pekerjaan = 'dalam_proses' THEN 1 ELSE 0 END) as dalam_proses,
                SUM(CASE WHEN status_pekerjaan = 'belum_mulai' THEN 1 ELSE 0 END) as belum_mulai,
                SUM(nilai_pekerjaan) as total_nilai_rab,
                SUM(bobot * progress_pekerjaan / 100) as progress_total
            FROM pekerjaan_proyek
            WHERE id_proyek = ?
        ");
        $stmt->execute([$id_proyek]);
        $result = $stmt->fetch();
        
        return [
            'total_pekerjaan' => (int)($result['total_pekerjaan'] ?? 0),
            'selesai' => (int)($result['selesai'] ?? 0),
            'dalam_proses' => (int)($result['dalam_proses'] ?? 0),
            'belum_mulai' => (int)($result['belum_mulai'] ?? 0),
            'total_nilai_rab' => (float)($result['total_nilai_rab'] ?? 0),
            'progress_total' => round($result['progress_total'] ?? 0, 2)
        ];
    }
}