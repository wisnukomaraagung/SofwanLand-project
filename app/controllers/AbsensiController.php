<?php
// app/controllers/AbsensiController.php

class AbsensiController {
    private $model;

    public function __construct() {
        $this->model = new AbsensiModel();
    }

    public function index() {
        $absensiList  = $this->model->getAll();
        $karyawanList = $this->model->getKaryawan();
        $proyekList   = $this->model->getProyek();
        $rekapList    = $this->model->getRekapPerProyek();
        $pageTitle    = 'Absensi';
        $pageSubtitle = 'Catatan kehadiran karyawan per proyek';
        require BASE_PATH . '/app/views/absensi/index.php';
    }

    public function store() {
        $data = [
            'id_karyawan' => intval($_POST['id_karyawan'] ?? 0),
            'id_proyek'   => intval($_POST['id_proyek'] ?? 0),
            'tanggal'     => $_POST['tanggal'] ?? date('Y-m-d'),
            'status'      => $_POST['status'] ?? 'hadir',
            'keterangan'  => trim($_POST['keterangan'] ?? ''),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Absensi berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=absensi'); exit;
    }

    public function delete(int $id) {
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Absensi berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=absensi'); exit;
    }
}
