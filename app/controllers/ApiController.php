<?php
// app/controllers/ApiController.php

require_once BASE_PATH . '/config/database.php';

class ApiController {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // ==================== ABSENSI ====================
    
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
    
    // ==================== FACE RECOGNITION ====================
    
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
    
    // ==================== STORE ABSENSI ====================
    
    public function storeAbsensi() {
        try {
            $id_karyawan = $_POST['id_karyawan'] ?? null;
            $id_proyek = $_POST['id_proyek'] ?? null;
            $absensi_type = $_POST['absensi_type'] ?? 'masuk';
            $tanggal = date('Y-m-d');
            $waktu = date('H:i:s');
            
            if (!$id_karyawan || !$id_proyek) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                return;
            }
            
            // ==================== VALIDASI PROYEK ====================

$cekProyek = $this->db->prepare("
    SELECT *
    FROM proyek_karyawan
    WHERE id_karyawan = ?
    AND id_proyek = ?
");

$cekProyek->execute([
    $id_karyawan,
    $id_proyek
]);

if (!$cekProyek->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'Anda tidak terdaftar pada proyek ini'
    ]);
    return;
}

// ==================== VALIDASI GPS ====================

$user_lat = $_POST['latitude'] ?? null;
$user_lng = $_POST['longitude'] ?? null;

if (!$user_lat || !$user_lng) {
    echo json_encode([
        'success' => false,
        'message' => 'GPS tidak aktif'
    ]);
    return;
}

$getProyek = $this->db->prepare("
    SELECT latitude, longitude, radius_meter
    FROM proyek
    WHERE id = ?
");

$getProyek->execute([$id_proyek]);

$proyek = $getProyek->fetch(PDO::FETCH_ASSOC);

if (!$proyek) {
    echo json_encode([
        'success' => false,
        'message' => 'Proyek tidak ditemukan'
    ]);
    return;
}

if (!$proyek['latitude'] || !$proyek['longitude']) {
    echo json_encode([
        'success' => false,
        'message' => 'Koordinat proyek belum diatur'
    ]);
    return;
}

// HITUNG JARAK (Haversine)
$earthRadius = 6371000;

$dLat = deg2rad($proyek['latitude'] - $user_lat);
$dLon = deg2rad($proyek['longitude'] - $user_lng);

$a =
    sin($dLat / 2) * sin($dLat / 2) +
    cos(deg2rad($user_lat)) *
    cos(deg2rad($proyek['latitude'])) *
    sin($dLon / 2) * sin($dLon / 2);

$c = 2 * atan2(sqrt($a), sqrt(1 - $a));

$jarak = $earthRadius * $c;

if ($jarak > $proyek['radius_meter']) {

    echo json_encode([
        'success' => false,
        'message' =>
            'Anda berada di luar area proyek (' .
            round($jarak) .
            ' meter)'
    ]);

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
                
                // Hitung lembur
                $lembur_jam = isset($_POST['lembur_jam']) ? floatval($_POST['lembur_jam']) : 0;
                if ($lembur_jam == 0) {
                    $jam_keluar_obj = new DateTime($waktu);
                    $jam_pulang_normal = new DateTime('17:00:00');
                    if ($jam_keluar_obj > $jam_pulang_normal) {
                        $diff = $jam_keluar_obj->diff($jam_pulang_normal);
                        $lembur_jam = $diff->h + ($diff->i / 60);
                        $lembur_jam = round($lembur_jam, 1);
                    }
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
    
    public function addKaryawan() {
        header('Content-Type: application/json');
        try {
            $nik = $_POST['nik'] ?? '';
            $nama = $_POST['nama'] ?? '';
            $jabatan = $_POST['jabatan'] ?? '';
            $gaji_pokok = $_POST['gaji_pokok'] ?? 5000000;
            $no_telp = $_POST['no_telp'] ?? '';
            $id_proyek = $_POST['id_proyek'] ?? null;
            
            if (empty($nik) || empty($nama) || empty($jabatan)) {
                echo json_encode(['success' => false, 'message' => 'NIK, Nama, dan Jabatan harus diisi']);
                return;
            }
            
            // Insert ke karyawan
            $query = "INSERT INTO karyawan (nik, nama, jabatan, gaji_pokok, no_telp) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nik, $nama, $jabatan, $gaji_pokok, $no_telp]);
            $karyawan_id = $this->db->lastInsertId();
            
            // Assign ke proyek jika ada proyek_id dan tabel proyek_karyawan ada
            if ($id_proyek) {
                $checkTable = $this->db->query("SHOW TABLES LIKE 'proyek_karyawan'");
                if ($checkTable->rowCount() > 0) {
                    $query2 = "INSERT INTO proyek_karyawan (id_proyek, id_karyawan) VALUES (?, ?)";
                    $stmt2 = $this->db->prepare($query2);
                    $stmt2->execute([$id_proyek, $karyawan_id]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Karyawan berhasil ditambahkan', 'id' => $karyawan_id]);
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
            
            // Hapus relasi di proyek_karyawan dulu
            $checkTable = $this->db->query("SHOW TABLES LIKE 'proyek_karyawan'");
            if ($checkTable->rowCount() > 0) {
                $stmt = $this->db->prepare("DELETE FROM proyek_karyawan WHERE id_karyawan = ?");
                $stmt->execute([$id]);
            }
            
            // Hapus absensi terkait
            $stmt = $this->db->prepare("DELETE FROM absensi WHERE id_karyawan = ?");
            $stmt->execute([$id]);
            
            // Hapus karyawan
            $query = "DELETE FROM karyawan WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Karyawan berhasil dihapus']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // ==================== PROYEK ====================
    
    public function getAllProyek() {
        header('Content-Type: application/json');
        try {
            $query = "SELECT * FROM proyek ORDER BY FIELD(status, 'aktif', 'selesai'), id DESC";
            $stmt = $this->db->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function getProyek() {
        header('Content-Type: application/json');
        try {
            $id = $_GET['id'] ?? 0;
            $query = "SELECT * FROM proyek WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function getKaryawanByProyek() {
        header('Content-Type: application/json');
        try {
            $id_proyek = $_GET['id_proyek'] ?? 0;
            
            $checkTable = $this->db->query("SHOW TABLES LIKE 'proyek_karyawan'");
            if ($checkTable->rowCount() > 0) {
                $query = "SELECT k.* FROM karyawan k 
                          JOIN proyek_karyawan pk ON k.id = pk.id_karyawan 
                          WHERE pk.id_proyek = ?
                          ORDER BY k.nama ASC";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$id_proyek]);
            } else {
                $query = "SELECT * FROM karyawan ORDER BY nama ASC";
                $stmt = $this->db->query($query);
            }
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function getAbsensiByProyek() {
        header('Content-Type: application/json');
        try {
            $id_proyek = $_GET['id_proyek'] ?? 0;
            $bulan_ini = isset($_GET['bulan_ini']);
            
            $query = "SELECT a.*, k.nama as nama_karyawan 
                      FROM absensi a 
                      JOIN karyawan k ON a.id_karyawan = k.id 
                      WHERE a.id_proyek = ?";
            
            if ($bulan_ini) {
                $query .= " AND MONTH(a.tanggal) = MONTH(CURDATE()) AND YEAR(a.tanggal) = YEAR(CURDATE())";
            }
            
            $query .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_proyek]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function getStatistikProyek() {
        header('Content-Type: application/json');
        try {
            $id_proyek = $_GET['id_proyek'] ?? 0;
            
            // Total karyawan
            $checkTable = $this->db->query("SHOW TABLES LIKE 'proyek_karyawan'");
            if ($checkTable->rowCount() > 0) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM proyek_karyawan WHERE id_proyek = ?");
                $stmt->execute([$id_proyek]);
                $total_karyawan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } else {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM karyawan");
                $total_karyawan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            }
            
            // Total hadir bulan ini
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total FROM absensi 
                WHERE id_proyek = ? AND status = 'hadir' 
                AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())
            ");
            $stmt->execute([$id_proyek]);
            $total_hadir = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Total gaji bulan ini
            $stmt = $this->db->prepare("
                SELECT SUM(
                    COALESCE(TIMESTAMPDIFF(HOUR, jam_masuk, jam_keluar), 0) * (COALESCE(k.gaji_pokok, 5000000) / 30 / 8) +
                    COALESCE(lembur_jam, 0) * (COALESCE(k.gaji_pokok, 5000000) / 30 / 8) * 1.5
                ) as total
                FROM absensi a
                JOIN karyawan k ON a.id_karyawan = k.id
                WHERE a.id_proyek = ? AND MONTH(a.tanggal) = MONTH(CURDATE()) AND YEAR(a.tanggal) = YEAR(CURDATE())
            ");
            $stmt->execute([$id_proyek]);
            $total_gaji = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_karyawan' => (int)$total_karyawan,
                    'total_hadir' => (int)$total_hadir,
                    'total_gaji' => (float)$total_gaji
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    // ==================== EXPORT EXCEL ====================
    
    public function exportExcel() {
        try {
            $proyek_id = $_GET['proyek_id'] ?? 0;
            
            // Get proyek info
            $stmt = $this->db->prepare("SELECT * FROM proyek WHERE id = ?");
            $stmt->execute([$proyek_id]);
            $proyek = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get absensi data
            $query = "SELECT a.*, k.nik, k.nama as nama_karyawan, k.jabatan, k.gaji_pokok 
                      FROM absensi a
                      JOIN karyawan k ON a.id_karyawan = k.id
                      WHERE a.id_proyek = ?
                      ORDER BY a.tanggal DESC, a.jam_masuk ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$proyek_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers untuk Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="absensi_' . ($proyek['nama_proyek'] ?? 'proyek') . '_' . date('Y-m-d') . '.xls"');
            
            echo '<table border="1">';
            echo '<tr style="background:#4472C4; color:white;">';
            echo '<th>No</th>';
            echo '<th>Tanggal</th>';
            echo '<th>NIK</th>';
            echo '<th>Nama Karyawan</th>';
            echo '<th>Jabatan</th>';
            echo '<th>Jam Masuk</th>';
            echo '<th>Jam Keluar</th>';
            echo '<th>Lembur (Jam)</th>';
            echo '<th>Status</th>';
            echo '<th>Keterangan</th>';
            echo '</tr>';
            
            $total_lembur = 0;
            foreach ($data as $i => $a) {
                $total_lembur += floatval($a['lembur_jam'] ?? 0);
                echo '<tr>';
                echo '<td>' . ($i+1) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($a['tanggal'])) . '</td>';
                echo '<td>' . htmlspecialchars($a['nik'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($a['nama_karyawan']) . '</td>';
                echo '<td>' . htmlspecialchars($a['jabatan']) . '</td>';
                echo '<td>' . ($a['jam_masuk'] ? date('H:i:s', strtotime($a['jam_masuk'])) : '-') . '</td>';
                echo '<td>' . ($a['jam_keluar'] ? date('H:i:s', strtotime($a['jam_keluar'])) : '-') . '</td>';
                echo '<td>' . number_format($a['lembur_jam'] ?? 0, 1) . '</td>';
                echo '<td>' . strtoupper($a['status']) . '</td>';
                echo '<td>' . htmlspecialchars($a['keterangan'] ?? '-') . '</td>';
                echo '</tr>';
            }
            
            echo '<tr style="background:#e0e0e0;">';
            echo '<td colspan="7" align="right"><strong>Total Lembur</strong></td>';
            echo '<td><strong>' . number_format($total_lembur, 1) . ' jam</strong></td>';
            echo '<td colspan="2"></td>';
            echo '</tr>';
            
            echo '<table>';
            exit;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
    // ==================== HELPER ====================
    
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