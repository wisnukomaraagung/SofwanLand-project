<?php
// app/controllers/ProyekController.php

class ProyekController {
    private $model;

    public function __construct() {
        $this->model = new ProyekModel();
    }

    public function index() {
        $proyekList = $this->model->getAll();
        $pageTitle = 'Proyek';
        $pageSubtitle = 'Manajemen daftar proyek kontraktor';
        $pageAction = roleCanManage('proyek') ? '<a href="' . BASE_URL . '/public/index.php?page=proyek&action=create" class="btn btn-primary">+ Tambah Proyek</a>' : '';
        require BASE_PATH . '/app/views/proyek/index.php';
    }

   public function detail(int $id) {
        $proyek = $this->model->getDetail($id);

        if (!$proyek) {
            header('Location: ' . BASE_URL . '/public/index.php?page=proyek');
            exit;
        }

        // =========================
        // DATA PROGRESS
        // =========================
        $progressHistory = $this->model->getProgressHistory($id);

        // =========================
        // DATA KEUANGAN
        // =========================
        $keuanganHistory = $this->model->getKeuanganHistory($id);

        $pengeluaranList = array_map(function($item) {
            return [
                'tanggal'    => $item['tanggal'],
                'kategori'   => $item['kategori'] ?? 'Lainnya',
                'keterangan' => $item['keterangan'] ?? '-',
                'nominal'    => (float)$item['jumlah']
            ];
        }, $keuanganHistory);

        // =========================
        // DATA PEKERJAAN / RAB
        // =========================
        if (!class_exists('PekerjaanModel')) {
            require_once BASE_PATH . '/app/models/PekerjaanModel.php';
        }

        $pekerjaanModel = new PekerjaanModel();

        $pekerjaanList = $pekerjaanModel->getByProyekId($id);
        $summaryPekerjaan = $pekerjaanModel->getSummary($id);

        /*
        * Format data pekerjaan agar bisa dipakai
        * oleh grafik dan bagian Analytics.
        */
        $rincianPekerjaan = array_map(function($item) {
            return [
                'nama'          => $item['nama_pekerjaan'],
                'progress'      => (float)$item['progress_pekerjaan'],
                'status'        => $item['status_pekerjaan'],
                'bobot'         => (float)$item['bobot'],
                'nilai'         => (float)$item['nilai_pekerjaan']
            ];
        }, $pekerjaanList);

        $totalPekerjaan = $summaryPekerjaan['total_pekerjaan'];
        $pekerjaanSelesai = $summaryPekerjaan['selesai'];

        // =========================
        // DOKUMENTASI
        // =========================
        $dokumentasiList = $this->model->getDokumentasi($id);
        $totalDokumentasi = count($dokumentasiList);
        $dokumentasiBaru = $totalDokumentasi;

        // =========================
        // KURVA S
        // =========================
        if (!class_exists('ProgressMingguanModel')) {
            require_once BASE_PATH . '/app/models/ProgressMingguanModel.php';
        }

        $progressMingguanModel = new ProgressMingguanModel();

        $kurvaData = $progressMingguanModel->getKurvaSData($id);
        $progressList = $progressMingguanModel->getByProyekId($id);

        // =========================
        // TARGET PROGRESS
        // =========================
        $targetProgress = 0;

        if (!empty($kurvaData)) {
            $lastKurva = end($kurvaData);
            $targetProgress = (float)$lastKurva['target_rencana'];
        }

        // =========================
        // TREND PROGRESS
        // =========================
        $progressTrend = 0;

        if (count($progressHistory) >= 2) {
            $latest = (float)$progressHistory[count($progressHistory) - 1]['persentase'];
            $previous = (float)$progressHistory[count($progressHistory) - 2]['persentase'];

            $progressTrend = round($latest - $previous, 1);
        }

        $pageTitle = htmlspecialchars($proyek['nama_proyek']);
        $pageSubtitle = 'Detail proyek';

        require BASE_PATH . '/app/views/proyek/detail.php';
    }

    public function create() {
        requireManagerPermission('proyek');
        $pageTitle = 'Tambah Proyek';
        $pageSubtitle = 'Buat proyek baru';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=proyek" class="btn btn-secondary">← Kembali</a>';
        $proyek = null;
        require BASE_PATH . '/app/views/proyek/form.php';
    }

    public function store() {
        requireManagerPermission('proyek');
        $data = [
            'nama_proyek'    => trim($_POST['nama_proyek'] ?? ''),
            'lokasi'         => trim($_POST['lokasi'] ?? ''),
            'tanggal_mulai'  => $_POST['tanggal_mulai'] ?? null,
            'tanggal_selesai'=> $_POST['tanggal_selesai'] ?? null,
            'nilai_kontrak'  => floatval(str_replace(['.', ','], ['', '.'], $_POST['nilai_kontrak'] ?? 0)),
            'status'         => $_POST['status'] ?? 'aktif',
            'deskripsi'      => trim($_POST['deskripsi'] ?? ''),
        ];
        if (empty($data['nama_proyek'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama proyek wajib diisi.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=proyek&action=create');
            exit;
        }
        $this->model->create($data);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Proyek berhasil ditambahkan.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=proyek');
        exit;
    }

    public function edit(int $id) {
        requireManagerPermission('proyek');
        $proyek = $this->model->getById($id);
        if (!$proyek) { header('Location: ' . BASE_URL . '/public/index.php?page=proyek'); exit; }
        $pageTitle = 'Edit Proyek';
        $pageSubtitle = htmlspecialchars($proyek['nama_proyek']);
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $id . '" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/proyek/form.php';
    }

    public function update(int $id) {
        requireManagerPermission('proyek');
        $data = [
            'nama_proyek'    => trim($_POST['nama_proyek'] ?? ''),
            'lokasi'         => trim($_POST['lokasi'] ?? ''),
            'tanggal_mulai'  => $_POST['tanggal_mulai'] ?? null,
            'tanggal_selesai'=> $_POST['tanggal_selesai'] ?? null,
            'nilai_kontrak'  => floatval(str_replace(['.', ','], ['', '.'], $_POST['nilai_kontrak'] ?? 0)),
            'status'         => $_POST['status'] ?? 'aktif',
            'deskripsi'      => trim($_POST['deskripsi'] ?? ''),
        ];
        $this->model->update($id, $data);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Proyek berhasil diperbarui.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $id);
        exit;
    }

    public function delete(int $id) {
        requireManagerPermission('proyek');
        $this->model->delete($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Proyek berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=proyek');
        exit;
    }

   

}
