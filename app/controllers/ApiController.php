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
    header('Content-Type: application/json');

    try {
        $id_proyek  = $_GET['id_proyek'] ?? 0;
        $start      = $_GET['start']     ?? date('Y-m-01');
        $end        = $_GET['end']       ?? date('Y-m-t');

        if (!$id_proyek) {
            echo json_encode(['success' => false, 'message' => 'ID proyek tidak ditemukan']);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT
                k.id,
                k.nik,
                k.nama,
                k.jabatan,
                COALESCE(k.gaji_pokok, 0)                                           AS gaji_pokok,
                COUNT(CASE WHEN a.status = 'hadir'  THEN 1 END)                     AS total_hadir,
                COUNT(CASE WHEN a.status = 'izin'   THEN 1 END)                     AS total_izin,
                COUNT(CASE WHEN a.status = 'sakit'  THEN 1 END)                     AS total_sakit,
                COALESCE(SUM(CASE WHEN a.status = 'hadir'
                                  THEN COALESCE(a.lembur_jam, 0) ELSE 0 END), 0)   AS total_lembur
            FROM karyawan k
            JOIN proyek_karyawan pk ON pk.id_karyawan = k.id
            LEFT JOIN absensi a
                ON  a.id_karyawan = k.id
                AND a.id_proyek   = pk.id_proyek
                AND a.tanggal BETWEEN ? AND ?
            WHERE pk.id_proyek = ?
            GROUP BY k.id, k.nik, k.nama, k.jabatan, k.gaji_pokok
            ORDER BY k.nama ASC
        ");
        $stmt->execute([$start, $end, $id_proyek]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_hadir_all  = 0;
        $total_lembur_all = 0;
        $total_gaji_all   = 0;

        foreach ($data as &$k) {
            $gaji_pokok      = (float) $k['gaji_pokok'];
            $hadir           = (int)   $k['total_hadir'];
            $lembur          = (float) $k['total_lembur'];
            $gaji_harian     = $gaji_pokok > 0 ? $gaji_pokok / 30 : 0;
            $gaji_kehadiran  = $hadir  * $gaji_harian;
            $gaji_lembur     = $lembur * ($gaji_harian / 8) * 1.5;
            $total_gaji      = $gaji_kehadiran + $gaji_lembur;

            $k['gaji_pokok']     = round($gaji_pokok);
            $k['gaji_harian']    = round($gaji_harian);
            $k['gaji_kehadiran'] = round($gaji_kehadiran);
            $k['gaji_lembur']    = round($gaji_lembur);
            $k['total_gaji']     = round($total_gaji);
            $k['total_lembur']   = round($lembur, 1);

            $total_hadir_all  += $hadir;
            $total_lembur_all += $lembur;
            $total_gaji_all   += $total_gaji;
        }
        unset($k);

        echo json_encode([
            'success' => true,
            'data'    => $data,
            'summary' => [
                'total_karyawan' => count($data),
                'total_hadir'    => $total_hadir_all,
                'total_lembur'   => round($total_lembur_all, 1),
                'total_gaji'     => round($total_gaji_all),
            ]
        ]);

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
    header('Content-Type: application/json');

    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode([
                'success' => false,
                'message' => 'Input JSON tidak valid'
            ]);
            return;
        }

        $id_karyawan = intval($input['id_karyawan'] ?? 0);
        $face_descriptor = $input['face_descriptor'] ?? [];

        // =========================
        // VALIDASI
        // =========================

        if ($id_karyawan <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'ID karyawan tidak ditemukan'
            ]);
            return;
        }

        if (!is_array($face_descriptor) || count($face_descriptor) !== 128) {
            echo json_encode([
                'success' => false,
                'message' => 'Face descriptor harus berisi 128 angka'
            ]);
            return;
        }

        $newDescriptor = array_map('floatval', $face_descriptor);

        // =========================
        // CEK ID KARYAWAN
        // =========================

        $stmt = $this->db->prepare("
            SELECT id, nama, face_descriptor
            FROM karyawan
            WHERE id = ?
        ");

        $stmt->execute([$id_karyawan]);

        $karyawan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$karyawan) {
            echo json_encode([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan'
            ]);
            return;
        }

        // =========================
        // CEK WAJAH SUDAH TERDAFTAR
        // DI KARYAWAN LAIN
        // =========================

        $stmt = $this->db->prepare("
            SELECT id, nama, face_descriptor
            FROM karyawan
            WHERE face_descriptor IS NOT NULL
            AND face_descriptor != ''
            AND id != ?
        ");

        $stmt->execute([$id_karyawan]);

        $karyawanLain = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($karyawanLain as $lain) {

            $oldDescriptor = json_decode(
                $lain['face_descriptor'],
                true
            );

            if (!is_array($oldDescriptor)) {
                continue;
            }

            if (count($oldDescriptor) !== 128) {
                continue;
            }

            $oldDescriptor = array_map(
                'floatval',
                $oldDescriptor
            );

            // HITUNG JARAK WAJAH
            $distance = $this->euclideanDistance(
                $newDescriptor,
                $oldDescriptor
            );

            // =========================
            // WAJAH SAMA
            // =========================

            if ($distance < 0.60) {

                echo json_encode([
                    'success' => false,
                    'duplicate' => true,
                    'message' =>
                        'Wajah sudah terdaftar sebagai "' .
                        $lain['nama'] .
                        '". Silakan gunakan wajah karyawan yang sesuai.',
                    'registered_as' => $lain['nama'],
                    'distance' => round($distance, 4)
                ]);

                return;
            }
        }

        // =========================
        // SIMPAN WAJAH
        // =========================

        $face_descriptor_json = json_encode(
            $newDescriptor,
            JSON_NUMERIC_CHECK
        );

        $stmt = $this->db->prepare("
            UPDATE karyawan
            SET face_descriptor = ?
            WHERE id = ?
        ");

        $berhasil = $stmt->execute([
            $face_descriptor_json,
            $id_karyawan
        ]);

        if (!$berhasil) {
            echo json_encode([
                'success' => false,
                'message' => 'Gagal menyimpan face descriptor'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' =>
                'Wajah ' . $karyawan['nama'] .
                ' berhasil diregistrasi'
        ]);

    } catch (Exception $e) {

        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
    
    // ==================== STORE ABSENSI ====================
    
    public function storeAbsensi() {
        try {
            date_default_timezone_set('Asia/Jakarta');
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
                    echo json_encode([
                        'success' => false,
                        'message' => 'Anda sudah absen keluar hari ini pada jam ' . date('H:i:s', strtotime($existing['jam_keluar']))
                    ]);
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

        $id_proyek = intval($_GET['id_proyek'] ?? 0);

        if (!$id_proyek) {
            echo json_encode([
                'success' => false,
                'message' => 'ID proyek tidak tersedia'
            ]);
            return;
        }

        // =====================================================
        // 1. TOTAL KARYAWAN DALAM PROYEK
        // =====================================================

        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT pk.id_karyawan) AS total
            FROM proyek_karyawan pk
            WHERE pk.id_proyek = ?
        ");

        $stmt->execute([$id_proyek]);

        $total_karyawan = (int) (
            $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0
        );


        // =====================================================
        // 2. TOTAL HADIR BULAN INI
        // =====================================================

        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM absensi
            WHERE id_proyek = ?
              AND status = 'hadir'
              AND MONTH(tanggal) = MONTH(CURDATE())
              AND YEAR(tanggal) = YEAR(CURDATE())
        ");

        $stmt->execute([$id_proyek]);

        $total_hadir = (int) (
            $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0
        );


        // =====================================================
        // 3. HITUNG TOTAL GAJI BERDASARKAN KEHADIRAN
        // =====================================================
        //
        // Gaji harian = gaji_pokok / 30
        // Gaji hadir  = jumlah hadir × gaji harian
        // Gaji lembur = lembur × (gaji harian / 8) × 1.5
        //

        $stmt = $this->db->prepare("
            SELECT
                k.id,
                k.gaji_pokok,

                COUNT(
                    CASE
                        WHEN a.status = 'hadir'
                        THEN 1
                    END
                ) AS total_hadir_karyawan,

                COALESCE(
                    SUM(
                        CASE
                            WHEN a.status = 'hadir'
                            THEN COALESCE(a.lembur_jam, 0)
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_lembur

            FROM proyek_karyawan pk

            JOIN karyawan k
                ON k.id = pk.id_karyawan

            LEFT JOIN absensi a
                ON a.id_karyawan = k.id
                AND a.id_proyek = pk.id_proyek
                AND MONTH(a.tanggal) = MONTH(CURDATE())
                AND YEAR(a.tanggal) = YEAR(CURDATE())

            WHERE pk.id_proyek = ?

            GROUP BY
                k.id,
                k.gaji_pokok
        ");

        $stmt->execute([$id_proyek]);

        $data_gaji = $stmt->fetchAll(PDO::FETCH_ASSOC);


        // =====================================================
        // HITUNG TOTAL
        // =====================================================

        $total_gaji = 0;
        $total_gaji_kehadiran = 0;
        $total_gaji_lembur = 0;

        foreach ($data_gaji as $karyawan) {

            $gaji_pokok = floatval(
                $karyawan['gaji_pokok'] ?? 0
            );

            $jumlah_hadir = intval(
                $karyawan['total_hadir_karyawan'] ?? 0
            );

            $lembur_jam = floatval(
                $karyawan['total_lembur'] ?? 0
            );

            // Gaji per hari
            $gaji_harian = $gaji_pokok / 30;

            // Gaji berdasarkan kehadiran
            $gaji_kehadiran =
                $jumlah_hadir * $gaji_harian;

            // Gaji lembur
            $gaji_lembur =
                $lembur_jam *
                ($gaji_harian / 8) *
                1.5;

            // Total
            $total_karyawan_gaji =
                $gaji_kehadiran +
                $gaji_lembur;

            $total_gaji_kehadiran +=
                $gaji_kehadiran;

            $total_gaji_lembur +=
                $gaji_lembur;

            $total_gaji +=
                $total_karyawan_gaji;
        }


        // =====================================================
        // RESPONSE
        // =====================================================

        echo json_encode([
            'success' => true,
            'data' => [
                'total_karyawan' => $total_karyawan,
                'total_hadir' => $total_hadir,
                'total_gaji' => round($total_gaji),
                'gaji_kehadiran' => round($total_gaji_kehadiran),
                'gaji_lembur' => round($total_gaji_lembur)
            ]
        ]);

    } catch (Exception $e) {

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
    
    // ==================== HELPER ====================
    
    public function updateAbsensi() {
        header('Content-Type: application/json');
        try {
            $id         = $_POST['id'] ?? null;
            $jam_keluar = $_POST['jam_keluar'] ?? null;
            $lembur_jam = floatval($_POST['lembur_jam'] ?? 0);
            $status     = $_POST['status'] ?? 'hadir';
            $keterangan = $_POST['keterangan'] ?? '';

            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID absensi tidak ditemukan']);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE absensi
                SET jam_keluar  = ?,
                    lembur_jam  = ?,
                    status      = ?,
                    keterangan  = ?
                WHERE id = ?
            ");
            $stmt->execute([$jam_keluar ?: null, $lembur_jam, $status, $keterangan, $id]);

            echo json_encode(['success' => true, 'message' => 'Data absensi berhasil diupdate']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportExcel() {
        try {
            $proyek_id = $_GET['proyek_id'] ?? null;
            $mulai     = $_GET['mulai']     ?? date('Y-m-01');
            $selesai   = $_GET['selesai']   ?? date('Y-m-t');

            $query  = "SELECT a.*, k.nama AS nama_karyawan, k.jabatan, k.gaji_pokok,
                              p.nama_proyek
                       FROM absensi a
                       JOIN karyawan k ON a.id_karyawan = k.id
                       JOIN proyek   p ON a.id_proyek   = p.id
                       WHERE DATE(a.tanggal) BETWEEN ? AND ?";
            $params = [$mulai, $selesai];

            if ($proyek_id) {
                $query  .= " AND a.id_proyek = ?";
                $params[] = $proyek_id;
            }
            $query .= " ORDER BY a.tanggal DESC, k.nama ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nama proyek untuk filename
            $nama_file = 'absensi_' . date('Y-m-d');
            if ($proyek_id) {
                $p = $this->db->prepare("SELECT nama_proyek FROM proyek WHERE id = ?");
                $p->execute([$proyek_id]);
                $row = $p->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $nama_file = 'absensi_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $row['nama_proyek']) . '_' . date('Y-m-d');
                }
            }

            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nama_file . '.xls"');
            header('Cache-Control: max-age=0');

            echo "\xEF\xBB\xBF"; // BOM UTF-8 agar Excel baca karakter Indonesia
            echo '<table border="1" style="border-collapse:collapse;">';
            echo '<tr style="background:#222;color:#fff;font-weight:bold;">
                    <th>No</th><th>Tanggal</th><th>Karyawan</th><th>Jabatan</th>
                    <th>Proyek</th><th>Jam Masuk</th><th>Jam Keluar</th>
                    <th>Total Jam</th><th>Lembur (Jam)</th>
                    <th>Gaji Harian</th><th>Gaji Lembur</th><th>Total Gaji</th>
                    <th>Status</th><th>Keterangan</th>
                  </tr>';

            $grand_total = 0;
            foreach ($data as $i => $a) {
                $total_jam  = 0;
                $gaji_pokok = floatval($a['gaji_pokok'] ?? 0);
                $gaji_harian = $gaji_pokok > 0 ? $gaji_pokok / 30 : 0;

                if ($a['jam_masuk'] && $a['jam_keluar']) {
                    $selisih   = strtotime($a['jam_keluar']) - strtotime($a['jam_masuk']);
                    $total_jam = max(0, $selisih / 3600);
                }

                $lembur_jam  = floatval($a['lembur_jam'] ?? 0);
                $gaji_normal = $a['status'] === 'hadir' ? $gaji_harian : 0;
                $gaji_lembur = $lembur_jam * ($gaji_harian / 8) * 1.5;
                $total_gaji  = $gaji_normal + $gaji_lembur;
                $grand_total += $total_gaji;

                echo '<tr>';
                echo '<td>' . ($i + 1) . '</td>';
                echo '<td>' . date('d/m/Y', strtotime($a['tanggal'])) . '</td>';
                echo '<td>' . htmlspecialchars($a['nama_karyawan']) . '</td>';
                echo '<td>' . htmlspecialchars($a['jabatan']) . '</td>';
                echo '<td>' . htmlspecialchars($a['nama_proyek']) . '</td>';
                echo '<td>' . ($a['jam_masuk']  ? substr($a['jam_masuk'],  0, 5) : '-') . '</td>';
                echo '<td>' . ($a['jam_keluar'] ? substr($a['jam_keluar'], 0, 5) : '-') . '</td>';
                echo '<td>' . number_format($total_jam, 1) . '</td>';
                echo '<td>' . number_format($lembur_jam, 1) . '</td>';
                echo '<td>Rp ' . number_format($gaji_harian, 0, ',', '.') . '</td>';
                echo '<td>Rp ' . number_format($gaji_lembur, 0, ',', '.') . '</td>';
                echo '<td>Rp ' . number_format($total_gaji,  0, ',', '.') . '</td>';
                echo '<td>' . ucfirst($a['status']) . '</td>';
                echo '<td>' . htmlspecialchars($a['keterangan'] ?? '-') . '</td>';
                echo '</tr>';
            }

            echo '<tr style="font-weight:bold;background:#f0f0f0;">';
            echo '<td colspan="11" align="right">TOTAL KESELURUHAN:</td>';
            echo '<td>Rp ' . number_format($grand_total, 0, ',', '.') . '</td>';
            echo '<td colspan="2"></td>';
            echo '</tr>';
            echo '</table>';
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function euclideanDistance($desc1, $desc2) {

    if (!is_array($desc1) || !is_array($desc2)) {
        return 999;
    }

    if (count($desc1) !== 128 || count($desc2) !== 128) {
        return 999;
    }

    $sum = 0;

    for ($i = 0; $i < 128; $i++) {

        $a = floatval($desc1[$i]);
        $b = floatval($desc2[$i]);

        $difference = $a - $b;

        $sum += $difference * $difference;
    }

    return sqrt($sum);
}
}
?>