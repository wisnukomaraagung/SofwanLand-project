<?php
// app/controllers/KeuanganController.php

class KeuanganController {
    private $model;

    public function __construct() {
        $this->model = new KeuanganModel();
    }

    public function index() {
        $idProyek = $_SESSION['selected_project_id'] ?? null;

        $masukList        = $this->model->getMasuk($idProyek);
        $keluarList       = $this->model->getKeluar($idProyek);
        $barangMasukList  = $this->model->getBarangMasukUntukLaporan($idProyek);
        $barangKeluarList = $this->model->getBarangKeluarUntukLaporan($idProyek);
        $summary          = $this->model->getSummaryGabungan($idProyek);
        $proyekList       = $this->model->getProyek();
        $pageTitle        = 'Keuangan';
        $pageSubtitle     = 'Manajemen pemasukan, pengeluaran, dan laporan keuangan proyek';
        require BASE_PATH . '/app/views/keuangan/index.php';
    }

    // ─── HELPER ──────────────────────────────────────────────────────
    private function keuanganUrl(string $tab = ''): string {
        $url = BASE_URL . '/public/index.php?page=keuangan';
        if ($tab !== '') $url .= '&tab=' . $tab;
        return $url;
    }

    // ─── STORE (lama — dipertahankan) ────────────────────────────────
    public function store() {
        requireManagerPermission('keuangan');
        $idProyek = $_SESSION['selected_project_id'] ?? intval($_POST['id_proyek'] ?? 0);
        if ($idProyek <= 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Harap pilih proyek terlebih dahulu.'];
            header('Location: ' . $this->keuanganUrl()); exit;
        }
        $data = [
            'id_proyek'  => $idProyek,
            'tipe'       => $_POST['tipe'] ?? 'pengeluaran',
            'kategori'   => trim($_POST['kategori'] ?? ''),
            'jumlah'     => floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0)),
            'sumber'     => trim($_POST['sumber'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dicatat.'];
        header('Location: ' . $this->keuanganUrl()); exit;
    }

    // ─── STORE MASUK ─────────────────────────────────────────────────
    public function storeMasuk() {
        requireManagerPermission('keuangan');
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        if (!$idProyek) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Harap pilih proyek terlebih dahulu.'];
            header('Location: ' . $this->keuanganUrl('masuk')); exit;
        }
        $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
        if ($jumlah <= 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pemasukan wajib diisi dan harus lebih dari 0'];
            header('Location: ' . $this->keuanganUrl('masuk')); exit;
        }
        $data = [
            'id_proyek'  => $idProyek,
            'tipe'       => 'pemasukan',
            'jumlah'     => $jumlah,
            'sumber'     => trim($_POST['sumber'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Pemasukan berhasil dicatat.'];
        header('Location: ' . $this->keuanganUrl('masuk')); exit;
    }

    // ─── STORE KELUAR ────────────────────────────────────────────────
    public function storeKeluar() {
        requireManagerPermission('keuangan');
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        if (!$idProyek) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Harap pilih proyek terlebih dahulu.'];
            header('Location: ' . $this->keuanganUrl('keluar')); exit;
        }
        $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
        if ($jumlah <= 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pengeluaran wajib diisi dan harus lebih dari 0'];
            header('Location: ' . $this->keuanganUrl('keluar')); exit;
        }
        $data = [
            'id_proyek'  => $idProyek,
            'tipe'       => 'pengeluaran',
            'kategori'   => trim($_POST['kategori'] ?? ''),
            'jumlah'     => $jumlah,
            'sumber'     => trim($_POST['sumber'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => $_POST['tanggal'] ?? date('Y-m-d'),
        ];
        $this->model->create($data);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Pengeluaran berhasil dicatat.'];
        header('Location: ' . $this->keuanganUrl('keluar')); exit;
    }

    // ─── DELETE (lama — dipertahankan) ───────────────────────────────
    public function delete(int $id) {
        requireManagerPermission('keuangan');
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi berhasil dihapus.'];
        header('Location: ' . $this->keuanganUrl()); exit;
    }

    // ─── DELETE MASUK ────────────────────────────────────────────────
    public function deleteMasuk(int $id) {
        requireManagerPermission('keuangan');
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi pemasukan berhasil dihapus.'];
        header('Location: ' . $this->keuanganUrl('masuk')); exit;
    }

    // ─── DELETE KELUAR ───────────────────────────────────────────────
    public function deleteKeluar(int $id) {
        requireManagerPermission('keuangan');
        $this->model->delete($id);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Transaksi pengeluaran berhasil dihapus.'];
        header('Location: ' . $this->keuanganUrl('keluar')); exit;
    }

    // ─── EDIT MASUK ──────────────────────────────────────────────────
    public function editMasuk(int $id) {
        requireManagerPermission('keuangan');
        $data = $this->model->getById($id);
        if (!$data || $data['tipe'] !== 'pemasukan') {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Data pemasukan tidak ditemukan.'];
            header('Location: ' . $this->keuanganUrl('masuk')); exit;
        }
        $pageTitle = 'Edit Keuangan Masuk';
        $pageSubtitle = 'Edit data pemasukan';
        require BASE_PATH . '/app/views/keuangan/edit_masuk.php';
    }

    // ─── UPDATE MASUK ────────────────────────────────────────────────
    public function updateMasuk(int $id) {
        requireManagerPermission('keuangan');
        $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
        if ($jumlah <= 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pemasukan wajib diisi dan harus lebih dari 0'];
            header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&action=editMasuk&id=' . $id); exit;
        }
        $data = [
            'jumlah'     => $jumlah,
            'sumber'     => trim($_POST['sumber'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d'),
        ];
        if ($this->model->update($id, $data)) {
            $_SESSION['flash'] = ['type'=>'success','message'=>'Data pemasukan berhasil diperbarui.'];
        } else {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Gagal memperbarui data pemasukan.'];
        }
        header('Location: ' . $this->keuanganUrl('masuk')); exit;
    }

    // ─── EDIT KELUAR ─────────────────────────────────────────────────
    public function editKeluar(int $id) {
        requireManagerPermission('keuangan');
        $data = $this->model->getById($id);
        if (!$data || $data['tipe'] !== 'pengeluaran') {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Data pengeluaran tidak ditemukan.'];
            header('Location: ' . $this->keuanganUrl('keluar')); exit;
        }
        $pageTitle = 'Edit Keuangan Keluar';
        $pageSubtitle = 'Edit data pengeluaran';
        require BASE_PATH . '/app/views/keuangan/edit_keluar.php';
    }

    // ─── UPDATE KELUAR ───────────────────────────────────────────────
    public function updateKeluar(int $id) {
        requireManagerPermission('keuangan');
        $jumlah = floatval(str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? 0));
        if ($jumlah <= 0) {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Jumlah pengeluaran wajib diisi dan harus lebih dari 0'];
            header('Location: ' . BASE_URL . '/public/index.php?page=keuangan&action=editKeluar&id=' . $id); exit;
        }
        $data = [
            'jumlah'     => $jumlah,
            'kategori'   => trim($_POST['kategori'] ?? ''),
            'sumber'     => trim($_POST['sumber'] ?? ''),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal'    => !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d'),
        ];
        if ($this->model->update($id, $data)) {
            $_SESSION['flash'] = ['type'=>'success','message'=>'Data pengeluaran berhasil diperbarui.'];
        } else {
            $_SESSION['flash'] = ['type'=>'error','message'=>'Gagal memperbarui data pengeluaran.'];
        }
        header('Location: ' . $this->keuanganUrl('keluar')); exit;
    }

    // ─── EXPORT MASUK EXCEL ──────────────────────────────────────────
    public function exportMasukExcel() {
        $idProyek = $_SESSION['selected_project_id'] ?? null;
        $list = $this->model->getMasuk($idProyek);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Keuangan_Masuk_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'><tr><th>No</th><th>Tanggal</th><th>Jumlah</th><th>Sumber</th><th>Keterangan</th></tr>";
        if (empty($list)) {
            echo "<tr><td colspan='5'>Tidak ada data</td></tr>";
        } else {
            $no = 1;
            foreach ($list as $r) {
                echo "<tr><td>{$no}</td><td>" . htmlspecialchars($r['tanggal']) . "</td><td>" . number_format($r['jumlah'],0,',','.') . "</td><td>" . htmlspecialchars($r['sumber'] ?? '-') . "</td><td>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td></tr>";
                $no++;
            }
        }
        echo "</table>"; exit;
    }

    // ─── EXPORT KELUAR EXCEL ─────────────────────────────────────────
    public function exportKeluarExcel() {
        $idProyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        $list = $this->model->getKeluar($idProyek);

        // Nama proyek untuk filename & Progress
        $namaProyek = 'Semua';
        $progressTerbaru = 0;
        
        if ($idProyek) {
            require_once BASE_PATH . '/app/models/ProyekModel.php';
            $proyekModel = new ProyekModel();
            $detailProyek = $proyekModel->getDetail($idProyek);
            if ($detailProyek) {
                $namaProyek = $detailProyek['nama_proyek'];
                $progressTerbaru = $detailProyek['progress_terbaru'] ?? 0;
            }
        }

        $safeNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaProyek);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Keuangan_Keluar_{$safeNama}_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
        $groupedKeluar = [];
        foreach ($categories as $c) {
            $groupedKeluar[$c] = [];
        }
        foreach ($list as $lk) {
            $cat = $lk['kategori'] ?: 'Lainnya';
            if (!in_array($cat, $categories)) {
                $groupedKeluar['Lainnya'][] = $lk;
            } else {
                $groupedKeluar[$cat][] = $lk;
            }
        }

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo "<table border='1'>";
        echo "<colgroup><col width='40'><col width='100'><col width='150'><col width='200'><col width='250'></colgroup>";
        echo "<tr><th colspan='5' style='text-align:center; background:#f2f2f2;'><strong>RIWAYAT PENGELUARAN PER KATEGORI</strong></th></tr>";
        echo "<tr><th colspan='5' style='text-align:center;'><strong>Proyek: " . htmlspecialchars($namaProyek) . " (Progress: " . $progressTerbaru . "%)</strong></th></tr>";
        echo "<tr><td colspan='5'></td></tr>";

        foreach ($groupedKeluar as $categoryName => $items) {
            echo "<tr><td colspan='5' style='background:#f1f2f6;'><strong>📁 " . strtoupper($categoryName) . " (Total: Rp " . number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') . ")</strong></td></tr>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Jumlah</th><th>Kepada / Tujuan</th><th>Keterangan</th></tr>";

            if (empty($items)) {
                echo "<tr><td colspan='5' style='text-align:center; color:#7f8c8d;'>Tidak ada transaksi untuk kategori ini</td></tr>";
            } else {
                $no = 1;
                foreach ($items as $r) {
                    $formattedDate = date('d/m/Y', strtotime($r['tanggal']));
                    echo "<tr><td>{$no}</td><td style='mso-number-format:\"\@\";'>" . htmlspecialchars($formattedDate) . "</td><td style='text-align:right;'>Rp " . number_format($r['jumlah'],0,',','.') . "</td><td>" . htmlspecialchars($r['sumber'] ?? '-') . "</td><td>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td></tr>";
                    $no++;
                }
            }
            echo "<tr><td colspan='5'></td></tr>";
        }
        echo "</table>"; exit;
    }

    // ─── EXPORT LAPORAN EXCEL (gabungan) ─────────────────────────────
    public function exportLaporanExcel() {
        $idProyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        $summary         = $this->model->getSummaryGabungan($idProyek);
        $barangMasukList = $this->model->getBarangMasukUntukLaporan($idProyek);
        $transaksiList   = array_merge(
            $this->model->getMasuk($idProyek) ?? [],
            $this->model->getKeluar($idProyek) ?? []
        );
        usort($transaksiList, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        // Nama proyek & Progress
        $namaProyek = 'Semua';
        $progressTerbaru = 0;
        
        if ($idProyek) {
            require_once BASE_PATH . '/app/models/ProyekModel.php';
            $proyekModel = new ProyekModel();
            $detailProyek = $proyekModel->getDetail($idProyek);
            if ($detailProyek) {
                $namaProyek = $detailProyek['nama_proyek'];
                $progressTerbaru = $detailProyek['progress_terbaru'] ?? 0;
            }
        }

        $safeNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaProyek);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Keuangan_{$safeNama}_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Aggregating HPP expenses
        $keluarList = $this->model->getKeluar($idProyek);
        $biayaGaji = 0;
        $biayaSewaAlat = 0;
        $biayaMaterialLangsung = 0;
        $biayaLainnya = 0;

        foreach ($keluarList as $lk) {
            $cat = $lk['kategori'] ?? '';
            if ($cat === 'Gaji') {
                $biayaGaji += (float) $lk['jumlah'];
            } elseif ($cat === 'Sewa Alat') {
                $biayaSewaAlat += (float) $lk['jumlah'];
            } elseif ($cat === 'Pembelian Material') {
                $biayaMaterialLangsung += (float) $lk['jumlah'];
            } else {
                $biayaLainnya += (float) $lk['jumlah'];
            }
        }

        $totalBiayaMaterial = ($summary['nilai_barang_masuk'] ?? 0) + $biayaMaterialLangsung;
        $totalHPP = $totalBiayaMaterial + $biayaGaji + $biayaSewaAlat + $biayaLainnya;
        $labaBruto = ($summary['total_pemasukan'] ?? 0) - $totalHPP;

        // Bagian 1: Laporan Laba Rugi
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo "<table border='1'>";
        echo "<tr><th colspan='3' style='text-align:center; background:#f2f2f2;'><strong>LAPORAN LABA RUGI PROYEK</strong></th></tr>";
        echo "<tr><th colspan='3' style='text-align:center;'><strong>Proyek: " . htmlspecialchars($namaProyek) . " (Progress: " . $progressTerbaru . "%)</strong></th></tr>";
        echo "<tr><th colspan='3' style='text-align:center;'><strong>Periode: " . date('Y') . "</strong></th></tr>";
        echo "<tr><td colspan='3'></td></tr>";

        echo "<tr><td colspan='2' style='background:#fafafa;'><strong>PENDAPATAN</strong></td><td style='text-align:right; background:#fafafa;'><strong>TOTAL (Rp)</strong></td></tr>";
        echo "<tr><td style='padding-left:15px;'>Penjualan Jasa Konstruksi (Termin Proyek)</td><td></td><td style='text-align:right; color:#27ae60;'>" . number_format($summary['total_pemasukan'],0,',','.') . "</td></tr>";
        echo "<tr><td style='background:#f1f2f6;'><strong>JUMLAH PENDAPATAN</strong></td><td style='background:#f1f2f6;'></td><td style='text-align:right; font-weight:bold; color:#27ae60; background:#f1f2f6;'>" . number_format($summary['total_pemasukan'],0,',','.') . "</td></tr>";
        echo "<tr><td colspan='3'></td></tr>";

        echo "<tr><td colspan='3' style='background:#fafafa;'><strong>HARGA POKOK PENJUALAN (HPP) / PENGELUARAN PROYEK</strong></td></tr>";
        echo "<tr><td style='padding-left:15px;'>Biaya Material Proyek (Barang Masuk + Cash)</td><td style='text-align:right;'>" . number_format($totalBiayaMaterial,0,',','.') . "</td><td></td></tr>";
        echo "<tr><td style='padding-left:15px;'>Biaya Tenaga Kerja Langsung (Gaji / Upah)</td><td style='text-align:right;'>" . number_format($biayaGaji,0,',','.') . "</td><td></td></tr>";
        echo "<tr><td style='padding-left:15px;'>Biaya Sewa Alat &amp; Kendaraan Proyek</td><td style='text-align:right;'>" . number_format($biayaSewaAlat,0,',','.') . "</td><td></td></tr>";
        echo "<tr><td style='padding-left:15px;'>Biaya Lain-lain / Administrasi</td><td style='text-align:right;'>" . number_format($biayaLainnya,0,',','.') . "</td><td></td></tr>";
        echo "<tr><td style='background:#f1f2f6;'><strong>JUMLAH HARGA POKOK PENGELUARAN</strong></td><td style='background:#f1f2f6;'></td><td style='text-align:right; font-weight:bold; color:#c0392b; background:#f1f2f6;'>" . number_format($totalHPP,0,',','.') . "</td></tr>";
        echo "<tr><td colspan='3'></td></tr>";

        echo "<tr><td style='background:#c8e6c9; color:#1b5e20;'><strong>LABA BRUTO</strong></td><td style='background:#c8e6c9;'></td><td style='text-align:right; font-weight:bold; color:#1b5e20; background:#c8e6c9;'>" . number_format($labaBruto,0,',','.') . "</td></tr>";
        echo "</table><br><br>";

        // Bagian 2: Riwayat Pengeluaran Per Kategori (Persis Gambar)
        $categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
        $groupedKeluar = [];
        foreach ($categories as $c) {
            $groupedKeluar[$c] = [];
        }
        foreach ($keluarList as $lk) {
            $cat = $lk['kategori'] ?: 'Lainnya';
            if (!in_array($cat, $categories)) {
                $groupedKeluar['Lainnya'][] = $lk;
            } else {
                $groupedKeluar[$cat][] = $lk;
            }
        }

        echo "<table border='1'>";
        echo "<colgroup><col width='40'><col width='100'><col width='150'><col width='200'><col width='250'></colgroup>";
        echo "<tr><th colspan='5' style='text-align:center; background:#f2f2f2;'><strong>RIWAYAT PENGELUARAN PER KATEGORI</strong></th></tr>";
        echo "<tr><th colspan='5' style='text-align:center;'><strong>Proyek: " . htmlspecialchars($namaProyek) . " (Progress: " . $progressTerbaru . "%)</strong></th></tr>";
        echo "<tr><td colspan='5'></td></tr>";

        foreach ($groupedKeluar as $categoryName => $items) {
            echo "<tr><td colspan='5' style='background:#f1f2f6;'><strong>📁 " . strtoupper($categoryName) . " (Total: Rp " . number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') . ")</strong></td></tr>";
            echo "<tr><th>No</th><th>Tanggal</th><th>Jumlah</th><th>Kepada / Tujuan</th><th>Keterangan</th></tr>";

            if (empty($items)) {
                echo "<tr><td colspan='5' style='text-align:center; color:#7f8c8d;'>Tidak ada transaksi untuk kategori ini</td></tr>";
            } else {
                $no = 1;
                foreach ($items as $r) {
                    $formattedDate = date('d/m/Y', strtotime($r['tanggal']));
                    echo "<tr><td>{$no}</td><td style='mso-number-format:\"\@\";'>" . htmlspecialchars($formattedDate) . "</td><td style='text-align:right;'>Rp" . number_format($r['jumlah'],0,',','.') . "</td><td>" . htmlspecialchars($r['sumber'] ?? '-') . "</td><td>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td></tr>";
                    $no++;
                }
            }
            echo "<tr><td colspan='5'></td></tr>";
        }
        echo "</table>"; exit;
    }

    // ─── EXPORT EXCEL (lama — dipertahankan) ─────────────────────────
    public function exportExcel() {
        $selectedProyek = $_SESSION['selected_project_id'] ?? null;
        $riwayatPerProyek = $this->model->getRiwayatPerProyek($selectedProyek);

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Laporan_Keuangan_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "<table border='1'><tr><th>No</th><th>Tanggal</th><th>Proyek</th><th>Tipe</th><th>Jumlah</th><th>Sumber</th><th>Keterangan</th></tr>";
        $no = 1;
        foreach ($riwayatPerProyek as $group) {
            $nama = $group['proyek']['nama_proyek'];
            echo "<tr><td colspan='7'><strong>" . htmlspecialchars($nama) . "</strong></td></tr>";
            if (empty($group['transaksi'])) {
                echo "<tr><td colspan='7'>Tidak ada transaksi</td></tr>"; continue;
            }
            foreach ($group['transaksi'] as $k) {
                echo "<tr><td>{$no}</td><td>" . $k['tanggal'] . "</td><td>" . htmlspecialchars($k['nama_proyek']) . "</td><td>" . ucfirst($k['tipe']) . "</td><td>" . $k['jumlah'] . "</td><td>" . htmlspecialchars($k['sumber'] ?? '-') . "</td><td>" . htmlspecialchars($k['keterangan'] ?? '-') . "</td></tr>";
                $no++;
            }
        }
        echo "</table>"; exit;
    }
}
