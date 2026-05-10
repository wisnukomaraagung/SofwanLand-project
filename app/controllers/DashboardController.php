<?php
// app/controllers/DashboardController.php

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel();
    }

    public function index() {
        $totalProyek      = $this->model->getTotalProyek();
        $totalPekerja     = $this->model->getTotalPekerja();
        $totalBarangKeluar = $this->model->getTotalBarangKeluar();
        $totalBiaya       = $this->model->getTotalBiaya();
        $daftarProyek     = $this->model->getDaftarProyek();
        $biayaPerBulan    = $this->model->getBiayaPerBulan();
        $progressProyek   = $this->model->getProgressPerProyek();

        $pageTitle    = 'Dashboard';
        $pageSubtitle = 'Ringkasan data proyek kontraktor';

        require BASE_PATH . '/app/views/dashboard.php';
    }
}
