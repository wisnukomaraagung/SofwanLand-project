<?php
// app/controllers/PekerjaanController.php

class PekerjaanController {
    private $model;
    private $proyekModel;

    public function __construct() {
        $this->model = new PekerjaanModel();
        $this->proyekModel = new ProyekModel();
    }

    public function index() {
        // Get selected project from session or default to first
        $id_proyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        
        if (!$id_proyek) {
            $proyekList = $this->proyekModel->getAll();
            if (!empty($proyekList)) {
                $id_proyek = $proyekList[0]['id'];
            }
        }
        
        $proyek = $this->proyekModel->getById($id_proyek);
        $pekerjaanList = $this->model->getByProyekId($id_proyek);
        $summary = $this->model->getSummary($id_proyek);
        $allProyek = $this->proyekModel->getAll();
        
        $pageTitle = 'RAB & Pekerjaan';
        $pageSubtitle = 'Rencana Anggaran Biaya dan Progress Pekerjaan';
        $pageAction = roleCanManage('pekerjaan') ? 
            '<a href="' . BASE_URL . '/public/index.php?page=pekerjaan&action=create&id_proyek=' . $id_proyek . '" class="btn btn-primary">+ Tambah Pekerjaan</a>' : '';
        
        require BASE_PATH . '/app/views/pekerjaan/index.php';
    }

    public function create() {
        requireManagerPermission('pekerjaan');
        
        $id_proyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        if (!$id_proyek) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pilih proyek terlebih dahulu'];
            header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan');
            exit;
        }
        
        $proyek = $this->proyekModel->getById($id_proyek);
        $allProyek = $this->proyekModel->getAll();
        
        $pageTitle = 'Tambah Pekerjaan';
        $pageSubtitle = 'Tambah item RAB baru';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=pekerjaan&id_proyek=' . $id_proyek . '" class="btn btn-secondary">← Kembali</a>';
        
        require BASE_PATH . '/app/views/pekerjaan/form.php';
    }

    public function store() {
        requireManagerPermission('pekerjaan');
        
        $id_proyek = (int)$_POST['id_proyek'];
        $data = [
            'id_proyek' => $id_proyek,
            'nama_pekerjaan' => trim($_POST['nama_pekerjaan'] ?? ''),
            'bobot' => (float)($_POST['bobot'] ?? 0),
            'nilai_pekerjaan' => (float)str_replace(['.', ','], ['', '.'], $_POST['nilai_pekerjaan'] ?? 0),
            'progress_pekerjaan' => (float)($_POST['progress_pekerjaan'] ?? 0),
            'status_pekerjaan' => $_POST['status_pekerjaan'] ?? 'belum_mulai'
        ];
        
        if (empty($data['nama_pekerjaan'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama pekerjaan wajib diisi'];
            header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan&action=create&id_proyek=' . $id_proyek);
            exit;
        }
        
        if ($this->model->create($data)) {
            // Update total progress proyek
            $this->model->calculateTotalProgress($id_proyek);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pekerjaan berhasil ditambahkan'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menambahkan pekerjaan'];
        }
        
        header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan&id_proyek=' . $id_proyek);
        exit;
    }

    public function edit(int $id) {
        requireManagerPermission('pekerjaan');
        
        $pekerjaan = $this->model->getById($id);
        if (!$pekerjaan) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pekerjaan tidak ditemukan'];
            header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan');
            exit;
        }
        
        $proyek = $this->proyekModel->getById($pekerjaan['id_proyek']);
        $allProyek = $this->proyekModel->getAll();
        
        $pageTitle = 'Edit Pekerjaan';
        $pageSubtitle = htmlspecialchars($pekerjaan['nama_pekerjaan']);
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=pekerjaan&id_proyek=' . $pekerjaan['id_proyek'] . '" class="btn btn-secondary">← Kembali</a>';
        
        require BASE_PATH . '/app/views/pekerjaan/form.php';
    }

    public function update(int $id) {
        requireManagerPermission('pekerjaan');
        
        $pekerjaan = $this->model->getById($id);
        if (!$pekerjaan) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pekerjaan tidak ditemukan'];
            header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan');
            exit;
        }
        
        $data = [
            'nama_pekerjaan' => trim($_POST['nama_pekerjaan'] ?? ''),
            'bobot' => (float)($_POST['bobot'] ?? 0),
            'nilai_pekerjaan' => (float)str_replace(['.', ','], ['', '.'], $_POST['nilai_pekerjaan'] ?? 0),
            'progress_pekerjaan' => (float)($_POST['progress_pekerjaan'] ?? 0),
            'status_pekerjaan' => $_POST['status_pekerjaan'] ?? 'belum_mulai'
        ];
        
        if ($this->model->update($id, $data)) {
            // Update total progress proyek
            $this->model->calculateTotalProgress($pekerjaan['id_proyek']);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pekerjaan berhasil diperbarui'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal memperbarui pekerjaan'];
        }
        
        header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan&id_proyek=' . $pekerjaan['id_proyek']);
        exit;
    }

    public function delete(int $id) {
        requireManagerPermission('pekerjaan');
        
        $pekerjaan = $this->model->getById($id);
        if ($pekerjaan) {
            $id_proyek = $pekerjaan['id_proyek'];
            $this->model->delete($id);
            $this->model->calculateTotalProgress($id_proyek);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pekerjaan berhasil dihapus'];
        }
        
        header('Location: ' . BASE_URL . '/public/index.php?page=pekerjaan');
        exit;
    }

    public function updateProgressAjax() {
        // For AJAX progress update
        header('Content-Type: application/json');
        
        $id = (int)($_POST['id'] ?? 0);
        $progress = (float)($_POST['progress'] ?? 0);
        
        if ($id && $this->model->updateProgress($id, $progress)) {
            $pekerjaan = $this->model->getById($id);
            $totalProgress = $this->model->calculateTotalProgress($pekerjaan['id_proyek']);
            echo json_encode(['success' => true, 'total_progress' => $totalProgress]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}