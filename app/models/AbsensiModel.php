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
            INSERT INTO absensi (id_karyawan, id_proyek, tanggal, status, keterangan, jam_masuk, jam_keluar)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['id_karyawan'], 
            $data['id_proyek'], 
            $data['tanggal'], 
            $data['status'], 
            $data['keterangan'],
            $data['jam_masuk'] ?? date('H:i:s'),
            $data['jam_keluar'] ?? null
        ]);
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

    // ==================== FUNGSI FACE RECOGNITION ====================
    
    /**
     * Recognisi wajah dengan validasi proyek
     * @param array $faceDescriptor - Face descriptor dari face-api.js
     * @param int $id_proyek - ID proyek yang sedang aktif
     * @return array
     */
    public function recognizeFace($faceDescriptor, $id_proyek) {
        try {
            // Ambil semua face descriptor dari karyawan di proyek yang SAMA
            $sql = "SELECT kf.*, k.nama, k.nik, k.jabatan, k.id as karyawan_id
                    FROM karyawan_face kf
                    JOIN karyawan k ON k.id = kf.id_karyawan
                    WHERE kf.id_proyek = :id_proyek";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_proyek', $id_proyek, PDO::PARAM_INT);
            $stmt->execute();
            $faces = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($faces)) {
                return ['success' => false, 'message' => 'Belum ada karyawan registrasi wajah di proyek ini'];
            }
            
            $bestMatch = null;
            $bestDistance = 0.5; // Threshold (semakin kecil semakin presisi)
            
            foreach ($faces as $face) {
                $storedDescriptor = json_decode($face['face_descriptor'], true);
                if (!$storedDescriptor) continue;
                
                $distance = $this->calculateDistance($faceDescriptor, $storedDescriptor);
                
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $face;
                }
            }
            
            if ($bestMatch) {
                return [
                    'success' => true,
                    'karyawan' => [
                        'id' => $bestMatch['karyawan_id'],
                        'nama' => $bestMatch['nama'],
                        'nik' => $bestMatch['nik'],
                        'jabatan' => $bestMatch['jabatan'],
                        'id_proyek' => $bestMatch['id_proyek']
                    ],
                    'distance' => $bestDistance
                ];
            }
            
            return ['success' => false, 'message' => 'Wajah tidak dikenali di proyek ini'];
            
        } catch (Exception $e) {
            error_log("Recognize Face Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Hitung jarak Euclidean antara dua face descriptor
     * @param array $desc1
     * @param array $desc2
     * @return float
     */
    private function calculateDistance($desc1, $desc2) {
        $sum = 0;
        for ($i = 0; $i < count($desc1); $i++) {
            $diff = $desc1[$i] - $desc2[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }
    
    /**
     * Cek apakah karyawan terdaftar di proyek tertentu
     * @param int $id_karyawan
     * @param int $id_proyek
     * @return bool
     */
    public function cekKaryawanDiProyek($id_karyawan, $id_proyek) {
        $sql = "SELECT id FROM karyawan WHERE id = :id_karyawan AND id_proyek = :id_proyek";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_karyawan', $id_karyawan, PDO::PARAM_INT);
        $stmt->bindParam(':id_proyek', $id_proyek, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() ? true : false;
    }
    
    /**
     * Store absensi dengan berbagai tipe (masuk/keluar)
     * @param array $data
     * @return array
     */
    public function storeAbsensi($data) {
        try {
            // Cek apakah karyawan terdaftar di proyek
            $isValid = $this->cekKaryawanDiProyek($data['id_karyawan'], $data['id_proyek']);
            if (!$isValid) {
                return ['success' => false, 'message' => 'Karyawan tidak terdaftar di proyek ini'];
            }
            
            // ABSEN MASUK
            if ($data['absensi_type'] == 'masuk') {
                // Cek apakah sudah absen masuk hari ini
                $sql = "SELECT id, jam_masuk FROM absensi 
                        WHERE id_karyawan = :id_karyawan 
                        AND id_proyek = :id_proyek
                        AND tanggal = CURDATE()";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_karyawan', $data['id_karyawan'], PDO::PARAM_INT);
                $stmt->bindParam(':id_proyek', $data['id_proyek'], PDO::PARAM_INT);
                $stmt->execute();
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    return ['success' => false, 'message' => 'Sudah absen masuk hari ini pada jam ' . $existing['jam_masuk']];
                }
                
                // Insert absen masuk
                $sql = "INSERT INTO absensi (id_karyawan, id_proyek, tanggal, jam_masuk, status, keterangan, face_snapshot) 
                        VALUES (:id_karyawan, :id_proyek, CURDATE(), CURTIME(), :status, :keterangan, :face_snapshot)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_karyawan', $data['id_karyawan'], PDO::PARAM_INT);
                $stmt->bindParam(':id_proyek', $data['id_proyek'], PDO::PARAM_INT);
                $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
                $stmt->bindParam(':keterangan', $data['keterangan'], PDO::PARAM_STR);
                $stmt->bindParam(':face_snapshot', $data['face_snapshot'], PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Absen masuk berhasil'];
                } else {
                    return ['success' => false, 'message' => 'Gagal menyimpan absen masuk'];
                }
            }
            
            // ABSEN KELUAR
            if ($data['absensi_type'] == 'keluar') {
                // Cek apakah sudah absen masuk hari ini
                $sql = "SELECT id FROM absensi 
                        WHERE id_karyawan = :id_karyawan 
                        AND id_proyek = :id_proyek
                        AND tanggal = CURDATE()";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_karyawan', $data['id_karyawan'], PDO::PARAM_INT);
                $stmt->bindParam(':id_proyek', $data['id_proyek'], PDO::PARAM_INT);
                $stmt->execute();
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$existing) {
                    return ['success' => false, 'message' => 'Belum absen masuk hari ini'];
                }
                
                // Update absen keluar
                $sql = "UPDATE absensi 
                        SET jam_keluar = CURTIME(), 
                            lembur_jam = :lembur_jam,
                            latitude = :latitude,
                            longitude = :longitude
                        WHERE id_karyawan = :id_karyawan 
                        AND id_proyek = :id_proyek
                        AND tanggal = CURDATE()";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':id_karyawan', $data['id_karyawan'], PDO::PARAM_INT);
                $stmt->bindParam(':id_proyek', $data['id_proyek'], PDO::PARAM_INT);
                $stmt->bindParam(':lembur_jam', $data['lembur_jam'], PDO::PARAM_STR);
                $stmt->bindParam(':latitude', $data['latitude'], PDO::PARAM_STR);
                $stmt->bindParam(':longitude', $data['longitude'], PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Absen keluar berhasil'];
                } else {
                    return ['success' => false, 'message' => 'Gagal menyimpan absen keluar'];
                }
            }
            
            return ['success' => false, 'message' => 'Tipe absensi tidak dikenal'];
            
        } catch (Exception $e) {
            error_log("Store Absensi Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update absensi (untuk edit jam keluar)
     * @param array $data
     * @return array
     */
    public function updateAbsensi($data) {
        try {
            $sql = "UPDATE absensi 
                    SET jam_keluar = :jam_keluar, 
                        lembur_jam = :lembur_jam, 
                        status = :status, 
                        keterangan = :keterangan 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':jam_keluar', $data['jam_keluar'], PDO::PARAM_STR);
            $stmt->bindParam(':lembur_jam', $data['lembur_jam'], PDO::PARAM_STR);
            $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindParam(':keterangan', $data['keterangan'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Absensi berhasil diupdate'];
            } else {
                return ['success' => false, 'message' => 'Gagal mengupdate absensi'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Register face descriptor untuk karyawan
     * @param int $id_karyawan
     * @param array $faceDescriptor
     * @param int $id_proyek
     * @return array
     */
    public function registerFace($id_karyawan, $faceDescriptor, $id_proyek) {
        try {
            // Validasi karyawan di proyek
            $sql = "SELECT id FROM karyawan WHERE id = :id_karyawan AND id_proyek = :id_proyek";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_karyawan', $id_karyawan, PDO::PARAM_INT);
            $stmt->bindParam(':id_proyek', $id_proyek, PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Karyawan tidak terdaftar di proyek ini'];
            }
            
            $faceDescriptorJson = json_encode($faceDescriptor);
            
            // Simpan ke tabel karyawan_face
            $sql = "INSERT INTO karyawan_face (id_karyawan, id_proyek, face_descriptor) 
                    VALUES (:id_karyawan, :id_proyek, :face_descriptor)
                    ON DUPLICATE KEY UPDATE face_descriptor = :face_descriptor";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_karyawan', $id_karyawan, PDO::PARAM_INT);
            $stmt->bindParam(':id_proyek', $id_proyek, PDO::PARAM_INT);
            $stmt->bindParam(':face_descriptor', $faceDescriptorJson, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Wajah berhasil diregistrasi untuk proyek ini'];
            } else {
                return ['success' => false, 'message' => 'Gagal menyimpan face descriptor'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>