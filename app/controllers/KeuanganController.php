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
        $proyekNama = $_SESSION['selected_project_name'] ?? 'Semua Proyek';

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"Keuangan_Masuk_" . date('Ymd') . ".xls\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Warna hijau untuk Keuangan Masuk
        $headerBg  = '#2E7D32';
        $headerBg2 = '#388E3C';
        $stripeBg  = '#E8F5E9';
        $borderCol = '#C8E6C9';
        $infoBg    = '#E8F5E9';
        $infoColor = '#1B5E20';

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        echo "<tr><td colspan='5' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN KEUANGAN MASUK</td></tr>";

        echo "<tr>";
        echo "<td colspan='3' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; border:1px solid {$borderCol};'>Proyek: <b>" . htmlspecialchars($proyekNama) . "</b></td>";
        echo "<td colspan='2' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; text-align:right; border:1px solid {$borderCol};'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";

        echo "<tr><td colspan='5' style='border:none; padding:4px;'></td></tr>";

        $cols = ['No', 'Tanggal', 'Jumlah (Rp)', 'Dari / Sumber', 'Keterangan'];
        echo "<tr>";
        foreach ($cols as $col) {
            echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:center; border:1px solid #1B5E20; font-weight:bold; font-size:14px;'>{$col}</th>";
        }
        echo "</tr>";

        $grandTotal = 0;
        $no = 1;
        foreach ($list as $r) {
            $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
            $jumlah = (float)$r['jumlah'];
            $grandTotal += $jumlah;
            echo "<tr style='background:{$rowBg};'>";
            echo "<td style='text-align:center; padding:5px 8px; border:1px solid {$borderCol};'>{$no}</td>";
            echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . date('d/m/Y', strtotime($r['tanggal'])) . "</td>";
            echo "<td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol}; font-weight:bold; color:#2E7D32;'>" . number_format($jumlah, 0, ',', '.') . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['sumber'] ?? '-') . "</td>";
            echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td>";
            echo "</tr>";
            $no++;
        }

        if (empty($list)) {
            echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#999; border:1px solid {$borderCol};'>Tidak ada data</td></tr>";
        }

        echo "<tr>";
        echo "<td colspan='2' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #1B5E20; font-size:14px;'>TOTAL PEMASUKAN</td>";
        echo "<td style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #1B5E20; font-size:14px;'>" . number_format($grandTotal, 0, ',', '.') . "</td>";
        echo "<td colspan='2' style='background:{$headerBg2}; border:1px solid #1B5E20;'></td>";
        echo "</tr>";

        echo "</table>";
        exit;
    }

    // ─── EXPORT KELUAR EXCEL ─────────────────────────────────────────
    public function exportKeluarExcel() {
        $idProyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        $list = $this->model->getKeluar($idProyek);

        $namaProyek = $_SESSION['selected_project_name'] ?? 'Semua Proyek';
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

        // Warna merah untuk Keuangan Keluar
        $headerBg  = '#B71C1C';
        $headerBg2 = '#C62828';
        $stripeBg  = '#FFEBEE';
        $borderCol = '#FFCDD2';
        $infoBg    = '#FFEBEE';
        $infoColor = '#7F0000';

        $categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
        $groupedKeluar = [];
        foreach ($categories as $c) $groupedKeluar[$c] = [];
        foreach ($list as $lk) {
            $cat = $lk['kategori'] ?: 'Lainnya';
            $groupedKeluar[in_array($cat, $categories) ? $cat : 'Lainnya'][] = $lk;
        }

        echo "\xEF\xBB\xBF";
        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        echo "<tr><td colspan='5' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN KEUANGAN KELUAR</td></tr>";

        echo "<tr>";
        echo "<td colspan='3' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; border:1px solid {$borderCol};'>Proyek: <b>" . htmlspecialchars($namaProyek) . "</b> &nbsp;|&nbsp; Progress: <b>{$progressTerbaru}%</b></td>";
        echo "<td colspan='2' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; text-align:right; border:1px solid {$borderCol};'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";

        echo "<tr><td colspan='5' style='border:none; padding:4px;'></td></tr>";

        $grandTotal = 0;
        foreach ($groupedKeluar as $categoryName => $items) {
            $catTotal = array_sum(array_column($items, 'jumlah'));
            $grandTotal += $catTotal;

            // Sub-header kategori
            echo "<tr><td colspan='5' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; padding:6px 10px; border:1px solid {$headerBg}; font-size:14px;'>"
                . strtoupper($categoryName)
                . " &nbsp;&mdash;&nbsp; Total: Rp " . number_format($catTotal, 0, ',', '.')
                . "</td></tr>";

            // Header kolom
            $cols = ['No', 'Tanggal', 'Jumlah (Rp)', 'Kepada / Tujuan', 'Keterangan'];
            echo "<tr>";
            foreach ($cols as $col) {
                echo "<th style='background:#EF9A9A; color:#7F0000; padding:6px 10px; text-align:center; border:1px solid {$borderCol}; font-weight:bold; font-size:14px;'>{$col}</th>";
            }
            echo "</tr>";

            if (empty($items)) {
                echo "<tr><td colspan='5' style='text-align:center; padding:12px; color:#999; border:1px solid {$borderCol};'>Tidak ada transaksi</td></tr>";
            } else {
                $no = 1;
                foreach ($items as $r) {
                    $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
                    echo "<tr style='background:{$rowBg};'>";
                    echo "<td style='text-align:center; padding:5px 8px; border:1px solid {$borderCol};'>{$no}</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . date('d/m/Y', strtotime($r['tanggal'])) . "</td>";
                    echo "<td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol}; font-weight:bold; color:#B71C1C;'>" . number_format((float)$r['jumlah'], 0, ',', '.') . "</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['sumber'] ?? '-') . "</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td>";
                    echo "</tr>";
                    $no++;
                }
            }
            echo "<tr><td colspan='5' style='border:none; padding:3px;'></td></tr>";
        }

        // Baris grand total
        echo "<tr>";
        echo "<td colspan='2' style='background:{$headerBg}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid {$headerBg}; font-size:14px;'>TOTAL PENGELUARAN</td>";
        echo "<td style='background:{$headerBg}; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid {$headerBg}; font-size:14px;'>" . number_format($grandTotal, 0, ',', '.') . "</td>";
        echo "<td colspan='2' style='background:{$headerBg}; border:1px solid {$headerBg};'></td>";
        echo "</tr>";

        echo "</table>";
        exit;
    }

    // ─── EXPORT LAPORAN EXCEL (gabungan) ─────────────────────────────
    public function exportLaporanExcel() {
        $idProyek = $_GET['id_proyek'] ?? $_SESSION['selected_project_id'] ?? null;
        $summary         = $this->model->getSummaryGabungan($idProyek);
        $keluarList      = $this->model->getKeluar($idProyek);

        $namaProyek = $_SESSION['selected_project_name'] ?? 'Semua Proyek';
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

        // Warna biru untuk Laporan Gabungan
        $headerBg  = '#1565C0';
        $headerBg2 = '#1976D2';
        $stripeBg  = '#E3F2FD';
        $borderCol = '#BBDEFB';
        $infoBg    = '#E3F2FD';
        $infoColor = '#0D47A1';

        // Hitung HPP
        $biayaGaji = 0; $biayaSewaAlat = 0; $biayaMaterialLangsung = 0; $biayaLainnya = 0;
        foreach ($keluarList as $lk) {
            $cat = $lk['kategori'] ?? '';
            if ($cat === 'Gaji')                  $biayaGaji              += (float)$lk['jumlah'];
            elseif ($cat === 'Sewa Alat')          $biayaSewaAlat          += (float)$lk['jumlah'];
            elseif ($cat === 'Pembelian Material') $biayaMaterialLangsung  += (float)$lk['jumlah'];
            else                                   $biayaLainnya           += (float)$lk['jumlah'];
        }
        $totalBiayaMaterial = ($summary['nilai_barang_masuk'] ?? 0) + $biayaMaterialLangsung;
        $totalHPP   = $totalBiayaMaterial + $biayaGaji + $biayaSewaAlat + $biayaLainnya;
        $labaBruto  = ($summary['total_pemasukan'] ?? 0) - $totalHPP;

        echo "\xEF\xBB\xBF";
        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";

        // Judul
        echo "<tr><td colspan='3' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>LAPORAN KEUANGAN PROYEK</td></tr>";

        // Info proyek
        echo "<tr>";
        echo "<td colspan='2' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; border:1px solid {$borderCol};'>Proyek: <b>" . htmlspecialchars($namaProyek) . "</b> &nbsp;|&nbsp; Progress: <b>{$progressTerbaru}%</b></td>";
        echo "<td style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; text-align:right; border:1px solid {$borderCol};'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";
        echo "<tr><td colspan='3' style='border:none; padding:4px;'></td></tr>";

        // ── Bagian 1: Laba Rugi ──
        echo "<tr><td colspan='3' style='background:{$headerBg}; color:#FFFFFF; font-weight:bold; font-size:16px; padding:8px 10px; border:1px solid {$headerBg};'>LAPORAN LABA RUGI</td></tr>";

        echo "<tr>";
        echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; border:1px solid {$borderCol}; font-size:14px;'>Keterangan</th>";
        echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:right; border:1px solid {$borderCol}; font-size:14px;'>Rincian (Rp)</th>";
        echo "<th style='background:{$headerBg2}; color:#FFFFFF; padding:7px 10px; text-align:right; border:1px solid {$borderCol}; font-size:14px;'>Total (Rp)</th>";
        echo "</tr>";

        // Pendapatan
        echo "<tr style='background:#E8F5E9;'><td colspan='3' style='padding:6px 10px; border:1px solid {$borderCol}; font-weight:bold; color:#1B5E20; font-size:14px;'>PENDAPATAN</td></tr>";
        echo "<tr style='background:{$stripeBg};'><td style='padding:5px 20px; border:1px solid {$borderCol};'>Penjualan Jasa Konstruksi / Termin Proyek</td><td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol};'></td><td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol}; color:#2E7D32; font-weight:bold;'>" . number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') . "</td></tr>";
        echo "<tr style='background:{$headerBg2};'><td style='color:#FFFFFF; font-weight:bold; padding:6px 10px; border:1px solid {$borderCol};'>JUMLAH PENDAPATAN</td><td style='border:1px solid {$borderCol};'></td><td style='text-align:right; color:#FFFFFF; font-weight:bold; padding:6px 8px; border:1px solid {$borderCol};'>" . number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') . "</td></tr>";

        echo "<tr><td colspan='3' style='border:none; padding:3px;'></td></tr>";

        // HPP
        echo "<tr style='background:#FFEBEE;'><td colspan='3' style='padding:6px 10px; border:1px solid {$borderCol}; font-weight:bold; color:#B71C1C; font-size:14px;'>HARGA POKOK PENGELUARAN (HPP)</td></tr>";
        $hppRows = [
            'Biaya Material (Barang Masuk + Cash)' => $totalBiayaMaterial,
            'Biaya Tenaga Kerja / Gaji'            => $biayaGaji,
            'Biaya Sewa Alat & Kendaraan'          => $biayaSewaAlat,
            'Biaya Lain-lain / Administrasi'       => $biayaLainnya,
        ];
        $i = 0;
        foreach ($hppRows as $label => $val) {
            $rowBg = ($i % 2 === 0) ? $stripeBg : '#FFFFFF';
            echo "<tr style='background:{$rowBg};'><td style='padding:5px 20px; border:1px solid {$borderCol};'>{$label}</td><td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol};'>" . number_format($val, 0, ',', '.') . "</td><td style='border:1px solid {$borderCol};'></td></tr>";
            $i++;
        }
        echo "<tr style='background:{$headerBg2};'><td style='color:#FFFFFF; font-weight:bold; padding:6px 10px; border:1px solid {$borderCol};'>JUMLAH HPP</td><td style='border:1px solid {$borderCol};'></td><td style='text-align:right; color:#FFFFFF; font-weight:bold; padding:6px 8px; border:1px solid {$borderCol};'>" . number_format($totalHPP, 0, ',', '.') . "</td></tr>";

        echo "<tr><td colspan='3' style='border:none; padding:3px;'></td></tr>";

        // Laba Bruto
        $labaBg    = $labaBruto >= 0 ? '#C8E6C9' : '#FFCDD2';
        $labaColor = $labaBruto >= 0 ? '#1B5E20'  : '#B71C1C';
        echo "<tr style='background:{$labaBg};'><td colspan='2' style='font-weight:bold; font-size:15px; padding:8px 10px; border:1px solid {$borderCol}; color:{$labaColor};'>LABA BRUTO</td><td style='text-align:right; font-weight:bold; font-size:15px; padding:8px; border:1px solid {$borderCol}; color:{$labaColor};'>" . number_format($labaBruto, 0, ',', '.') . "</td></tr>";

        echo "</table>";
        echo "<br><br>";

        // ── Bagian 2: Riwayat Pemasukan ──
        $masukList = $this->model->getMasuk($idProyek);
        $totalPemasukan = array_sum(array_column($masukList, 'jumlah'));

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";
        echo "<tr><td colspan='5' style='background:#2E7D32; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>RIWAYAT PEMASUKAN</td></tr>";
        echo "<tr>";
        echo "<td colspan='3' style='background:#E8F5E9; color:#1B5E20; font-size:14px; padding:6px 10px; border:1px solid #C8E6C9;'>Proyek: <b>" . htmlspecialchars($namaProyek) . "</b></td>";
        echo "<td colspan='2' style='background:#E8F5E9; color:#1B5E20; font-size:14px; padding:6px 10px; text-align:right; border:1px solid #C8E6C9;'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";
        echo "<tr><td colspan='5' style='border:none; padding:4px;'></td></tr>";

        $masukCols = ['No', 'Tanggal', 'Jumlah (Rp)', 'Dari / Sumber', 'Keterangan'];
        echo "<tr>";
        foreach ($masukCols as $col) {
            echo "<th style='background:#388E3C; color:#FFFFFF; padding:7px 10px; text-align:center; border:1px solid #1B5E20; font-weight:bold; font-size:14px;'>{$col}</th>";
        }
        echo "</tr>";

        if (empty($masukList)) {
            echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#999; border:1px solid #C8E6C9;'>Tidak ada data pemasukan</td></tr>";
        } else {
            $no = 1;
            foreach ($masukList as $r) {
                $rowBg = ($no % 2 === 0) ? '#FFFFFF' : '#E8F5E9';
                echo "<tr style='background:{$rowBg};'>";
                echo "<td style='text-align:center; padding:5px 8px; border:1px solid #C8E6C9;'>{$no}</td>";
                echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . date('d/m/Y', strtotime($r['tanggal'])) . "</td>";
                echo "<td style='text-align:right; padding:5px 8px; border:1px solid #C8E6C9; font-weight:bold; color:#2E7D32;'>" . number_format((float)$r['jumlah'], 0, ',', '.') . "</td>";
                echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($r['sumber'] ?? '-') . "</td>";
                echo "<td style='padding:5px 8px; border:1px solid #C8E6C9;'>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td>";
                echo "</tr>";
                $no++;
            }
        }

        echo "<tr>";
        echo "<td colspan='2' style='background:#388E3C; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #1B5E20; font-size:14px;'>TOTAL PEMASUKAN</td>";
        echo "<td style='background:#388E3C; color:#FFFFFF; font-weight:bold; text-align:right; padding:6px 10px; border:1px solid #1B5E20; font-size:14px;'>" . number_format($totalPemasukan, 0, ',', '.') . "</td>";
        echo "<td colspan='2' style='background:#388E3C; border:1px solid #1B5E20;'></td>";
        echo "</tr>";

        echo "</table>";
        echo "<br><br>";

        // ── Bagian 3: Riwayat Pengeluaran Per Kategori ──
        $categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
        $groupedKeluar = [];
        foreach ($categories as $c) $groupedKeluar[$c] = [];
        foreach ($keluarList as $lk) {
            $cat = $lk['kategori'] ?: 'Lainnya';
            $groupedKeluar[in_array($cat, $categories) ? $cat : 'Lainnya'][] = $lk;
        }

        echo "<table border='1' style='border-collapse:collapse; font-family:Arial; font-size:14px;'>";
        echo "<tr><td colspan='5' style='background:{$headerBg}; color:#FFFFFF; font-size:20px; font-weight:bold; text-align:center; padding:12px; border:none;'>RIWAYAT PENGELUARAN PER KATEGORI</td></tr>";
        echo "<tr>";
        echo "<td colspan='3' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; border:1px solid {$borderCol};'>Proyek: <b>" . htmlspecialchars($namaProyek) . "</b></td>";
        echo "<td colspan='2' style='background:{$infoBg}; color:{$infoColor}; font-size:14px; padding:6px 10px; text-align:right; border:1px solid {$borderCol};'>Tanggal Cetak: <b>" . date('d/m/Y H:i') . "</b></td>";
        echo "</tr>";
        echo "<tr><td colspan='5' style='border:none; padding:4px;'></td></tr>";

        foreach ($groupedKeluar as $categoryName => $items) {
            $catTotal = array_sum(array_column($items, 'jumlah'));
            echo "<tr><td colspan='5' style='background:{$headerBg2}; color:#FFFFFF; font-weight:bold; padding:6px 10px; border:1px solid {$headerBg}; font-size:14px;'>"
                . strtoupper($categoryName) . " &nbsp;&mdash;&nbsp; Total: Rp " . number_format($catTotal, 0, ',', '.')
                . "</td></tr>";

            $cols = ['No', 'Tanggal', 'Jumlah (Rp)', 'Kepada / Tujuan', 'Keterangan'];
            echo "<tr>";
            foreach ($cols as $col) {
                echo "<th style='background:#90CAF9; color:#0D47A1; padding:6px 10px; text-align:center; border:1px solid {$borderCol}; font-weight:bold; font-size:14px;'>{$col}</th>";
            }
            echo "</tr>";

            if (empty($items)) {
                echo "<tr><td colspan='5' style='text-align:center; padding:12px; color:#999; border:1px solid {$borderCol};'>Tidak ada transaksi</td></tr>";
            } else {
                $no = 1;
                foreach ($items as $r) {
                    $rowBg = ($no % 2 === 0) ? '#FFFFFF' : $stripeBg;
                    echo "<tr style='background:{$rowBg};'>";
                    echo "<td style='text-align:center; padding:5px 8px; border:1px solid {$borderCol};'>{$no}</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . date('d/m/Y', strtotime($r['tanggal'])) . "</td>";
                    echo "<td style='text-align:right; padding:5px 8px; border:1px solid {$borderCol}; font-weight:bold; color:#1565C0;'>" . number_format((float)$r['jumlah'], 0, ',', '.') . "</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['sumber'] ?? '-') . "</td>";
                    echo "<td style='padding:5px 8px; border:1px solid {$borderCol};'>" . htmlspecialchars($r['keterangan'] ?? '-') . "</td>";
                    echo "</tr>";
                    $no++;
                }
                // Subtotal per kategori
                echo "<tr style='background:{$stripeBg};'>";
                echo "<td colspan='2' style='font-weight:bold; color:{$infoColor}; text-align:right; padding:6px 10px; border:1px solid {$borderCol}; font-size:14px;'>Subtotal " . strtoupper($categoryName) . "</td>";
                echo "<td style='text-align:right; font-weight:bold; color:{$infoColor}; padding:6px 10px; border:1px solid {$borderCol}; font-size:14px;'>" . number_format($catTotal, 0, ',', '.') . "</td>";
                echo "<td colspan='2' style='border:1px solid {$borderCol};'></td>";
                echo "</tr>";
            }
            echo "<tr><td colspan='5' style='border:none; padding:3px;'></td></tr>";
        }

        // Grand total seluruh pengeluaran
        $grandTotalKeluar = array_sum(array_column($keluarList, 'jumlah'));
        echo "<tr style='background:{$headerBg};'>";
        echo "<td colspan='2' style='color:#FFFFFF; font-weight:bold; text-align:right; padding:8px 10px; border:1px solid {$headerBg}; font-size:15px;'>TOTAL SELURUH PENGELUARAN</td>";
        echo "<td style='text-align:right; color:#FFFFFF; font-weight:bold; padding:8px 10px; border:1px solid {$headerBg}; font-size:15px;'>" . number_format($grandTotalKeluar, 0, ',', '.') . "</td>";
        echo "<td colspan='2' style='background:{$headerBg}; border:1px solid {$headerBg};'></td>";
        echo "</tr>";

        echo "</table>";
        exit;
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
