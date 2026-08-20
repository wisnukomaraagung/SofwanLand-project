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
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $barangList  = $this->model->getAll($idProyek);
        $masukList   = $this->model->getMasuk($idProyek);
        $keluarList  = $this->model->getKeluar($idProyek);
        $summary     = $this->model->getDashboardSummary($idProyek);
        $pageTitle   = 'Manajemen Barang';
        $pageSubtitle = 'Pencatatan barang masuk & keluar - terintegrasi laporan keuangan otomatis';
        $pageAction  = roleCanManage('barang') ? '<a href="' . BASE_URL . '/public/index.php?page=barang&action=create" class="btn btn-primary">+ Tambah Barang</a>' : '';
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
        $idProyek = $_SESSION['selected_project_id'] ?? null;
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
        $this->model->create($data, $idProyek);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Barang berhasil ditambahkan.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    public function edit(int $id) {
        requireManagerPermission('barang');
        $barang = $this->model->getById($id);
        if (!$barang) { header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit; }
        $pageTitle = 'Edit Barang';
        $pageSubtitle = htmlspecialchars($barang['nama_barang']);
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=barang" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/barang/form.php';
    }

    public function update(int $id) {
        requireManagerPermission('barang');
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
        requireManagerPermission('barang');
        try {
            $this->model->delete($id);
            $_SESSION['flash'] = ['type'=>'success','message'=>'Barang berhasil dihapus.'];
        } catch (Exception $e) {
            $_SESSION['flash'] = ['type'=>'error','message'=>$e->getMessage()];
        }
        header('Location: ' . BASE_URL . '/public/index.php?page=barang'); exit;
    }

    // Barang Masuk
    public function storeMasuk() {
        requireManagerPermission('barang');
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        
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
            ], $idProyek);
            
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
            'tanggal'      => !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d'),
            'harga_satuan' => floatval($_POST['harga_satuan'] ?? 0),
            'supplier'     => trim($_POST['supplier'] ?? ''),
            'no_kuitansi'  => trim($_POST['no_kuitansi'] ?? ''),
            'foto_kuitansi'=> $foto_kuitansi,
            'keterangan'   => trim($_POST['keterangan'] ?? ''),
        ];
        
        if ($this->model->storeMasuk($data)) {
            $_SESSION['flash'] = ['type'=>'success','message'=>'Barang masuk berhasil dicatat.'];
        } else {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Gagal mencatat barang masuk. Periksa input data.'];
        }
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }

    // Barang Keluar
    public function storeKeluar() {
        requireManagerPermission('barang');
        $idProyek = $_SESSION['selected_project_id'] ?? 0;
        
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
            'id_proyek'  => $idProyek,
            'jumlah'     => intval($_POST['jumlah'] ?? 0),
            'tanggal'    => !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d'),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'foto_bukti' => $foto_bukti,
        ];
        if ($this->model->storeKeluar($data)) {
            $_SESSION['flash'] = ['type'=>'success','message'=>'Barang keluar berhasil dicatat.'];
        } else {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Gagal mencatat barang keluar. Periksa input data.'];
        }
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=keluar'); exit;
    }

    // Export Excel
    public function exportMasukExcel() {
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $masukList = $this->model->getMasuk($idProyek);
        $proyekNama = $_SESSION['selected_project_name'] ?? 'Semua Proyek';

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Barang_Masuk_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Warna biru untuk Barang Masuk
        $headerBg  = '#1565C0'; // biru tua (judul)
        $headerBg2 = '#1976D2'; // biru medium (kolom)
        $stripeBg  = '#E3F2FD'; // biru muda (baris ganjil)

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        // Baris judul utama
        echo "<tr>";
        echo "<td colspan='11' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN BARANG MASUK</td>";
        echo "</tr>";

        // Baris info proyek & tanggal
        echo "<tr>";
        echo "<td colspan='6' style='background:#E3F2FD; color:#0D47A1; font-size:14px; padding:6px 10px; border:1px solid #BBDEFB;'>Proyek: <b>" . htmlspecialchars($proyekNama) . "</b></td>";
        echo "<td colspan='5' style='background:#E3F2FD; color:#0D47A1; font-size:14px; padding:6px 10px; text-align:right; border:1px solid #BBDEFB;'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";

        // Baris kosong pemisah
        echo "<tr><td colspan='11' style='border:none; padding:4px;'></td></tr>";

        // Header kolom
        $cols = ['No', 'Tanggal', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Satuan (Rp)', 'Total (Rp)', 'Supplier', 'No Kuitansi', 'Keterangan', 'Foto Kuitansi'];
        echo "<tr>";
        foreach ($cols as $col) {
            echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:center; border:1px solid #0D47A1; font-weight:bold;'>" . $col . "</th>";
        }
        echo "</tr>";

        $no = 1;
        foreach ($masukList as $m) {
            $harga = $m['harga_satuan'] ?? 0;
            $total = $harga * $m['jumlah'];
            $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
            echo "<tr style='background:{$rowBg};'>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #BBDEFB;'>" . $no++ . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #BBDEFB;'>" . date('d/m/Y', strtotime($m['tanggal'])) . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #BBDEFB;'>" . htmlspecialchars($m['nama_barang']) . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #BBDEFB;'>" . number_format($m['jumlah'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #BBDEFB;'>" . htmlspecialchars($m['satuan']) . "</td>";
            echo "<td style='text-align:right; padding:5px 8px; border:1px solid #BBDEFB;'>" . number_format($harga, 0, ',', '.') . "</td>";
            echo "<td style='text-align:right; padding:5px 8px; border:1px solid #BBDEFB; font-weight:bold;'>" . number_format($total, 0, ',', '.') . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #BBDEFB;'>" . htmlspecialchars($m['supplier'] ?? '-') . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #BBDEFB;'>" . htmlspecialchars($m['no_kuitansi'] ?? '-') . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #BBDEFB;'>" . htmlspecialchars($m['keterangan'] ?? '-') . "</td>";
            if (!empty($m['foto_kuitansi'])) {
                $url = rtrim(BASE_URL, '/') . '/public/' . $m['foto_kuitansi'];
                echo "<td style='text-align:center; padding:5px 8px; border:1px solid #BBDEFB;'><a href='" . $url . "' style='color:#1565C0;'>Lihat Foto</a></td>";
            } else {
                echo "<td style='text-align:center; padding:5px 8px; border:1px solid #BBDEFB; color:#999;'>-</td>";
            }
            echo "</tr>";
        }

        // Baris total
        $grandTotal = array_sum(array_map(fn($m) => ($m['harga_satuan'] ?? 0) * $m['jumlah'], $masukList));
        echo "<tr>";
        echo "<td colspan='6' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #0D47A1;'>TOTAL KESELURUHAN</td>";
        echo "<td style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #0D47A1;'>" . number_format($grandTotal, 0, ',', '.') . "</td>";
        echo "<td colspan='4' style='background:{$headerBg2}; border:1px solid #0D47A1;'></td>";
        echo "</tr>";

        echo "</table>";
        exit;
    }

    public function exportKeluarExcel() {
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $keluarList = $this->model->getKeluar($idProyek);
        $proyekNama = $_SESSION['selected_project_name'] ?? 'Semua Proyek';

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Barang_Keluar_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Warna hijau untuk Barang Keluar
        $headerBg  = '#2E7D32'; // hijau tua (judul)
        $headerBg2 = '#388E3C'; // hijau medium (kolom)
        $stripeBg  = '#E8F5E9'; // hijau muda (baris ganjil)

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        // Baris judul utama
        echo "<tr>";
        echo "<td colspan='8' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN BARANG KELUAR</td>";
        echo "</tr>";

        // Baris info proyek & tanggal
        echo "<tr>";
        echo "<td colspan='4' style='background:#E8F5E9; color:#1B5E20; font-size:14px; padding:6px 10px; border:1px solid #C8E6C9;'>Proyek: <b>" . htmlspecialchars($proyekNama) . "</b></td>";
        echo "<td colspan='4' style='background:#E8F5E9; color:#1B5E20; font-size:14px; padding:6px 10px; text-align:right; border:1px solid #C8E6C9;'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";

        // Baris kosong pemisah
        echo "<tr><td colspan='8' style='border:none; padding:4px;'></td></tr>";

        // Header kolom
        $cols = ['No', 'Tanggal', 'Nama Barang', 'Proyek', 'Jumlah', 'Satuan', 'Keterangan', 'Foto Bukti'];
        echo "<tr>";
        foreach ($cols as $col) {
            echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:center; border:1px solid #1B5E20; font-weight:bold;'>" . $col . "</th>";
        }
        echo "</tr>";

        $no = 1;
        foreach ($keluarList as $k) {
            $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
            echo "<tr style='background:{$rowBg};'>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9;'>" . $no++ . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . date('d/m/Y', strtotime($k['tanggal'])) . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($k['nama_barang']) . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($k['nama_proyek']) . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9;'>" . number_format($k['jumlah'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($k['satuan']) . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($k['keterangan'] ?? '-') . "</td>";
            if (!empty($k['foto_bukti'])) {
                $url = rtrim(BASE_URL, '/') . '/public/' . $k['foto_bukti'];
                echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9;'><a href='" . $url . "' style='color:#2E7D32;'>Lihat Foto</a></td>";
            } else {
                echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9; color:#999;'>-</td>";
            }
            echo "</tr>";
        }

        // Baris total
        $totalItem = count($keluarList);
        echo "<tr>";
        echo "<td colspan='4' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #1B5E20;'>TOTAL TRANSAKSI</td>";
        echo "<td style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:center; padding:6px 10px; border:1px solid #1B5E20;'>{$totalItem} transaksi</td>";
        echo "<td colspan='3' style='background:{$headerBg2}; border:1px solid #1B5E20;'></td>";
        echo "</tr>";

        echo "</table>";
        exit;
    }

    public function exportStokExcel() {
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $barangList = $this->model->getAll($idProyek);
        $proyekNama = $_SESSION['selected_project_name'] ?? 'Semua Proyek';

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Master_Barang_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Warna orange untuk Master Barang
        $headerBg  = '#E65100'; // orange tua (judul)
        $headerBg2 = '#F57C00'; // orange medium (kolom)
        $stripeBg  = '#FFF3E0'; // orange muda (baris ganjil)

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        // Baris judul utama
        echo "<tr>";
        echo "<td colspan='7' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN MASTER BARANG (STOK)</td>";
        echo "</tr>";

        // Baris info proyek & tanggal
        echo "<tr>";
        echo "<td colspan='4' style='background:#FFF3E0; color:#BF360C; font-size:14px; padding:6px 10px; border:1px solid #FFCCBC;'>Proyek: <b>" . htmlspecialchars($proyekNama) . "</b></td>";
        echo "<td colspan='3' style='background:#FFF3E0; color:#BF360C; font-size:14px; padding:6px 10px; text-align:right; border:1px solid #FFCCBC;'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";

        // Baris kosong pemisah
        echo "<tr><td colspan='7' style='border:none; padding:4px;'></td></tr>";

        // Header kolom
        $cols = ['No', 'Nama Barang', 'Satuan', 'Harga Satuan (Rp)', 'Total Masuk', 'Total Keluar', 'Stok Akhir'];
        echo "<tr>";
        foreach ($cols as $col) {
            echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:center; border:1px solid #BF360C; font-weight:bold;'>" . $col . "</th>";
        }
        echo "</tr>";

        $no = 1;
        foreach ($barangList as $b) {
            $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
            $stokStyle = ($b['stok'] <= 10) ? 'color:#c0392b; font-weight:bold;' : '';
            echo "<tr style='background:{$rowBg};'>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #FFCCBC;'>" . $no++ . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid #FFCCBC; font-weight:bold;'>" . htmlspecialchars($b['nama_barang']) . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #FFCCBC;'>" . htmlspecialchars($b['satuan']) . "</td>";
            echo "<td style='text-align:right; padding:5px 8px; border:1px solid #FFCCBC;'>" . number_format($b['harga_satuan'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #FFCCBC;'>" . number_format($b['total_masuk'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #FFCCBC;'>" . number_format($b['total_keluar'], 0, ',', '.') . "</td>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid #FFCCBC; {$stokStyle}'>" . number_format($b['stok'], 0, ',', '.') . "</td>";
            echo "</tr>";
        }

        // Baris total
        $totalJenis = count($barangList);
        echo "<tr>";
        echo "<td colspan='6' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #BF360C;'>TOTAL JENIS BARANG</td>";
        echo "<td style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:center; padding:6px 10px; border:1px solid #BF360C;'>{$totalJenis} jenis</td>";
        echo "</tr>";

        echo "</table>";
        exit;
    }

    public function editMasuk(int $id) {
        requireManagerPermission('barang');
        $masuk = $this->model->getMasukById($id);
        if (!$masuk) { header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit; }
        
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $barangList = $this->model->getAllForSelect($idProyek);
        $pageTitle = 'Edit Barang Masuk';
        $pageSubtitle = 'Edit catatan riwayat masuk';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=barang&tab=masuk" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/barang/edit_masuk.php';
    }

    public function updateMasuk(int $id) {
        requireManagerPermission('barang');
        $oldMasuk = $this->model->getMasukById($id);
        $foto_kuitansi = $oldMasuk['foto_kuitansi'] ?? null;

        if (isset($_FILES['foto_kuitansi']) && $_FILES['foto_kuitansi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kuitansi/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($_FILES['foto_kuitansi']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['foto_kuitansi']['tmp_name'], $targetPath)) {
                // Delete old file if exists
                if ($foto_kuitansi && file_exists(BASE_PATH . '/public/' . $foto_kuitansi)) {
                    @unlink(BASE_PATH . '/public/' . $foto_kuitansi);
                }
                $foto_kuitansi = 'uploads/kuitansi/' . $fileName;
            }
        }

        $data = [
            'id_barang'     => intval($_POST['id_barang'] ?? 0),
            'jumlah'        => intval($_POST['jumlah'] ?? 0),
            'tanggal'       => $_POST['tanggal'] ?? date('Y-m-d'),
            'harga_satuan'  => floatval($_POST['harga_satuan'] ?? 0),
            'supplier'      => trim($_POST['supplier'] ?? ''),
            'no_kuitansi'   => trim($_POST['no_kuitansi'] ?? ''),
            'foto_kuitansi' => $foto_kuitansi,
            'keterangan'    => trim($_POST['keterangan'] ?? ''),
        ];
        
        $this->model->updateMasuk($id, $data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang masuk berhasil diperbarui.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }

    public function deleteMasuk(int $id) {
        requireManagerPermission('barang');
        $this->model->deleteMasuk($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang masuk berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=masuk'); exit;
    }

    public function editKeluar(int $id) {
        requireManagerPermission('barang');
        $keluar = $this->model->getKeluarById($id);
        if (!$keluar) { header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=keluar'); exit; }

        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $barangList = $this->model->getAllForSelect($idProyek);
        $proyekList = $this->proyekModel->getAll();
        $pageTitle = 'Edit Barang Keluar';
        $pageSubtitle = 'Edit catatan riwayat keluar';
        $pageAction = '<a href="' . BASE_URL . '/public/index.php?page=barang&tab=keluar" class="btn btn-secondary">← Kembali</a>';
        require BASE_PATH . '/app/views/barang/edit_keluar.php';
    }

    public function updateKeluar(int $id) {
        requireManagerPermission('barang');
        $oldKeluar = $this->model->getKeluarById($id);
        $foto_bukti = $oldKeluar['foto_bukti'] ?? null;

        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kuitansi/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_keluar_' . basename($_FILES['foto_bukti']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], $targetPath)) {
                if ($foto_bukti && file_exists(BASE_PATH . '/public/' . $foto_bukti)) {
                    @unlink(BASE_PATH . '/public/' . $foto_bukti);
                }
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

        $this->model->updateKeluar($id, $data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang keluar berhasil diperbarui.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=keluar'); exit;
    }

    public function deleteKeluar(int $id) {
        requireManagerPermission('barang');
        $this->model->deleteKeluar($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Riwayat barang keluar berhasil dihapus.'];
        header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=keluar'); exit;
    }

    public function viewBukti() {
        $tab = $_GET['tab'] ?? 'masuk';
        if (!in_array($tab, ['masuk', 'keluar', 'stok'], true)) {
            $tab = 'masuk';
        }

        $relativePath = $this->resolveKuitansiPath($_GET['file'] ?? '');
        if (!$relativePath) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'File bukti tidak valid.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=' . urlencode($tab));
            exit;
        }

        $fullPath = BASE_PATH . '/public/' . $relativePath;
        if (!is_file($fullPath)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'File bukti tidak ditemukan.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=barang&tab=' . urlencode($tab));
            exit;
        }

        $backUrl = BASE_URL . '/public/index.php?page=barang&tab=' . urlencode($tab);
        if (!empty($_GET['return'])) {
            $return = $_GET['return'];
            if (strpos($return, BASE_URL) === 0) {
                $backUrl = $return;
            }
        }
        $pageTitle = $tab === 'keluar' ? 'Bukti Barang Keluar' : 'Bukti Kuitansi';
        $pageSubtitle = basename($relativePath);
        $pageAction = '<a href="' . htmlspecialchars($backUrl) . '" class="btn btn-secondary">← Kembali</a>';
        $imageUrl = BASE_URL . '/public/' . $relativePath;

        require BASE_PATH . '/app/views/barang/view_bukti.php';
    }

    public static function buktiViewUrl(string $relativePath, string $tab = 'masuk', ?string $returnUrl = null): string {
        $url = BASE_URL . '/public/index.php?page=barang&action=viewBukti&tab='
            . rawurlencode($tab) . '&file=' . rawurlencode($relativePath);
        if ($returnUrl !== null && $returnUrl !== '') {
            $url .= '&return=' . rawurlencode($returnUrl);
        }
        return $url;
    }

    private function resolveKuitansiPath(string $file): ?string {
        $file = str_replace('\\', '/', trim($file));
        if ($file === '' || strpos($file, '..') !== false) {
            return null;
        }
        if (!preg_match('#^uploads/kuitansi/[^/\\\\]+\\.(png|jpe?g|gif|webp)$#i', $file)) {
            return null;
        }
        return $file;
    }
}
