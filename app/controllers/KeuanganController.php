<?php
// app/controllers/KeuanganController.php

class KeuanganController {
    private $model;

    public function __construct() {
        $this->model = new KeuanganModel();
    }

    public function index() {
        $selectedProyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : null;
        if ($selectedProyek !== null && $selectedProyek <= 0) {
            $selectedProyek = null;
        }

        $riwayatPerProyek = $this->model->getRiwayatPerProyek($selectedProyek);
        $summary          = $this->model->getSummary();
        $proyekList       = $this->model->getProyek();
        $pageTitle        = 'Keuangan';
        $pageSubtitle     = 'Laporan pemasukan dan pengeluaran per proyek';
        require BASE_PATH . '/app/views/keuangan/index.php';
    }

    private function keuanganRedirectUrl(): string {
        $url = BASE_URL . '/public/index.php?page=keuangan';
        $idProyek = intval($_GET['id_proyek'] ?? $_POST['id_proyek'] ?? 0);
        if ($idProyek > 0) {
            $url .= '&id_proyek=' . $idProyek;
        }
        return $url;
    }

    public function store() {
        requireManagerPermission('keuangan');
        $data = [
            'id_proyek'  => intval($_POST['id_proyek'] ?? 0),
            'tipe'       => $_POST['tipe'] ?? 'pengeluaran',
            'jumlah'     => floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0)),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dicatat.'];
        header('Location: ' . $this->keuanganRedirectUrl()); exit;
    }

    public function delete(int $id) {
        requireManagerPermission('keuangan');
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dihapus.'];
        header('Location: ' . $this->keuanganRedirectUrl()); exit;
    }

    public function exportExcel() {
        $selectedProyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : null;
        if ($selectedProyek !== null && $selectedProyek <= 0) {
            $selectedProyek = null;
        }
        $riwayatPerProyek = $this->model->getRiwayatPerProyek($selectedProyek);
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Keuangan_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Tanggal</th>";
        echo "<th>Proyek</th>";
        echo "<th>Tipe</th>";
        echo "<th>Jumlah</th>";
        echo "<th>Keterangan</th>";
        echo "</tr>";
        
        $no = 1;
        foreach ($riwayatPerProyek as $group) {
            $nama = $group['proyek']['nama_proyek'];
            echo "<tr><td colspan='6'><strong>" . htmlspecialchars($nama) . "</strong></td></tr>";
            if (empty($group['transaksi'])) {
                echo "<tr><td colspan='6'>Tidak ada transaksi</td></tr>";
                continue;
            }
            foreach ($group['transaksi'] as $k) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $k['tanggal'] . "</td>";
                echo "<td>" . htmlspecialchars($k['nama_proyek']) . "</td>";
                echo "<td>" . ucfirst($k['tipe']) . "</td>";
                echo "<td>" . $k['jumlah'] . "</td>";
                echo "<td>" . htmlspecialchars($k['keterangan'] ?? '-') . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
        exit;
    }
}
