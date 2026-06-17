<?php
// app/controllers/KurvaSController.php

class KurvaSController {
    private $model;
    private $proyekModel;
    private $progressModel;

    public function __construct() {
        $this->model = new ProgressMingguanModel();
        $this->proyekModel = new ProyekModel();
    }

    public function index() {
        // Get selected project
        $id_proyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        
        if (!$id_proyek) {
            $allProyek = $this->proyekModel->getAll();
            if (!empty($allProyek)) {
                $id_proyek = $allProyek[0]['id'];
            }
        }
        
        $proyek = $this->proyekModel->getById($id_proyek);
        $allProyek = $this->proyekModel->getAll();
        $kurvaData = $this->model->getKurvaSData($id_proyek);
        $progressList = $this->model->getByProyekId($id_proyek);
        
        // Hitung statistik
        $totalTarget = array_sum(array_column($kurvaData, 'target_rencana'));
        $totalRealisasi = array_sum(array_column($kurvaData, 'realisasi'));
        $selisihAkhir = !empty($kurvaData) ? end($kurvaData)['selisih'] : 0;
        
        $pageTitle = 'Kurva S';
        $pageSubtitle = 'Grafik Progress Rencana vs Realisasi';
        $pageAction = roleCanManage('kurva_s') ? 
            '<a href="' . BASE_URL . '/public/index.php?page=kurva_s&action=create&id_proyek=' . $id_proyek . '" class="btn btn-primary">+ Tambah Data Mingguan</a>' : '';
        
        require BASE_PATH . '/app/views/kurva_s/index.php';
    }

    public function create() {
        requireManagerPermission('kurva_s');
        
        $id_proyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        if (!$id_proyek) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pilih proyek terlebih dahulu'];
            header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s');
            exit;
        }
        
        $proyek = $this->proyekModel->getById($id_proyek);
        $allProyek = $this->proyekModel->getAll();
        
        $pageTitle = 'Tambah Data Progress Mingguan';
        $pageSubtitle = 'Input progress untuk Kurva S';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=kurva_s&id_proyek=' . $id_proyek . '" class="btn btn-secondary">← Kembali</a>';
        
        require BASE_PATH . '/app/views/kurva_s/form.php';
    }

    public function store() {
        requireManagerPermission('kurva_s');
        
        $id_proyek = (int)$_POST['id_proyek'];
        $data = [
            'id_proyek' => $id_proyek,
            'minggu_ke' => (int)$_POST['minggu_ke'],
            'target_rencana' => (float)$_POST['target_rencana'],
            'realisasi' => (float)$_POST['realisasi'],
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai']
        ];
        
        if ($this->model->create($data)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data progress mingguan berhasil ditambahkan'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan data'];
        }
        
        header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s&id_proyek=' . $id_proyek);
        exit;
    }

    public function edit(int $id) {
        requireManagerPermission('kurva_s');
        
        $progress = $this->model->getById($id);
        if (!$progress) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan'];
            header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s');
            exit;
        }
        
        $proyek = $this->proyekModel->getById($progress['id_proyek']);
        $allProyek = $this->proyekModel->getAll();
        
        $pageTitle = 'Edit Data Progress Mingguan';
        $pageSubtitle = 'Minggu ke-' . $progress['minggu_ke'];
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=kurva_s&id_proyek=' . $progress['id_proyek'] . '" class="btn btn-secondary">← Kembali</a>';
        
        require BASE_PATH . '/app/views/kurva_s/form.php';
    }

    public function update(int $id) {
        requireManagerPermission('kurva_s');
        
        $progress = $this->model->getById($id);
        if (!$progress) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan'];
            header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s');
            exit;
        }
        
        $data = [
            'minggu_ke' => (int)$_POST['minggu_ke'],
            'target_rencana' => (float)$_POST['target_rencana'],
            'realisasi' => (float)$_POST['realisasi'],
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai']
        ];
        
        if ($this->model->update($id, $data)) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil diperbarui'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal memperbarui data'];
        }
        
        header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s&id_proyek=' . $progress['id_proyek']);
        exit;
    }

    public function delete(int $id) {
        requireManagerPermission('kurva_s');
        
        $progress = $this->model->getById($id);
        if ($progress) {
            $id_proyek = $progress['id_proyek'];
            $this->model->delete($id);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Data berhasil dihapus'];
            header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s&id_proyek=' . $id_proyek);
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data tidak ditemukan'];
            header('Location: ' . BASE_URL . '/public/index.php?page=kurva_s');
        }
        exit;
    }
}