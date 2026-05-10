<?php
// app/controllers/KeuanganController.php

class KeuanganController {
    private $model;

    public function __construct() {
        $this->model = new KeuanganModel();
        session_start();
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
}
