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

        $progressHistory = $this->model->getProgressHistory($id);
        $keuanganHistory = $this->model->getKeuanganHistory($id);
        $barangKeluar    = $this->model->getBarangKeluar($id);
        $dokumentasiList = $this->model->getDokumentasi($id);
        
        $dokumentasiBaru = count($dokumentasiList);


        $pengeluaranList = array_map(function($item) {
            return [
                'tanggal' => $item['tanggal'],
                'kategori' => ucfirst($item['tipe']),
                'keterangan' => $item['keterangan'],
                'nominal' => (int)$item['jumlah']
            ];
        }, $keuanganHistory);

        $rincianPekerjaan = [
            [
                'nama' => 'Pondasi',
                'progress' => 100,
                'status' => 'selesai',
                'estimasi_hari' => 7,
                'deskripsi' => 'Pekerjaan pondasi utama'
            ],
            [
                'nama' => 'Struktur Beton',
                'progress' => 65,
                'status' => 'proses',
                'estimasi_hari' => 14,
                'deskripsi' => 'Pengerjaan struktur lantai'
            ],
            [
                'nama' => 'Finishing',
                'progress' => 10,
                'status' => 'belum-mulai',
                'estimasi_hari' => 10,
                'deskripsi' => 'Tahap finishing akhir'
            ]
        ];

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
