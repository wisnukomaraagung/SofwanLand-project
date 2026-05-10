<?php
// app/controllers/BarangController.php

class BarangController {
    private $model;
    private $proyekModel;

    public function __construct() {
        $this->model      = new BarangModel();
        $this->proyekModel = new ProyekModel();
        session_start();
    }

    public function index() {
        $barangList  = $this->model->getAll();
        $masukList   = $this->model->getMasuk();
        $keluarList  = $this->model->getKeluar();
        $pageTitle   = 'Barang';
        $pageSubtitle = 'Manajemen stok dan transaksi barang';
        $pageAction  = '<a href="' . BASE_URL . '/public/index.php?page=barang&action=create" class="btn btn-primary">+ Tambah Barang</a>';
        require BASE_PATH . '/app/views/barang/index.php';
    }

    public function create() {
        $pageTitle    = 'Tambah Barang';
        $pageSubtitle = 'Daftarkan barang baru';
        $pageAction   = '<a href="' . BASE_URL . '/public/index.php?page=barang" class="btn btn-secondary">← Kembali</a>';
        $barang = null;
        require BASE_PATH . '/app/views/barang/form.php';
    }

    public function store() {
        $data = [
            'nama_barang'  => trim($_POST['nama_barang'] ?? ''),
            'satuan'       => trim($_POST['satuan'] ?? ''),
            'stok'         => intval($_POST['stok'] ?? 0),
            'harga_satuan' => floatval($_POST['harga_satuan'] ?? 0),
        ];
        if (empty($data['nama_barang'])) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Nama barang wajib diisi.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=barang&action=create'); exit;
        }
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang berhasil ditambahkan.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    public function edit(int $id) {
        $barang = $this->model->getById($id);
        if (!$barang) { header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit; }
        $pageTitle = 'Edit Barang';
        $pageSubtitle = htmlspecialchars($barang['nama_barang']);
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=barang" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/barang/form.php';
    }

    public function update(int $id) {
        $data = [
            'nama_barang'  => trim($_POST['nama_barang'] ?? ''),
            'satuan'       => trim($_POST['satuan'] ?? ''),
            'harga_satuan' => floatval($_POST['harga_satuan'] ?? 0),
        ];
        $this->model->update($id, $data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang berhasil diperbarui.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    public function delete(int $id) {
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    // Barang Masuk
    public function storeMasuk() {
        $data = [
            'id_barang'  => intval($_POST['id_barang'] ?? 0),
            'jumlah'     => intval($_POST['jumlah'] ?? 0),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
        ];
        $this->model->storeMasuk($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang masuk berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    // Barang Keluar
    public function storeKeluar() {
        $data = [
            'id_barang'  => intval($_POST['id_barang'] ?? 0),
            'id_proyek'  => intval($_POST['id_proyek'] ?? 0),
            'jumlah'     => intval($_POST['jumlah'] ?? 0),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
        ];
        $this->model->storeKeluar($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang keluar berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }
}
