<?php
// app/controllers/ApiController.php

require_once BASE_PATH . '/config/database.php';

class ApiController {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getTodayAbsensi() {
        try {
            $query = "SELECT a.*, k.nama as nama_karyawan, k.jabatan, p.nama_proyek 
                      FROM absensi a
                      JOIN karyawan k ON a.id_karyawan = k.id
                      JOIN proyek p ON a.id_proyek = p.id
                      WHERE a.tanggal = CURDATE()
                      ORDER BY a.jam_masuk ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function getRekapGaji() {
        try {
            $query = "SELECT k.id, k.nama, k.jabatan, k.gaji_pokok,
                      COUNT(CASE WHEN a.status = 'hadir' THEN 1 END) as total_hadir,
                      SUM(COALESCE(a.lembur_jam, 0)) as total_lembur,
                      SUM(COALESCE(a.lembur_jam, 0) * (COALESCE(k.gaji_pokok, 5000000)/30/8) * 1.5 + 
                          (CASE WHEN a.jam_masuk IS NOT NULL AND a.jam_keluar IS NOT NULL 
                           THEN (TIMESTAMPDIFF(HOUR, a.jam_masuk, a.jam_keluar) * (COALESCE(k.gaji_pokok, 5000000)/30/8))
                           ELSE 0 END)) as total_gaji
                      FROM karyawan k
                      LEFT JOIN absensi a ON k.id = a.id_karyawan 
                          AND MONTH(a.tanggal) = MONTH(CURDATE()) 
                          AND YEAR(a.tanggal) = YEAR(CURDATE())
                      GROUP BY k.id, k.nama, k.jabatan, k.gaji_pokok";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function addKaryawan() {
        try {
            $nik = $_POST['nik'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $jabatan = $_POST['jabatan'] ?? '';
            $gaji_pokok = $_POST['gaji_pokok'] ?? 5000000;
            $no_telp = $_POST['no_telp'] ?? '';
            
            if (empty($nik) || empty($nama) || empty($jabatan)) {
                echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
                return;
            }
            
            $query = "INSERT INTO karyawan (nik, nama, jabatan, gaji_pokok, no_telp) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if ($stmt->execute([$nik, $nama, $jabatan, $gaji_pokok, $no_telp])) {
                echo json_encode(['success' => true, 'message' => 'Karyawan berhasil ditambahkan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan karyawan']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function recognizeFace() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
                return;
            }
            
            $face_descriptors = $input['face_descriptors'] ?? [];
            if (empty($face_descriptors)) {
                echo json_encode(['success' => false, 'message' => 'Face descriptor kosong']);
                return;
            }
            
            $query = "SELECT id, nama, jabatan, face_descriptor FROM karyawan WHERE face_descriptor IS NOT NULL AND face_descriptor != ''";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $karyawan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $best_match = null;
            $best_distance = 0.6;
            
            foreach ($karyawan_list as $karyawan) {
                $stored = json_decode($karyawan['face_descriptor'], true);
                if ($stored && is_array($stored) && count($stored) > 0) {
                    $distance = $this->euclideanDistance($face_descriptors, $stored);
                    if ($distance < $best_distance) {
                        $best_distance = $distance;
                        $best_match = $karyawan;
                    }
                }
            }
            
            if ($best_match) {
                echo json_encode([
                    'success' => true,
                    'karyawan' => [
                        'id' => $best_match['id'],
                        'nama' => $best_match['nama'],
                        'jabatan' => $best_match['jabatan']
                    ],
                    'match' => round(1 - $best_distance, 2)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Wajah tidak dikenali']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function registerFace() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
                return;
            }
            
            $id_karyawan = $input['id_karyawan'] ?? null;
            $face_descriptor = $input['face_descriptor'] ?? [];
            
            if (!$id_karyawan) {
                echo json_encode(['success' => false, 'message' => 'ID karyawan tidak ditemukan']);
                return;
            }
            
            if (empty($face_descriptor)) {
                echo json_encode(['success' => false, 'message' => 'Face descriptor kosong']);
                return;
            }
            
            $face_descriptor_json = json_encode($face_descriptor);
            
            $query = "UPDATE karyawan SET face_descriptor = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            if ($stmt->execute([$face_descriptor_json, $id_karyawan])) {
                echo json_encode(['success' => true, 'message' => 'Wajah berhasil diregistrasi']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal registrasi wajah']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function storeAbsensi() {
        try {
            $id_karyawan = $_POST['id_karyawan'] ?? null;
            $id_proyek = $_POST['id_proyek'] ?? null;
            $absensi_type = $_POST['absensi_type'] ?? 'masuk';
            $tanggal = date('Y-m-d');
            // Gunakan waktu real saat ini, bukan dari input user
            $waktu = date('H:i:s');
            
            if (!$id_karyawan || !$id_proyek) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                return;
            }
            
            // Cek apakah sudah absen hari ini
            $check = $this->db->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
            $check->execute([$id_karyawan, $tanggal]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);
            
            // Simpan foto
            $foto_name = null;
            if (!empty($_POST['face_snapshot'])) {
                $foto_data = $_POST['face_snapshot'];
                $foto_data = preg_replace('#^data:image/[^;]+;base64,#', '', $foto_data);
                $foto_data = str_replace(' ', '+', $foto_data);
                $foto_binary = base64_decode($foto_data);
                if ($foto_binary) {
                    $foto_name = 'absensi_' . $id_karyawan . '_' . date('Ymd_His') . '.jpg';
                    $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/absensi/';
                    if (!is_dir($upload_path)) mkdir($upload_path, 0777, true);
                    file_put_contents($upload_path . $foto_name, $foto_binary);
                }
            }
            
            if ($absensi_type == 'masuk') {
                if ($existing) {
                    echo json_encode(['success' => false, 'message' => 'Anda sudah absen masuk hari ini pada jam ' . date('H:i:s', strtotime($existing['jam_masuk']))]);
                    return;
                }
                $query = "INSERT INTO absensi (id_karyawan, id_proyek, tanggal, jam_masuk, foto_absensi, status) VALUES (?, ?, ?, ?, ?, 'hadir')";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$id_karyawan, $id_proyek, $tanggal, $waktu, $foto_name]);
                echo json_encode(['success' => true, 'message' => 'Absen masuk berhasil! Jam: ' . $waktu]);
            } else {
                if (!$existing) {
                    echo json_encode(['success' => false, 'message' => 'Anda belum absen masuk hari ini']);
                    return;
                }
                if ($existing['jam_keluar']) {
                    echo json_encode(['success' => false, 'message' => 'Anda sudah absen keluar hari ini pada jam ' . date('H:i:s', strtotime($existing['jam_keluar']))]);
                    return;
                }
                // Hitung lembur otomatis jika lebih dari jam 17:00
                $lembur_jam = 0;
                $jam_keluar_obj = new DateTime($waktu);
                $jam_pulang_normal = new DateTime('17:00:00');
                if ($jam_keluar_obj > $jam_pulang_normal) {
                    $diff = $jam_keluar_obj->diff($jam_pulang_normal);
                    $lembur_jam = $diff->h + ($diff->i / 60);
                    $lembur_jam = round($lembur_jam, 1);
                }
                
                $query = "UPDATE absensi SET jam_keluar = ?, lembur_jam = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$waktu, $lembur_jam, $existing['id']]);
                
                $message = 'Absen keluar berhasil! Jam: ' . $waktu;
                if ($lembur_jam > 0) {
                    $message .= ' (Lembur: ' . $lembur_jam . ' jam)';
                }
                echo json_encode(['success' => true, 'message' => $message]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ==================== CRUD KARYAWAN ====================

public function getKaryawan() {
    header('Content-Type: application/json');
    try {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $query = "SELECT * FROM karyawan WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan']);
            }
        } else {
            $query = "SELECT * FROM karyawan ORDER BY id DESC";
            $stmt = $this->db->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

public function updateKaryawan() {
    header('Content-Type: application/json');
    try {
        $id = $_POST['id'] ?? null;
        $nik = $_POST['nik'] ?? '';
        $nama = $_POST['nama'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        $gaji_pokok = $_POST['gaji_pokok'] ?? 5000000;
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID karyawan tidak ditemukan']);
            return;
        }
        
        $query = "UPDATE karyawan SET nik = ?, nama = ?, jabatan = ?, gaji_pokok = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([$nik, $nama, $jabatan, $gaji_pokok, $id])) {
            echo json_encode(['success' => true, 'message' => 'Karyawan berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupdate karyawan']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

public function deleteKaryawan() {
    header('Content-Type: application/json');
    try {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID karyawan tidak ditemukan']);
            return;
        }
        
        // Hapus data absensi terkait terlebih dahulu
        $deleteAbsensi = $this->db->prepare("DELETE FROM absensi WHERE id_karyawan = ?");
        $deleteAbsensi->execute([$id]);
        
        $query = "DELETE FROM karyawan WHERE id = ?";
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Karyawan berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus karyawan']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
    
    public function exportExcel() {
        try {
            $mulai = $_GET['mulai'] ?? date('Y-m-01');
            $selesai = $_GET['selesai'] ?? date('Y-m-t');
            $proyek_id = $_GET['proyek_id'] ?? null;
            
            $query = "SELECT a.*, k.nik, k.nama as nama_karyawan, k.jabatan, k.gaji_pokok, p.nama_proyek 
                      FROM absensi a
                      JOIN karyawan k ON a.id_karyawan = k.id
                      JOIN proyek p ON a.id_proyek = p.id
                      WHERE DATE(a.tanggal) BETWEEN ? AND ?";
            $params = [$mulai, $selesai];
            
            if ($proyek_id) {
                $query .= " AND a.id_proyek = ?";
                $params[] = $proyek_id;
            }
            
            $query .= " ORDER BY a.tanggal DESC, a.jam_masuk ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers untuk download Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="absensi_' . date('Y-m-d') . '.xls"');
            
            echo '<table border="1">';
            echo '<tr style="background:#f0f0f0;">';
            echo '<th>No</th>';
            echo '<th>Tanggal</th>';
            echo '<th>NIK</th>';
            echo '<th>Nama Karyawan</th>';
            echo '<th>Jabatan</th>';
            echo '<th>Proyek</th>';
            echo '<th>Jam Masuk</th>';
            echo '<th>Jam Keluar</th>';
            echo '<th>Total Jam</th>';
            echo '<th>Lembur (Jam)</th>';
            echo '<th>Gaji Pokok/Hari</th>';
            echo '<th>Gaji Lembur</th>';
            echo '<th>Total Gaji</th>';
            echo '<th>Status</th>';
            echo '<th>Keterangan</th>';
            echo '</tr>';
            
            $total_gaji_keseluruhan = 0;
            $total_lembur_keseluruhan = 0;
            
            foreach ($data as $i => $a) {
                // Hitung total jam kerja
                $total_jam = 0;
                if ($a['jam_masuk'] && $a['jam_keluar']) {
                    $masuk = strtotime($a['jam_masuk']);
                    $keluar = strtotime($a['jam_keluar']);
                    $total_jam = ($keluar - $masuk) / 3600;
                }
                
                $gaji_per_jam = ($a['gaji_pokok'] ?? 5000000) / 30 / 8;
                $gaji_normal = $total_jam * $gaji_per_jam;
                $lembur_jam = floatval($a['lembur_jam'] ?? 0);
                $gaji_lembur = $lembur_jam * $gaji_per_jam * 1.5;
                $total_gaji = $gaji_normal + $gaji_lembur;
                
                $total_gaji_keseluruhan += $total_gaji;
                $total_lembur_keseluruhan += $lembur_jam;
                
                $status_color = '';
                if ($a['status'] == 'hadir') $status_color = '#d4edda';
                elseif ($a['status'] == 'izin') $status_color = '#fff3cd';
                elseif ($a['status'] == 'sakit') $status_color = '#cce5ff';
                else $status_color = '#f8d7da';
                
                echo '<tr style="background:' . $status_color . ';">';
                echo '<td>' . ($i+1) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($a['tanggal'])) . '</td>';
                echo '<td>' . htmlspecialchars($a['nik'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($a['nama_karyawan']) . '</td>';
                echo '<td>' . htmlspecialchars($a['jabatan']) . '</td>';
                echo '<td>' . htmlspecialchars($a['nama_proyek']) . '</td>';
                echo '<td>' . ($a['jam_masuk'] ? date('H:i:s', strtotime($a['jam_masuk'])) : '-') . '</td>';
                echo '<td>' . ($a['jam_keluar'] ? date('H:i:s', strtotime($a['jam_keluar'])) : '-') . '</td>';
                echo '<td>' . number_format($total_jam, 1) . '</td>';
                echo '<td>' . number_format($lembur_jam, 1) . '</td>';
                echo '<td>Rp ' . number_format($gaji_per_jam * 8, 0, ',', '.') . '</td>';
                echo '<td>Rp ' . number_format($gaji_lembur, 0, ',', '.') . '</td>';
                echo '<td><strong>Rp ' . number_format($total_gaji, 0, ',', '.') . '</strong></td>';
                echo '<td>' . strtoupper($a['status']) . '</td>';
                echo '<td>' . htmlspecialchars($a['keterangan'] ?? '-') . '</td>';
                echo '</tr>';
            }
            
            // Baris Total
            echo '<tr style="background:#e0e0e0; font-weight:bold;">';
            echo '<td colspan="9" align="right">TOTAL</td>';
            echo '<td>' . number_format($total_lembur_keseluruhan, 1) . ' jam</td>';
            echo '<td colspan="2"></td>';
            echo '<td>Rp ' . number_format($total_gaji_keseluruhan, 0, ',', '.') . '</td>';
            echo '<td colspan="2"></td>';
            echo '</tr>';
            
            echo '</table>';
            exit;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    private function euclideanDistance($desc1, $desc2) {
        $sum = 0;
        $len = min(count($desc1), count($desc2));
        for ($i = 0; $i < $len; $i++) {
            $sum += pow(floatval($desc1[$i]) - floatval($desc2[$i]), 2);
        }
        return sqrt($sum);
    }
}
?>