<?php
// app/controllers/DashboardController.php

class DashboardController {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel();
    }

    public function index() {
        $role = $_SESSION['user_role'] ?? 'admin';
        $perms = getRolePermissions();
        $pageTitle = 'Dashboard';
        $pageSubtitle = $perms[$role]['dashboard_subtitle'] ?? '';

        if ($role === 'manager') {
            $totalProyek       = $this->model->getTotalProyek();
            $proyekAktif       = $this->model->getProyekAktif();
            $totalBiaya        = $this->model->getTotalBiaya();
            $daftarProyek      = $this->model->getDaftarProyek();
            $biayaPerBulan     = $this->model->getBiayaPerBulan();
            $progressProyek    = $this->model->getProgressPerProyek();
            require BASE_PATH . '/app/views/dashboard_manager.php';
            return;
        }

        // Admin dashboard
        $totalKaryawan     = $this->model->getTotalKaryawan();
        $absensiBulanIni   = $this->model->getAbsensiBulanIni();
        $totalBarang       = $this->model->getTotalBarang();
        $totalBarangKeluar = $this->model->getTotalBarangKeluar();
        $barangStokRendah  = $this->model->getBarangStokRendah();
        $absensiPerStatus  = $this->model->getAbsensiPerStatus();
        $rekapAbsensi      = $this->model->getRekapAbsensiProyek();
        require BASE_PATH . '/app/views/dashboard_admin.php';
    }
}
