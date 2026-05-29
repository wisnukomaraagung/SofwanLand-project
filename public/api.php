<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once __DIR__ . '/../config/roles.php';

// ============ KONEKSI DATABASE ============
$host = 'localhost';
$dbname = 'kontraktor_db';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        // ============ GET KARYAWAN ============
        case 'getKaryawan':
            $stmt = $db->query("SELECT * FROM karyawan ORDER BY id DESC");
            $karyawan = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $karyawan]);
            break;
            
        // ============ ADD KARYAWAN ============
        case 'addKaryawan':
            if (!roleCanManage('absensi')) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
                break;
            }
            $nama = $_POST['nama'] ?? '';
            $jabatan = $_POST['jabatan'] ?? '';
            $no_telp = $_POST['no_telp'] ?? '';
            $gaji_per_hari = $_POST['gaji_per_hari'] ?? 50000;
            
            $stmt = $db->prepare("INSERT INTO karyawan (nama, jabatan, no_telp, gaji_per_hari) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $jabatan, $no_telp, $gaji_per_hari]);
            
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;
            
        // ============ DELETE KARYAWAN ============
        case 'deleteKaryawan':
            if (!roleCanManage('absensi')) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
                break;
            }
            $id = $_GET['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM karyawan WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;
            
        // ============ GET REKAP HARIAN ============
        case 'getRekapHarian':
            $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
            $stmt = $db->prepare("
                SELECT 
                    a.*, 
                    k.nama as nama_karyawan, 
                    k.jabatan,
                    DATE_FORMAT(a.jam_masuk, '%H:%i') as jam_masuk,
                    DATE_FORMAT(a.jam_keluar, '%H:%i') as jam_keluar,
                    TIME_FORMAT(TIMEDIFF(a.jam_keluar, a.jam_masuk), '%H:%i') as total_jam
                FROM absensi a
                JOIN karyawan k ON a.id_karyawan = k.id
                WHERE a.tanggal = ?
                ORDER BY a.id DESC
            ");
            $stmt->execute([$tanggal]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
            
        // ============ GET GAJI ============
        case 'getGaji':
            $bulan = $_GET['bulan'] ?? date('m');
            $tahun = $_GET['tahun'] ?? date('Y');
            $startDate = "$tahun-$bulan-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $stmt = $db->prepare("
                SELECT 
                    k.id, 
                    k.nama, 
                    k.gaji_per_hari,
                    COUNT(CASE WHEN a.status = 'hadir' AND a.jam_masuk IS NOT NULL THEN 1 END) as hadir,
                    COUNT(CASE WHEN a.status = 'izin' THEN 1 END) as izin,
                    COUNT(CASE WHEN a.status = 'sakit' THEN 1 END) as sakit,
                    COUNT(CASE WHEN a.status = 'alpha' THEN 1 END) as alpha,
                    COALESCE(SUM(a.lembur_jam), 0) as total_lembur,
                    (
                        COUNT(CASE WHEN a.status = 'hadir' AND a.jam_masuk IS NOT NULL THEN 1 END) * k.gaji_per_hari
                    ) + (
                        COALESCE(SUM(a.lembur_jam), 0) * k.gaji_per_hari * 1.5
                    ) as total_gaji
                FROM karyawan k
                LEFT JOIN absensi a ON k.id = a.id_karyawan 
                    AND a.tanggal BETWEEN ? AND ?
                    AND a.status = 'hadir'
                GROUP BY k.id
                ORDER BY k.nama ASC
            ");
            $stmt->execute([$startDate, $endDate]);
            $detail = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total_gaji = array_sum(array_column($detail, 'total_gaji'));
            
            echo json_encode(['success' => true, 'detail' => $detail, 'total_gaji' => $total_gaji]);
            break;
            
        // ============ GET STATUS HARI INI ============
        case 'getStatusHariIni':
            $today = date('Y-m-d');
            $stmt = $db->query("SELECT COUNT(*) FROM karyawan");
            $total = $stmt->fetchColumn();
            
            $stmt = $db->prepare("SELECT COUNT(DISTINCT id_karyawan) FROM absensi WHERE tanggal = ? AND jam_masuk IS NOT NULL");
            $stmt->execute([$today]);
            $hadir = $stmt->fetchColumn();
            
            echo json_encode(['success' => true, 'hadir' => (int)$hadir, 'belum_absen' => (int)($total - $hadir)]);
            break;
            
        // ============ RECOGNIZE FACE ============
        case 'recognizeFace':
            $input = json_decode(file_get_contents('php://input'), true);
            $faceDescriptor = $input['face_descriptor'] ?? [];
            
            // Ambil semua karyawan yang punya face_descriptor
            $stmt = $db->query("SELECT * FROM karyawan WHERE face_descriptor IS NOT NULL AND face_descriptor != ''");
            $karyawanList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $found = null;
            foreach ($karyawanList as $karyawan) {
                $savedDescriptor = json_decode($karyawan['face_descriptor'], true);
                if ($savedDescriptor && count($faceDescriptor) == count($savedDescriptor)) {
                    // Hitung jarak Euclidean
                    $distance = 0;
                    for ($i = 0; $i < count($faceDescriptor); $i++) {
                        $distance += pow($faceDescriptor[$i] - $savedDescriptor[$i], 2);
                    }
                    $distance = sqrt($distance);
                    
                    if ($distance < 0.6) {
                        $found = $karyawan;
                        break;
                    }
                }
            }
            
            if ($found) {
                echo json_encode(['success' => true, 'karyawan' => $found]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Wajah tidak dikenali']);
            }
            break;
            
        // ============ REGISTER FACE ============
        case 'registerFace':
            if (!roleCanManage('absensi')) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
                break;
            }
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? 0;
            $faceDescriptor = json_encode($input['face_descriptor'] ?? []);
            
            $stmt = $db->prepare("UPDATE karyawan SET face_descriptor = ? WHERE id = ?");
            $stmt->execute([$faceDescriptor, $id]);
            
            echo json_encode(['success' => true]);
            break;
            
        // ============ STORE ABSENSI ============
        case 'storeAbsensi':
            if (!roleCanManage('absensi')) {
                echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
                break;
            }
            $id_karyawan = $_POST['id_karyawan'] ?? 0;
            $id_proyek = $_POST['id_proyek'] ?? 0;
            $absensi_type = $_POST['absensi_type'] ?? 'masuk';
            $lembur_jam = $_POST['lembur_jam'] ?? 0;
            $keterangan = $_POST['keterangan'] ?? '';
            $face_snapshot = $_POST['face_snapshot'] ?? '';
            
            $today = date('Y-m-d');
            $now = date('H:i:s');
            
            // Simpan foto snapshot
            $foto = '';
            if ($face_snapshot) {
                $foto_data = explode(',', $face_snapshot);
                if (count($foto_data) > 1) {
                    $foto_base64 = $foto_data[1];
                    $foto_decoded = base64_decode($foto_base64);
                    $target_dir = __DIR__ . '/uploads/absensi/';
                    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                    $foto = time() . '_' . $id_karyawan . '.jpg';
                    file_put_contents($target_dir . $foto, $foto_decoded);
                }
            }
            
            if ($absensi_type == 'masuk') {
                // Cek sudah absen masuk
                $stmt = $db->prepare("SELECT id FROM absensi WHERE id_karyawan = ? AND tanggal = ? AND jam_masuk IS NOT NULL");
                $stmt->execute([$id_karyawan, $today]);
                if ($stmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Sudah absen masuk hari ini']);
                    break;
                }
                
                $stmt = $db->prepare("INSERT INTO absensi (id_karyawan, id_proyek, tanggal, jam_masuk, lembur_jam, keterangan, foto_absensi, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'hadir')");
                $result = $stmt->execute([$id_karyawan, $id_proyek, $today, $now, $lembur_jam, $keterangan, $foto]);
                echo json_encode(['success' => $result, 'message' => $result ? 'Absen masuk berhasil' : 'Gagal absen']);
            } else {
                // Update jam keluar
                $stmt = $db->prepare("UPDATE absensi SET jam_keluar = ?, lembur_jam = ?, keterangan = CONCAT(COALESCE(keterangan, ''), ' ', ?) WHERE id_karyawan = ? AND tanggal = ? AND jam_keluar IS NULL");
                $result = $stmt->execute([$now, $lembur_jam, $keterangan, $id_karyawan, $today]);
                echo json_encode(['success' => $result, 'message' => $result ? 'Absen keluar berhasil' : 'Gagal absen']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak ditemukan: ' . $action]);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>