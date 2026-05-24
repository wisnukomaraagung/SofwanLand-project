<?php
// app/controllers/BarangController.php

class BarangController {
    private $model;
    private $proyekModel;

    public function __construct() {
        $this->model      = new BarangModel();
        $this->proyekModel = new ProyekModel();
    }

    public function index() {
        $barangList  = $this->model->getAll();
        $masukList   = $this->model->getMasuk();
        $keluarList  = $this->model->getKeluar();
        $summary     = $this->model->getDashboardSummary();
        $pageTitle   = 'Manajemen Barang';
        $pageSubtitle = 'Pencatatan barang masuk & keluar - terintegrasi laporan keuangan otomatis';
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
        // Handle new item creation dynamically
        $id_barang = intval($_POST['id_barang'] ?? 0);
        if ($id_barang === 0 && !empty($_POST['nama_barang_baru'])) {
            $nama_barang = trim($_POST['nama_barang_baru']);
            $satuan = trim($_POST['satuan'] ?? '-');
            $harga_satuan = floatval($_POST['harga_satuan'] ?? 0);
            
            $this->model->create([
                'nama_barang' => $nama_barang,
                'satuan' => $satuan,
                'stok' => 0,
                'harga_satuan' => $harga_satuan
            ]);
            
            // Get the newly created ID
            $db = getDB();
            $id_barang = $db->lastInsertId();
        }

        if ($id_barang === 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Barang belum dipilih atau diinput.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
        }

        // Handle file upload (foto kuitansi)
        $foto_kuitansi = null;
        if (isset($_FILES['foto_kuitansi']) && $_FILES['foto_kuitansi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kuitansi/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['foto_kuitansi']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['foto_kuitansi']['tmp_name'], $targetPath)) {
                $foto_kuitansi = 'uploads/kuitansi/' . $fileName;
            }
        }

        $data = [
            'id_barang'    => $id_barang,
            'jumlah'       => intval($_POST['jumlah'] ?? 0),
            'tanggal'      => $_POST['tanggal'] ?? date('Y-m-d'),
            'harga_satuan' => floatval($_POST['harga_satuan'] ?? 0),
            'supplier'     => trim($_POST['supplier'] ?? ''),
            'no_kuitansi'  => trim($_POST['no_kuitansi'] ?? ''),
            'foto_kuitansi'=> $foto_kuitansi,
            'keterangan'   => trim($_POST['keterangan'] ?? ''),
        ];
        
        $this->model->storeMasuk($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang masuk berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }

    // Barang Keluar
    public function storeKeluar() {
        // Handle file upload (foto bukti)
        $foto_bukti = null;
        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kuitansi/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_keluar_' . basename($_FILES['foto_bukti']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $targetPath)) {
                $foto_bukti = 'uploads/kuitansi/' . $fileName;
            }
        }

        $data = [
            'id_barang'  => intval($_POST['id_barang'] ?? 0),
            'id_proyek'  => intval($_POST['id_proyek'] ?? 0),
            'jumlah'     => intval($_POST['jumlah'] ?? 0),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'foto_bukti' => $foto_bukti,
        ];
        $this->model->storeKeluar($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang keluar berhasil dicatat.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=keluar'); exit;
    }

    // Export Excel
    public function exportMasukExcel() {
        $masukList = $this->model->getMasuk();
        
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Barang_Masuk_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th>Tanggal</th>";
        echo "<th>Barang</th>";
        echo "<th>Jumlah</th>";
        echo "<th>Satuan</th>";
        echo "<th>Harga Satuan</th>";
        echo "<th>Total</th>";
        echo "<th>Supplier</th>";
        echo "<th>No Kuitansi</th>";
        echo "<th>Keterangan</th>";
        echo "</tr>";
        
        $no = 1;
        foreach ($masukList as $m) {
            $harga = $m['harga_satuan'] ?? 0;
            $total = $harga * $m['jumlah'];
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . $m['tanggal'] . "</td>";
            echo "<td>" . htmlspecialchars($m['nama_barang']) . "</td>";
            echo "<td>" . $m['jumlah'] . "</td>";
            echo "<td>" . htmlspecialchars($m['satuan']) . "</td>";
            echo "<td>" . $harga . "</td>";
            echo "<td>" . $total . "</td>";
            echo "<td>" . htmlspecialchars($m['supplier'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($m['no_kuitansi'] ?? '-') . "</td>";
            echo "<td>" . htmlspecialchars($m['keterangan'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }

    public function editMasuk(int $id) {
        $masuk = $this->model->getMasukById($id);
        if (!$masuk) { header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit; }
        
        $barangList = $this->model->getAllForSelect();
        $pageTitle = 'Edit Barang Masuk';
        $pageSubtitle = 'Edit catatan riwayat masuk';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=barang&tab=masuk" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/barang/edit_masuk.php';
    }

    public function updateMasuk(int $id) {
        $data = [
            'id_barang'    => intval($_POST['id_barang'] ?? 0),
            'jumlah'       => intval($_POST['jumlah'] ?? 0),
            'tanggal'      => $_POST['tanggal'] ?? date('Y-m-d'),
            'harga_satuan' => floatval($_POST['harga_satuan'] ?? 0),
            'supplier'     => trim($_POST['supplier'] ?? ''),
            'no_kuitansi'  => trim($_POST['no_kuitansi'] ?? ''),
            'keterangan'   => trim($_POST['keterangan'] ?? ''),
        ];
        
        $this->model->updateMasuk($id, $data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang masuk berhasil diperbarui.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }

    public function deleteMasuk(int $id) {
        $this->model->deleteMasuk($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang masuk berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }
}
