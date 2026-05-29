<?php
require_once BASE_PATH . '/app/core/Controller.php';

class ExportController extends Controller {
    
    public function exportExcel() {
        $model = new Absensi();
        
        $mulai = $_GET['mulai'] ?? date('Y-m-01');
        $selesai = $_GET['selesai'] ?? date('Y-m-t');
        $proyek_id = $_GET['proyek_id'] ?? null;
        
        $query = "SELECT a.*, k.nama as nama_karyawan, k.jabatan, k.gaji_pokok, p.nama_proyek 
                  FROM absensi a
                  JOIN karyawan k ON a.id_karyawan = k.id
                  JOIN proyek p ON a.id_proyek = p.id
                  WHERE DATE(a.tanggal) BETWEEN ? AND ?";
        $params = [$mulai, $selesai];
        
        if ($proyek_id) {
            $query .= " AND a.id_proyek = ?";
            $params[] = $proyek_id;
        }
        
        $query .= " ORDER BY a.tanggal DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="absensi_' . date('Y-m-d') . '.xls"');
        
        echo '<table border="1">';
        echo '<tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Proyek</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Total Jam</th>
                <th>Lembur (Jam)</th>
                <th>Gaji Pokok/Jam</th>
                <th>Gaji Lembur</th>
                <th>Total Gaji</th>
                <th>Status</th>
                <th>Keterangan</th>
              </tr>';
        
        $total_gaji_keseluruhan = 0;
        $gaji_per_jam = 15000;
        
        foreach ($data as $i => $a) {
            $total_jam = 0;
            if ($a['jam_masuk'] && $a['jam_keluar']) {
                $masuk = strtotime($a['jam_masuk']);
                $keluar = strtotime($a['jam_keluar']);
                $total_jam = ($keluar - $masuk) / 3600;
            }
            
            $gaji_normal = $total_jam * $gaji_per_jam;
            $lembur_jam = floatval($a['lembur_jam'] ?? 0);
            $gaji_lembur = $lembur_jam * $gaji_per_jam * 1.5;
            $total_gaji = $gaji_normal + $gaji_lembur;
            $total_gaji_keseluruhan += $total_gaji;
            
            echo '<tr>';
            echo '<td>' . ($i+1) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($a['tanggal'])) . '</td>';
            echo '<td>' . htmlspecialchars($a['nama_karyawan']) . '</td>';
            echo '<td>' . htmlspecialchars($a['jabatan']) . '</td>';
            echo '<td>' . htmlspecialchars($a['nama_proyek']) . '</td>';
            echo '<td>' . ($a['jam_masuk'] ? date('H:i:s', strtotime($a['jam_masuk'])) : '-') . '</td>';
            echo '<td>' . ($a['jam_keluar'] ? date('H:i:s', strtotime($a['jam_keluar'])) : '-') . '</td>';
            echo '<td>' . number_format($total_jam, 1) . '</td>';
            echo '<td>' . number_format($lembur_jam, 1) . '</td>';
            echo '<td>Rp ' . number_format($gaji_per_jam, 0, ',', '.') . '</td>';
            echo '<td>Rp ' . number_format($gaji_lembur, 0, ',', '.') . '</td>';
            echo '<td>Rp ' . number_format($total_gaji, 0, ',', '.') . '</td>';
            echo '<td>' . ucfirst($a['status']) . '</td>';
            echo '<td>' . htmlspecialchars($a['keterangan'] ?? '-') . '</td>';
            echo '</tr>';
        }
        
        echo '<tr style="font-weight:bold; background:#f0f0f0;">';
        echo '<td colspan="11" align="right">TOTAL KESELURUHAN:</td>';
        echo '<td>Rp ' . number_format($total_gaji_keseluruhan, 0, ',', '.') . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';
        
        echo '</table>';
        exit;
    }
}