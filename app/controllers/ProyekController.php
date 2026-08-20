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
        $importHistory = $this->model->getImportHistory($id);

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

    public function storeDokumentasi(int $id): void {
        requireManagerPermission('proyek');
        $redirect = BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $id;
        $proyek = $this->model->getById($id);
        $file = $_FILES['file_dokumentasi'] ?? null;

        if (!$proyek || !$file || $file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'File dokumentasi gagal diupload.'];
            header('Location: ' . $redirect); exit;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ukuran file maksimal 10 MB.'];
            header('Location: ' . $redirect); exit;
        }

        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowedTypes[$mime])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Dokumentasi harus berupa JPG, PNG, atau WEBP.'];
            header('Location: ' . $redirect); exit;
        }

        $uploadDirectory = BASE_PATH . '/public/uploads/dokumentasi';
        if (!is_dir($uploadDirectory)) mkdir($uploadDirectory, 0755, true);
        $fileName = 'proyek-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $allowedTypes[$mime];
        if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . '/' . $fileName)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'File dokumentasi tidak dapat disimpan.'];
            header('Location: ' . $redirect); exit;
        }

        $this->model->createDokumentasi([
            'id_proyek' => $id,
            'judul' => trim($_POST['judul'] ?? pathinfo($file['name'], PATHINFO_FILENAME)),
            'file_path' => 'uploads/dokumentasi/' . $fileName,
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'tanggal' => $_POST['tanggal'] ?? date('Y-m-d'),
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Dokumentasi berhasil ditambahkan.'];
        header('Location: ' . $redirect); exit;
    }

    public function deleteDokumentasi(int $id): void {
        requireManagerPermission('proyek');
        $idProyek = (int)($_GET['id_proyek'] ?? 0);
        $redirect = BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $idProyek;
        $dokumentasi = $this->model->getDokumentasiById($id, $idProyek);

        if (!$dokumentasi) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Dokumentasi tidak ditemukan.'];
            header('Location: ' . $redirect); exit;
        }

        if ($this->model->deleteDokumentasi($id, $idProyek)) {
            $relativePath = ltrim((string)$dokumentasi['file_path'], '/\\');
            $uploadRoot = realpath(BASE_PATH . '/public/uploads/dokumentasi');
            $filePath = realpath(BASE_PATH . '/public/' . $relativePath);
            if ($uploadRoot && $filePath && str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR) && is_file($filePath)) {
                unlink($filePath);
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Dokumentasi berhasil dihapus.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Dokumentasi gagal dihapus.'];
        }
        header('Location: ' . $redirect); exit;
    }

    public function exportDetailExcel(int $id): void {
        requireManagerPermission('proyek');
        if (!class_exists('ZipArchive')) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Export Excel membutuhkan ekstensi ZIP PHP. Aktifkan extension=zip pada php.ini Apache lalu restart Apache.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $id); exit;
        }
        $proyek = $this->model->getById($id);
        if (!$proyek) {
            header('Location: ' . BASE_URL . '/public/index.php?page=proyek');
            exit;
        }

        $db = getDB();
        $queries = [
            'RAB' => ['SELECT nama_pekerjaan, bobot, nilai_pekerjaan, progress_pekerjaan, status_pekerjaan FROM pekerjaan_proyek WHERE id_proyek = ? ORDER BY id ASC', [$id]],
            'Kurva S' => ['SELECT minggu_ke, target_rencana, realisasi, tanggal_mulai, tanggal_selesai FROM progress_mingguan WHERE id_proyek = ? ORDER BY minggu_ke ASC', [$id]],
            'Keuangan' => ['SELECT tipe, tanggal, jumlah, kategori, sumber, keterangan FROM laporan_keuangan WHERE id_proyek = ? ORDER BY tanggal ASC, id ASC', [$id]],
            'Dokumentasi' => ['SELECT judul, file_path, keterangan, tanggal FROM dokumentasi WHERE id_proyek = ? ORDER BY tanggal ASC, id ASC', [$id]],
        ];
        $section = $_GET['section'] ?? '';
        if (isset($queries[$section])) {
            $queries = [$section => $queries[$section]];
        }

        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $book->getProperties()->setTitle('Detail Proyek - ' . $proyek['nama_proyek']);
        $first = true;
        foreach ($queries as $sheetName => [$sql, $params]) {
            $sheet = $first ? $book->getActiveSheet() : $book->createSheet();
            $first = false;
            $sheet->setTitle($sheetName);
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $headers = array_keys($rows[0] ?? match ($sheetName) {
                'RAB' => ['nama_pekerjaan', 'bobot', 'nilai_pekerjaan', 'progress_pekerjaan', 'status_pekerjaan'],
                'Kurva S' => ['minggu_ke', 'target_rencana', 'realisasi', 'tanggal_mulai', 'tanggal_selesai'],
                'Keuangan' => ['tipe', 'tanggal', 'jumlah', 'kategori', 'sumber', 'keterangan'],
                default => ['judul', 'file_path', 'keterangan', 'tanggal'],
            });
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
            $sheet->fromArray([['Proyek', $proyek['nama_proyek']], $headers], null, 'A1');
            $sheet->mergeCells('A1:' . $lastColumn . '1');
            $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A2:' . $lastColumn . '2')->getFont()->setBold(true);
            $sheet->getStyle('A2:' . $lastColumn . '2')->getFill()->setFillType('solid')->getStartColor()->setARGB('D9EAF7');
            $line = 3;
            foreach ($rows as $row) {
                $sheet->fromArray([array_values($row)], null, 'A' . $line++);
            }
            foreach (range('A', $sheet->getHighestColumn()) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $filePrefix = $section !== '' ? str_replace(' ', '_', $section) : 'Detail_Proyek';
        header('Content-Disposition: attachment; filename="' . $filePrefix . '_' . $id . '_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save('php://output');
        exit;
    }

    public function importDetailExcel(int $id): void {
        requireManagerPermission('proyek');
        $proyek = $this->model->getById($id);
        $redirect = BASE_URL . '/public/index.php?page=proyek&action=detail&id=' . $id;
        $section = $_GET['section'] ?? 'Detail';
        $fileName = $_FILES['file']['name'] ?? '-';
        if (!$proyek || empty($_FILES['file']['tmp_name'])) {
            if ($proyek) $this->model->recordImport($id, $section, $fileName, 0, 'gagal', 'Proyek atau file Excel tidak ditemukan.');
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Proyek atau file Excel tidak ditemukan.'];
            header('Location: ' . $redirect); exit;
        }

        try {
            if (!class_exists('ZipArchive')) {
                throw new RuntimeException('Ekstensi ZIP PHP belum aktif. Aktifkan extension=zip pada php.ini Apache lalu restart Apache.');
            }
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['file']['tmp_name']);
            $db = getDB();
            $db->beginTransaction();
            $inserted = 0;
            $sheetMap = [
                'RAB' => ['table' => 'pekerjaan_proyek', 'columns' => ['nama_pekerjaan', 'bobot', 'nilai_pekerjaan', 'progress_pekerjaan', 'status_pekerjaan']],
                'Kurva S' => ['table' => 'progress_mingguan', 'columns' => ['minggu_ke', 'target_rencana', 'realisasi', 'tanggal_mulai', 'tanggal_selesai']],
                'Keuangan' => ['table' => 'laporan_keuangan', 'columns' => ['tipe', 'tanggal', 'jumlah', 'kategori', 'sumber', 'keterangan']],
                'Dokumentasi' => ['table' => 'dokumentasi', 'columns' => ['judul', 'file_path', 'keterangan', 'tanggal']],
            ];
            if (isset($sheetMap[$section])) {
                $sheetMap = [$section => $sheetMap[$section]];
            }
            foreach ($sheetMap as $sheetName => $definition) {
                if (!$book->sheetNameExists($sheetName)) continue;
                $rows = $book->getSheetByName($sheetName)->toArray(null, true, true, false);
                foreach (array_slice($rows, 2) as $row) {
                    $values = array_slice(array_pad($row, count($definition['columns']), null), 0, count($definition['columns']));
                    if (trim((string)($values[0] ?? '')) === '') continue;
                    if ($sheetName === 'Keuangan' && !in_array(strtolower(trim((string)$values[0])), ['pemasukan', 'pengeluaran'], true)) continue;
                    if ($sheetName === 'Kurva S' && (!is_numeric($values[0]) || !is_numeric($values[1] ?? null) || !is_numeric($values[2] ?? null))) continue;
                    $columns = array_merge(['id_proyek'], $definition['columns']);
                    $placeholders = implode(',', array_fill(0, count($columns), '?'));
                    $stmt = $db->prepare('INSERT INTO ' . $definition['table'] . ' (' . implode(',', $columns) . ') VALUES (' . $placeholders . ')');
                    $stmt->execute(array_merge([$id], $values));
                    $inserted++;
                }
            }
            $db->commit();
            $this->model->recordImport($id, $section, $fileName, $inserted, 'berhasil', 'Import selesai.');
            $_SESSION['flash'] = ['type' => 'success', 'message' => $inserted . ' data detail proyek berhasil diimport.'];
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $this->model->recordImport($id, $section, $fileName, $inserted ?? 0, 'gagal', $e->getMessage());
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Import Excel gagal: ' . $e->getMessage()];
        }
        header('Location: ' . $redirect); exit;
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
