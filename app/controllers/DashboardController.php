<?php
// app/controllers/DashboardController.php

class DashboardController {
    private $model;
    private $proyekModel;

    public function __construct() {
        $this->model = new DashboardModel();
        $this->proyekModel = new ProyekModel();
    }

    public function index() {
        $role = $_SESSION['user_role'] ?? 'admin';
        $perms = getRolePermissions();
        $pageTitle   = 'Dashboard';
        $pageSubtitle = $perms[$role]['dashboard_subtitle'] ?? 'Selamat datang di Sistem Manajemen';

        // Fetch all dashboard stats (same as before)
        $totalProyek         = $this->model->getTotalProyek();
        $proyekAktif         = $this->model->getProyekAktif();
        $totalBiaya          = $this->model->getTotalBiaya();
        $totalPekerja        = $this->model->getTotalPekerja();
        $totalBarangKeluar   = $this->model->getTotalBarangKeluar();
        $daftarProyek        = $this->model->getDaftarProyek();
        $biayaPerBulan       = $this->model->getBiayaPerBulan();
        $progressProyek      = $this->model->getProgressPerProyek();

        // Extra stats for admin role
        $totalKaryawan       = $this->model->getTotalKaryawan();
        $totalBarang         = $this->model->getTotalBarang();
        $absensiBulanIni     = $this->model->getAbsensiBulanIni();
        $barangStokRendah    = $this->model->getBarangStokRendah();
        $absensiPerStatus    = $this->model->getAbsensiPerStatus();
        $rekapAbsensi        = $this->model->getRekapAbsensiProyek();

        // Selected project (for highlighting)
        $selectedProjectId   = $_SESSION['selected_project_id'] ?? null;

        require BASE_PATH . '/app/views/dashboard.php';
    }

    public function selectProject() {
        $id = intval($_GET['id'] ?? 0);
        $db = getDB();
        $stmt = $db->prepare("SELECT id, nama_proyek FROM proyek WHERE id = ?");
        $stmt->execute([$id]);
        $proyek = $stmt->fetch();
        if ($proyek) {
            $_SESSION['selected_project_id']   = (int) $proyek['id'];
            $_SESSION['selected_project_name'] = $proyek['nama_proyek'];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Proyek "' . $proyek['nama_proyek'] . '" berhasil dipilih!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Proyek tidak ditemukan!'];
        }
        
        // After selecting a project, go directly to the project dashboard/detail
        header('Location: ' . BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . (int)$proyek['id']);
        exit;
    }

    public function clearProject() {
        unset($_SESSION['selected_project_id']);
        unset($_SESSION['selected_project_name']);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Filter proyek berhasil dinonaktifkan.'];
        
        // Selalu kembali ke dashboard utama
        header('Location: ' . BASE_URL . '/public/index.php?page=dashboard');
        exit;
    }
}
