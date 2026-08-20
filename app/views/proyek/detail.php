<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ========== STYLES ========== */
.detail-wrapper { background: #fff; border-radius: 24px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,.08); }
.dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px; transition: all 0.3s ease; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.stat-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; }
.stat-icon.progress { background: #dbeafe; color: #2563eb; }
.stat-icon.budget { background: #dcfce7; color: #16a34a; }
.stat-icon.work { background: #fef3c7; color: #d97706; }
.stat-icon.doc { background: #fce7f3; color: #db2777; }
.stat-value { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
.stat-label { color: #64748b; font-size: 14px; }
.stat-trend { font-size: 12px; margin-top: 10px; display: flex; align-items: center; gap: 5px; }
.trend-up { color: #16a34a; }
.trend-down { color: #dc2626; }
.project-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; flex-wrap: wrap; gap: 20px; }
.project-info { display: flex; gap: 20px; flex-wrap: wrap; }
.project-title h1 { font-size: 28px; margin-bottom: 8px; }
.project-sub { color: #64748b; margin-bottom: 15px; }
.project-stats { display: flex; gap: 40px; margin-top: 20px; flex-wrap: wrap; }
.stat-item small { color: #94a3b8; display: block; margin-bottom: 5px; }
.stat-item strong { font-size: 18px; }
.progress-area { min-width: 250px; }
.progress-bar { width: 100%; height: 12px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 10px; }
.progress-fill { height: 100%; background: #22c55e; border-radius: 10px; transition: width 0.5s ease; }
.tabs { display: flex; gap: 30px; border-bottom: 1px solid #e2e8f0; margin: 30px 0; flex-wrap: wrap; }
.tab { padding-bottom: 14px; cursor: pointer; font-weight: 600; color: #64748b; transition: .2s; display: flex; align-items: center; gap: 8px; }
.tab.active { color: #2563eb; border-bottom: 3px solid #2563eb; }
.tab:hover { color: #2563eb; }
.detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.chart-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; }
.chart-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.work-list { display: flex; flex-direction: column; gap: 20px; }
.work-item { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.work-left { display: flex; align-items: center; gap: 10px; min-width: 120px; }
.work-progress { flex: 1; height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden; }
.work-progress span { display: block; height: 100%; background: #22c55e; }
.badge-status { padding: 8px 14px; border-radius: 10px; background: #dbeafe; color: #2563eb; font-weight: 600; font-size: 13px; display: inline-block; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table th, .custom-table td { padding: 14px; border-bottom: 1px solid #e2e8f0; text-align: left; }
.custom-table th { background: #f8fafc; font-weight: 600; }
.job-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.job-card { border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; background: #fff; transition: all 0.3s ease; }
.job-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.job-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
.job-status { padding: 6px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; }
.selesai { background: #dcfce7; color: #16a34a; }
.proses { background: #fef9c3; color: #ca8a04; }
.belum-mulai { background: #f1f5f9; color: #64748b; }
.job-progress { width: 100%; height: 10px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin: 15px 0; }
.job-progress span { display: block; height: 100%; background: #2563eb; }
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
.gallery-card { border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0; background: #fff; }
.gallery-card img { width: 100%; height: 220px; object-fit: cover; }
.gallery-body { padding: 18px; }
.alert-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
.alert-box.warning { background: #fffbeb; border-left-color: #f59e0b; }
.alert-box.info { background: #dbeafe; border-left-color: #2563eb; }
.budget-summary { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
.budget-item { text-align: center; padding: 15px; background: #f8fafc; border-radius: 12px; }
.budget-value.positive { color: #16a34a; }
.budget-value.negative { color: #dc2626; }
.filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-input { padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; }
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
.kpi-card { text-align: center; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; color: white; }
.kpi-value { font-size: 24px; font-weight: 700; }
.back-button { display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; padding: 10px 20px; border-radius: 12px; color: #2563eb; text-decoration: none; margin-bottom: 20px; }
.btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
.btn-primary { background: #2563eb; color: white; }
.btn-outline { background: transparent; border: 1px solid #2563eb; color: #2563eb; }
@media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } .tabs { overflow-x: auto; } .dashboard-stats { grid-template-columns: repeat(2, 1fr); } }
</style>

<?php
// Inisialisasi variabel dengan nilai default jika tidak ada dari controller
$rincianPekerjaan = $rincianPekerjaan ?? [];
$pengeluaranList = $pengeluaranList ?? [];
$dokumentasiList = $dokumentasiList ?? [];
$progressHistory = $progressHistory ?? [];

$totalPekerjaan = $totalPekerjaan ?? 0;
$pekerjaanSelesai = $pekerjaanSelesai ?? 0;
$totalDokumentasi = $totalDokumentasi ?? 0;
$progressTrend = $progressTrend ?? 0;
$targetProgress = $targetProgress ?? 0;

$nilaiKontrak = $proyek['nilai_kontrak'] ?? 0;
$totalBiaya = $proyek['total_biaya'] ?? 0;
$budgetPercentage = ($nilaiKontrak > 0) ? ($totalBiaya / $nilaiKontrak) * 100 : 0;
$sisaBudget = $nilaiKontrak - $totalBiaya;
?>

<div class="detail-wrapper">

    <a href="<?= BASE_URL ?>/public/index.php?page=proyek" class="back-button">
        <i class="ri-arrow-left-line"></i> Kembali ke Daftar Proyek
    </a>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon progress"><i class="ri-line-chart-line"></i></div>
            <div class="stat-value"><?= $proyek['progress_terbaru'] ?? 0 ?>%</div>
            <div class="stat-label">Progress Keseluruhan</div>
            <div class="stat-trend trend-up"><i class="ri-arrow-up-line"></i> +<?= $progressTrend ?>% minggu ini</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon budget"><i class="ri-wallet-line"></i></div>
            <div class="stat-value"><?= number_format($budgetPercentage, 1) ?>%</div>
            <div class="stat-label">Budget Terpakai</div>
            <div class="stat-trend"><i class="ri-arrow-down-line"></i> Sisa: Rp <?= number_format($sisaBudget, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon work"><i class="ri-hard-drive-line"></i></div>
            <div class="stat-value"><?= $totalPekerjaan ?></div>
            <div class="stat-label">Total Pekerjaan</div>
            <div class="stat-trend trend-up"><i class="ri-checkbox-circle-line"></i> <?= $pekerjaanSelesai ?> selesai</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon doc"><i class="ri-image-line"></i></div>
            <div class="stat-value"><?= $totalDokumentasi ?></div>
            <div class="stat-label">Dokumentasi</div>
            <div class="stat-trend trend-up"><i class="ri-upload-line"></i> +<?= $dokumentasiBaru ?? 0 ?> baru</div>
        </div>
    </div>

    <!-- Alert jika budget melebihi -->
    <?php if($budgetPercentage > 85): ?>
    <div class="alert-box warning">
        <i class="ri-alert-line"></i>
        <div>Perhatian! Penggunaan budget telah mencapai <?= number_format($budgetPercentage, 1) ?>%.</div>
    </div>
    <?php endif; ?>

    <!-- Header Proyek -->
    <div class="project-top">
        <div class="project-info">
            <div class="project-title">
                <h1><?= htmlspecialchars($proyek['nama_proyek'] ?? 'Proyek') ?></h1>
                <div class="project-sub"><i class="ri-map-pin-line"></i> <?= htmlspecialchars($proyek['lokasi'] ?? '-') ?></div>
                <span class="badge-status"><i class="ri-information-line"></i> <?= ucfirst($proyek['status'] ?? 'Belum Diketahui') ?></span>
                <div class="project-stats">
                    <div class="stat-item"><small>Total Budget</small><strong>Rp <?= number_format($nilaiKontrak, 0, ',', '.') ?></strong></div>
                    <div class="stat-item"><small>Pengeluaran</small><strong>Rp <?= number_format($totalBiaya, 0, ',', '.') ?></strong></div>
                    <div class="stat-item"><small>Sisa Budget</small><strong>Rp <?= number_format($sisaBudget, 0, ',', '.') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="progress-area">
            <strong style="font-size:24px"><?= $proyek['progress_terbaru'] ?? 0 ?>%</strong>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $proyek['progress_terbaru'] ?? 0 ?>%"></div></div>
            <small>Target: <?= $targetProgress ?>% | Selisih: <?= ($proyek['progress_terbaru'] ?? 0) - $targetProgress ?>%</small>
        </div>
    </div>

   <!-- Tombol Aksi -->
    <div class="action-buttons" style="display: flex; gap: 10px; margin: 20px 0; align-items: center; flex-wrap: wrap;">
        <?php if (roleCanManage('proyek')): ?>
        <a href="<?= BASE_URL ?>/public/index.php?page=proyek&action=edit&id=<?= $proyek['id'] ?? '' ?>" class="btn btn-primary"><i class="ri-edit-line"></i> Edit Proyek</a>
        <a href="javascript:void(0)"
        onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=proyek&action=delete&id=<?= $proyek['id'] ?? '' ?>', '<?= htmlspecialchars($proyek['nama_proyek'] ?? '', ENT_QUOTES) ?>')"
        class="btn" style="background:#e74c3c; color:white;"><i class="ri-delete-bin-line"></i> Hapus Proyek</a>
        <?php endif; ?>
        
        <a href="<?= BASE_URL ?>/public/index.php?page=keuangan&id_proyek=<?= $proyek['id'] ?? '' ?>" class="btn btn-outline"><i class="ri-add-line"></i> Tambah Pengeluaran</a>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" data-tab="dashboard"><i class="ri-dashboard-line"></i> Dashboard</div>
        <div class="tab" data-tab="grafik"><i class="ri-line-chart-line"></i> Grafik</div>
        <div class="tab" data-tab="pengeluaran"><i class="ri-wallet-line"></i> Pengeluaran</div>
        <div class="tab" data-tab="rincian"><i class="ri-list-check-2"></i> Pekerjaan</div>
        <div class="tab" data-tab="dokumentasi"><i class="ri-image-line"></i> Dokumentasi</div>
        <div class="tab" data-tab="analytics"><i class="ri-bar-chart-2-line"></i> Analytics</div>
        <div class="tab" data-tab="rab"><i class="ri-clipboard-line"></i> RAB & Pekerjaan</div>
        <div class="tab" data-tab="kurva"><i class="ri-line-chart-line"></i> Kurva S</div>
    </div>

    <!-- Tab RAB & Pekerjaan -->
    <div class="tab-content" id="rab">
        <?php
        ?>
        
        <!-- Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value"><?= $summaryPekerjaan['total_pekerjaan'] ?></div>
                <div class="stat-label">Total Pekerjaan</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value" style="color: #16a34a;"><?= $summaryPekerjaan['selesai'] ?></div>
                <div class="stat-label">Selesai</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value" style="color: #f59e0b;"><?= $summaryPekerjaan['dalam_proses'] ?></div>
                <div class="stat-label">Dalam Proses</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value">Rp <?= number_format(($summaryPekerjaan['total_nilai_rab'] ?? 0) / 1000000, 1) ?>M</div>
                <div class="stat-label">Total RAB</div>
            </div>
        </div>
        
        <!-- Tombol Tambah -->
        <?php if (roleCanManage('pekerjaan')): ?>
        <div style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=create&id_proyek=<?= $proyek['id'] ?>" class="btn btn-primary">
                <i class="ri-add-line"></i> Tambah Pekerjaan
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Tabel Pekerjaan -->
        <div class="chart-card">
            <div class="chart-title">Daftar Pekerjaan (RAB)</div>
            <div class="table-wrap" style="overflow-x: auto;">
                <table class="custom-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Pekerjaan</th>
                            <th>Bobot</th>
                            <th>Nilai</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <?php if (roleCanManage('pekerjaan')): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pekerjaanList)): ?>
                        <tr>
                            <td colspan="<?= roleCanManage('pekerjaan') ? 7 : 6 ?>" style="text-align: center; padding: 40px;">
                                Belum ada data pekerjaan.
                                <?php if (roleCanManage('pekerjaan')): ?>
                                <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=create&id_proyek=<?= $proyek['id'] ?>">Tambah sekarang</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pekerjaanList as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($p['nama_pekerjaan']) ?></strong></td>
                            <td><?= number_format($p['bobot'], 2) ?>%</td>
                            <td>Rp <?= number_format($p['nilai_pekerjaan'], 0, ',', '.') ?></td>
                            <td style="min-width: 120px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="progress-bar" style="flex: 1; height: 8px;">
                                        <div class="progress-fill" style="width: <?= $p['progress_pekerjaan'] ?>%;"></div>
                                    </div>
                                    <span style="font-size: 12px;"><?= $p['progress_pekerjaan'] ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-status" style="background: <?= $p['status_pekerjaan'] == 'selesai' ? '#dcfce7' : ($p['status_pekerjaan'] == 'dalam_proses' ? '#fef3c7' : '#f1f5f9'); ?>; color: <?= $p['status_pekerjaan'] == 'selesai' ? '#16a34a' : ($p['status_pekerjaan'] == 'dalam_proses' ? '#d97706' : '#64748b'); ?>">
                                    <?= $p['status_pekerjaan'] == 'selesai' ? 'Selesai' : ($p['status_pekerjaan'] == 'dalam_proses' ? 'Dalam Proses' : 'Belum Mulai') ?>
                                </span>
                            </td>
                            <?php if (roleCanManage('pekerjaan')): ?>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=edit&id=<?= $p['id'] ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=delete&id=<?= $p['id'] ?>', '<?= htmlspecialchars($p['nama_pekerjaan']) ?>')" class="btn" style="background: #dc2626; color: white; padding: 5px 10px; font-size: 12px;">Hapus</a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Kurva S -->
    <div class="tab-content" id="kurva">
        <?php
        // Load ProgressMingguanModel
        if (!class_exists('ProgressMingguanModel')) {
            require_once BASE_PATH . '/app/models/ProgressMingguanModel.php';
        }
        $progressMingguanModel = new ProgressMingguanModel();
        $kurvaData = $progressMingguanModel->getKurvaSData($proyek['id']);
        $progressList = $progressMingguanModel->getByProyekId($proyek['id']);
        
        // Hitung statistik
        $totalTarget = 0;
        $totalRealisasi = 0;
        $selisihAkhir = 0;

        if (!empty($kurvaData)) {
            $lastKurva = end($kurvaData);

            $totalTarget = (float)$lastKurva['target_rencana'];
            $totalRealisasi = (float)$lastKurva['realisasi'];
            $selisihAkhir = $totalRealisasi - $totalTarget;
        }
        ?>
        
        <!-- Summary Cards -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value"><?= count($kurvaData) ?></div>
                <div class="stat-label">Total Minggu</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value"><?= round($totalTarget, 1) ?>%</div>
                <div class="stat-label">Target Akhir</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value" style="color: <?= $selisihAkhir >= 0 ? '#16a34a' : '#dc2626'; ?>">
                    <?= round($totalRealisasi, 1) ?>%
                </div>
                <div class="stat-label">Realisasi Akhir</div>
            </div>
            <div class="stat-card" style="text-align: center;">
                <div class="stat-value" style="color: <?= $selisihAkhir >= 0 ? '#16a34a' : '#dc2626'; ?>">
                    <?= $selisihAkhir >= 0 ? '+' : '' ?><?= round($selisihAkhir, 1) ?>%
                </div>
                <div class="stat-label">Selisih</div>
            </div>
        </div>
        
        <!-- Tombol Tambah -->
        <?php if (roleCanManage('kurva_s')): ?>
        <div style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=create&id_proyek=<?= $proyek['id'] ?>" class="btn btn-primary">
                <i class="ri-add-line"></i> Tambah Data Mingguan
            </a>
        </div>
        <?php endif; ?>
        
        <!-- Grafik Kurva S -->
        <div class="chart-card">
            <div class="chart-title">📈 Kurva S - Progress Rencana vs Realisasi</div>
            <?php if (empty($kurvaData)): ?>
            <div class="alert-box info" style="text-align: center; padding: 60px;">
                Belum ada data progress mingguan untuk Kurva S.
                <?php if (roleCanManage('kurva_s')): ?>
                <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=create&id_proyek=<?= $proyek['id'] ?>">Tambah sekarang</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div style="height: 350px;">
                <canvas id="kurvaSChartProyek"></canvas>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Tabel Data Mingguan -->
        <?php if (!empty($progressList)): ?>
        <div class="chart-card" style="margin-top: 20px;">
            <div class="chart-title">Data Progress Mingguan</div>
            <div class="table-wrap" style="overflow-x: auto;">
                <table class="custom-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Minggu</th>
                            <th>Target</th>
                            <th>Realisasi</th>
                            <th>Selisih</th>
                            <th>Periode</th>
                            <?php if (roleCanManage('kurva_s')): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progressList as $p): 
                            $selisih = $p['realisasi'] - $p['target_rencana'];
                        ?>
                        <tr>
                            <td><strong>Minggu <?= $p['minggu_ke'] ?></strong></td>
                            <td><?= number_format($p['target_rencana'], 1) ?>%</td>
                            <td><?= number_format($p['realisasi'], 1) ?>%</td>
                            <td style="color: <?= $selisih >= 0 ? '#16a34a' : '#dc2626' ?>;">
                                <?= $selisih >= 0 ? '+' : '' ?><?= number_format($selisih, 1) ?>%
                            </td>
                            <td style="font-size: 12px;">
                                <?= date('d/m/Y', strtotime($p['tanggal_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($p['tanggal_selesai'])) ?>
                            </td>
                            <?php if (roleCanManage('kurva_s')): ?>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=edit&id=<?= $p['id'] ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 12px;">Edit</a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=kurva_s&action=delete&id=<?= $p['id'] ?>', 'minggu ke-<?= $p['minggu_ke'] ?>')" class="btn" style="background: #dc2626; color: white; padding: 5px 10px; font-size: 12px;">Hapus</a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

<script>
// Chart untuk Kurva S (tambahkan di bagian script yang sudah ada)
<?php if (!empty($kurvaData)): ?>
const kurvaDataProyek = <?= json_encode($kurvaData) ?>;
const labelsProyek = kurvaDataProyek.map(item => 'Minggu ' + item.minggu_ke);
const targetDataProyek = kurvaDataProyek.map(item => parseFloat(item.target_rencana));
const realisasiDataProyek = kurvaDataProyek.map(item => parseFloat(item.realisasi));

if(document.getElementById('kurvaSChartProyek')) {
    new Chart(document.getElementById('kurvaSChartProyek').getContext('2d'), {
        type: 'line',
        data: {
            labels: labelsProyek,
            datasets: [
                {
                    label: 'Rencana (Target)',
                    data: targetDataProyek,
                    borderColor: '#2563eb',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Realisasi (Aktual)',
                    data: realisasiDataProyek,
                    borderColor: '#16a34a',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#16a34a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toFixed(1) + '%';
                        }
                    }
                },
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { callback: v => v + '%' },
                    title: { display: true, text: 'Progress Kumulatif (%)' }
                },
                x: {
                    title: { display: true, text: 'Periode (Minggu)' }
                }
            }
        }
    });
}
<?php endif; ?>
</script>

    <!-- Tab Dashboard -->
    <div class="tab-content active" id="dashboard">
        <div class="kpi-grid">
            <div class="kpi-card" style="background: linear-gradient(135deg, #667eea, #764ba2);"><div class="kpi-value"><?= $proyek['progress_terbaru'] ?? 0 ?>%</div><div class="kpi-label">Progress</div></div>
            <div class="kpi-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);"><div class="kpi-value"><?= number_format($budgetPercentage, 1) ?>%</div><div class="kpi-label">Budget Terpakai</div></div>
        </div>
        <div class="detail-grid" style="margin-top:20px">
            <div class="chart-card">
                <div class="chart-title">Ringkasan Progress Pekerjaan</div>
                <div class="work-list" id="work-list-container"></div>
            </div>
            <div class="chart-card">
                <div class="chart-title">Ringkasan Budget</div>
                <div class="budget-summary">
                    <div class="budget-item"><div class="budget-label">Total Budget</div><div class="budget-value">Rp <?= number_format($nilaiKontrak, 0, ',', '.') ?></div></div>
                    <div class="budget-item"><div class="budget-label">Realisasi</div><div class="budget-value <?= $budgetPercentage > 100 ? 'negative' : 'positive' ?>">Rp <?= number_format($totalBiaya, 0, ',', '.') ?></div></div>
                </div>
                <div class="progress-bar" style="height:20px"><div class="progress-fill" style="width:<?= min(100, $budgetPercentage) ?>%; background:#2563eb"></div></div>
                <small style="text-align:center; display:block; margin-top:8px">Realisasi: <?= number_format($budgetPercentage, 1) ?>%</small>
            </div>
        </div>
        <div class="chart-card" style="margin-top:20px">
            <div class="chart-title">Aktivitas Terbaru <i class="ri-refresh-line" style="cursor:pointer" onclick="refreshActivity()"></i></div>
            <div id="recent-activities"></div>
        </div>
    </div>

    <!-- Tab Grafik -->
    <div class="tab-content" id="grafik">
        <div class="detail-grid">
            <div class="chart-card"><div class="chart-title">Progress Proyek</div><div style="height:300px"><canvas id="progressChart"></canvas></div></div>
            <div class="chart-card"><div class="chart-title">Prediksi Progress</div><div style="height:300px"><canvas id="predictionChart"></canvas></div></div>
        </div>
        <div class="chart-card" style="margin-top:20px"><div class="chart-title">Perbandingan Progress vs Target</div><div style="height:300px"><canvas id="comparisonChart"></canvas></div></div>
    </div>

    <!-- Tab Pengeluaran -->
    <div class="tab-content" id="pengeluaran">
        <div class="detail-grid">
            <div class="chart-card"><div class="chart-title">Pengeluaran per Kategori</div><div style="height:280px"><canvas id="expenseChart"></canvas></div></div>
            <div class="chart-card"><div class="chart-title">Tren Pengeluaran</div><div style="height:280px"><canvas id="trendChart"></canvas></div></div>
        </div>
        <div class="chart-card" style="margin-top:20px">
            <div class="chart-title">Data Pengeluaran Detail</div>
            <div class="filter-bar">
                <input type="text" id="search-expense" class="filter-input" placeholder="Cari..." onkeyup="filterExpenseTable()">
                <select id="filter-category" class="filter-input" onchange="filterExpenseTable()"><option value="">Semua</option><option>Material</option><option>Upah</option><option>Transport</option></select>
            </div>
            <table class="custom-table"><thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Nominal</th></tr></thead><tbody id="expense-tbody"></tbody></table>
        </div>
    </div>

    <!-- Tab Pekerjaan -->
    <div class="tab-content" id="rincian">
        <div class="filter-bar"><input type="text" id="search-job" class="filter-input" placeholder="Cari pekerjaan..." onkeyup="filterJobCards()"><select id="filter-job-status" class="filter-input" onchange="filterJobCards()"><option value="">Semua</option><option value="selesai">Selesai</option><option value="proses">Berjalan</option><option value="belum-mulai">Belum Mulai</option></select></div>
        <div class="job-grid" id="job-grid-container"></div>
    </div>

    <!-- Tab Dokumentasi -->
    <div class="tab-content" id="dokumentasi">
        <div class="filter-bar"><input type="text" id="search-doc" class="filter-input" placeholder="Cari dokumentasi..." onkeyup="filterDocCards()"></div>
        <div class="gallery-grid" id="gallery-container"></div>
    </div>

    <!-- Tab Analytics -->
    <div class="tab-content" id="analytics">
        <div class="detail-grid">
            <div class="chart-card"><div class="chart-title">Efisiensi Pekerjaan</div><div style="height:280px"><canvas id="efficiencyChart"></canvas></div></div>
            <div class="chart-card"><div class="chart-title">Distribusi Waktu</div><div style="height:280px"><canvas id="timeDistributionChart"></canvas></div></div>
        </div>
        <div class="chart-card" style="margin-top:20px"><div class="chart-title">Rekomendasi & Insight</div><div id="insights-container"></div></div>
    </div>

</div>

<script>
// Data dari PHP
const progressHistory = <?= json_encode($progressHistory) ?>;
const expenseData = <?= json_encode($pengeluaranList) ?>;
const pekerjaanList = <?= json_encode($rincianPekerjaan) ?>;
const dokumentasiList = <?= json_encode($dokumentasiList) ?>;
const kurvaData = <?= json_encode($kurvaData) ?>;

// Render functions
function renderWorkList() {
    const container = document.getElementById('work-list-container');
    if(!container) return;
    if(pekerjaanList.length === 0) { container.innerHTML = '<div class="alert-box info">Belum ada data</div>'; return; }
    container.innerHTML = pekerjaanList.map(w => `<div class="work-item"><div class="work-left"><i class="ri-hammer-line"></i> ${w.nama}</div><div class="work-progress"><span style="width:${w.progress || 0}%"></span></div><small>${w.progress || 0}%</small></div>`).join('');
}

function renderExpenseTable() {
    const tbody = document.getElementById('expense-tbody');
    if(!tbody) return;
    if(expenseData.length === 0) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center">Belum ada data</td></tr>'; return; }
    const search = document.getElementById('search-expense')?.value.toLowerCase() || '';
    const category = document.getElementById('filter-category')?.value || '';
    const filtered = expenseData.filter(e => (e.keterangan?.toLowerCase().includes(search) || e.kategori?.toLowerCase().includes(search)) && (!category || e.kategori === category));
    tbody.innerHTML = filtered.map(e => `<tr><td>${new Date(e.tanggal).toLocaleDateString('id-ID')}</td><td>${e.kategori}</td><td>${e.keterangan}</td><td>Rp ${e.nominal.toLocaleString('id-ID')}</td></tr>`).join('');
}

function renderJobCards() {
    const container = document.getElementById('job-grid-container');
    if(!container) return;
    if(pekerjaanList.length === 0) { container.innerHTML = '<div class="alert-box info">Belum ada data</div>'; return; }
    const search = document.getElementById('search-job')?.value.toLowerCase() || '';
    const status = document.getElementById('filter-job-status')?.value || '';
    const filtered = pekerjaanList.filter(j => j.nama?.toLowerCase().includes(search) && (!status || j.status === status));
    container.innerHTML = filtered.map(j => `<div class="job-card"><div class="job-head"><h3>${j.nama}</h3><span class="job-status ${j.status}">${j.status === 'selesai' ? 'Selesai' : (j.status === 'proses' ? 'Berjalan' : 'Belum Mulai')}</span></div><p>${j.deskripsi || '-'}</p><div class="job-progress"><span style="width:${j.progress || 0}%"></span></div><small>Estimasi: ${j.estimasi_hari || 0} Hari | Progress: ${j.progress || 0}%</small></div>`).join('');
}

function renderGallery() {
    const container = document.getElementById('gallery-container');
    if(!container) return;
    if(dokumentasiList.length === 0) { container.innerHTML = '<div class="alert-box info">Belum ada dokumentasi</div>'; return; }
    const search = document.getElementById('search-doc')?.value.toLowerCase() || '';
    const filtered = dokumentasiList.filter(d => d.judul?.toLowerCase().includes(search));
    container.innerHTML = filtered.map(d => `<div class="gallery-card"><img src="${d.file_path || 'https://via.placeholder.com/280x220'}" onerror="this.src='https://via.placeholder.com/280x220'"><div class="gallery-body"><h4>${d.judul}</h4><small>${new Date(d.tanggal).toLocaleDateString('id-ID')}</small><div class="job-progress"><span style="width:${d.progress || 0}%"></span></div></div></div>`).join('');
}

function renderRecentActivities() {
    const container = document.getElementById('recent-activities');
    if(!container) return;
    let html = '';
    if(progressHistory.slice(-3).length) html += progressHistory.slice(-3).map(p => `<div style="padding:10px 0; border-bottom:1px solid #e2e8f0"><i class="ri-line-chart-line" style="color:#2563eb"></i> Progress ${p.persentase}% (${new Date(p.tanggal).toLocaleDateString('id-ID')})</div>`).join('');
    if(expenseData.slice(-3).length) html += expenseData.slice(-3).map(e => `<div style="padding:10px 0; border-bottom:1px solid #e2e8f0"><i class="ri-wallet-line" style="color:#16a34a"></i> ${e.keterangan} - Rp ${e.nominal.toLocaleString('id-ID')}</div>`).join('');
    container.innerHTML = html || '<div class="alert-box info">Belum ada aktivitas</div>';
}

function renderInsights() {
    const container = document.getElementById('insights-container');
    if(!container) return;
    const totalProgress = <?= $proyek['progress_terbaru'] ?? 0 ?>;
    const budgetUsage = <?= $budgetPercentage ?>;
    let insights = [];
    if(totalProgress >= 75) insights.push('✅ Progress baik, proyek berjalan sesuai jadwal');
    else if(totalProgress < 50) insights.push('⚠️ Progress rendah, perlu percepatan');
    if(budgetUsage > 85) insights.push('💰 Budget mendekati batas, harap efisien');
    if(budgetUsage > 100) insights.push('🚨 Budget overrun! Segera evaluasi');
    container.innerHTML = insights.map(i => `<div style="padding:12px; background:#f8fafc; border-radius:10px; margin-bottom:10px">${i}</div>`).join('');
}

function filterExpenseTable() { renderExpenseTable(); }
function filterJobCards() { renderJobCards(); }
function filterDocCards() { renderGallery(); }
function refreshActivity() { renderRecentActivities(); }

// Charts
if(document.getElementById('progressChart')) {
    new Chart(document.getElementById('progressChart').getContext('2d'), {
        type: 'line',
        data: { labels: progressHistory.map(p => new Date(p.tanggal).toLocaleDateString('id-ID',{day:'numeric',month:'short'})), datasets: [{ label:'Progress', data: progressHistory.map(p=>p.persentase), borderColor:'#2563eb', fill:true, tension:0.4 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } } }
    });
}
if(document.getElementById('predictionChart') && progressHistory.length >= 2) {

    const first = parseFloat(progressHistory[0].persentase) || 0;
    const last = parseFloat(progressHistory[progressHistory.length - 1].persentase) || 0;

    const kenaikanPerData = (last - first) / (progressHistory.length - 1);

    const predictionData = Array.from(
        { length: 4 },
        (_, i) => Math.min(100, last + kenaikanPerData * (i + 1))
    );

    new Chart(document.getElementById('predictionChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Sekarang', 'Periode +1', 'Periode +2', 'Periode +3', 'Periode +4'],
            datasets: [{
                label: 'Prediksi Progress',
                data: [last, ...predictionData],
                borderDash: [5, 5],
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

if(document.getElementById('comparisonChart') && kurvaData.length) {
    new Chart(document.getElementById('comparisonChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: kurvaData.map(p => 'Minggu ' + p.minggu_ke),
            datasets: [
                {
                    label: 'Rencana',
                    data: kurvaData.map(p => parseFloat(p.target_rencana)),
                    tension: 0.3
                },
                {
                    label: 'Realisasi',
                    data: kurvaData.map(p => parseFloat(p.realisasi)),
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}
if(expenseData.length && document.getElementById('expenseChart')) {
    const byCat = {};
    expenseData.forEach(e => byCat[e.kategori] = (byCat[e.kategori]||0) + e.nominal);
    new Chart(document.getElementById('expenseChart').getContext('2d'), { type:'doughnut', data:{ labels:Object.keys(byCat), datasets:[{ data:Object.values(byCat), backgroundColor:['#2563eb','#16a34a','#f59e0b','#dc2626'] }] }, options:{ responsive:true, maintainAspectRatio:false } });
}
if(expenseData.length && document.getElementById('trendChart')) {
    const monthly = {};
    expenseData.forEach(e => { const m = new Date(e.tanggal).toLocaleDateString('id-ID',{month:'short',year:'numeric'}); monthly[m] = (monthly[m]||0) + e.nominal; });
    new Chart(document.getElementById('trendChart').getContext('2d'), { type:'bar', data:{ labels:Object.keys(monthly), datasets:[{ label:'Pengeluaran', data:Object.values(monthly), backgroundColor:'#2563eb' }] }, options:{ responsive:true, maintainAspectRatio:false } });
}
if(pekerjaanList.length && document.getElementById('efficiencyChart')) {
    new Chart(document.getElementById('efficiencyChart').getContext('2d'), { type:'radar', data:{ labels:pekerjaanList.map(p=>p.nama), datasets:[{ label:'Progress', data:pekerjaanList.map(p=>p.progress||0), backgroundColor:'rgba(37,99,235,0.2)', borderColor:'#2563eb' }] }, options:{ responsive:true, maintainAspectRatio:false, scales:{ r:{ beginAtZero:true, max:100 } } } });
}
if(pekerjaanList.length && document.getElementById('timeDistributionChart')) {
    new Chart(document.getElementById('timeDistributionChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: pekerjaanList.map(p => p.nama),
            datasets: [{
                label: 'Bobot Pekerjaan',
                data: pekerjaanList.map(p => p.bobot || 0)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}


// Tab switching
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});

// Initial render
renderWorkList();
renderExpenseTable();
renderJobCards();
renderGallery();
renderRecentActivities();
renderInsights();
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>