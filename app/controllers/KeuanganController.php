<?php
// app/controllers/KeuanganController.php

class KeuanganController {
    private $model;

    public function __construct() {
        $this->model = new KeuanganModel();
    }

    public function index() {
        $keuanganList    = $this->model->getAll();
        $summary         = $this->model->getSummary();
        $summaryPerProyek = $this->model->getSummaryPerProyek();
        $proyekList      = $this->model->getProyek();
        $pageTitle       = 'Keuangan';
        $pageSubtitle    = 'Laporan pemasukan dan pengeluaran proyek';
        require BASE_PATH . '/app/views/keuangan/index.php';
    }

    public function store() {
        $data = [
            'id_proyek'  => intval($_POST['id_proyek'] ?? 0),
            'tipe'       => $_POST['tipe'] ?? 'pengeluaran',
            'jumlah'     => floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0)),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan'); exit;
    }

    public function delete(int $id) {
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=keuangan'); exit;
    }

    public function exportExcel() {
        $keuanganList = $this->model->getAll();
        
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
        foreach ($keuanganList as $k) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . $k['tanggal'] . "</td>";
            echo "<td>" . htmlspecialchars($k['nama_proyek']) . "</td>";
            echo "<td>" . ucfirst($k['tipe']) . "</td>";
            echo "<td>" . $k['jumlah'] . "</td>";
            echo "<td>" . htmlspecialchars($k['keterangan'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
}
