<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php
$selectedProjectId = $selectedProjectId ?? null;
$selectedProjectName = $_SESSION['selected_project_name'] ?? null;
?>

<?php if ($role === 'manager'): ?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Proyek</div>
        <div class="stat-value"><?= $totalProyek ?></div>
        <div class="stat-trend">Semua proyek terdaftar</div>
        <div class="stat-icon">◫</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Proyek Aktif</div>
        <div class="stat-value"><?= $proyekAktif ?></div>
        <div class="stat-trend">Sedang berjalan</div>
        <div class="stat-icon">◈</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small">Rp <?= number_format($totalBiaya / 1000000, 1) ?>M</div>
        <div class="stat-trend"><?= 'Rp ' . number_format($totalBiaya, 0, ',', '.') ?></div>
        <div class="stat-icon">◪</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Modul</div>
        <div class="stat-value" style="font-size:1.25rem">Proyek &amp; Keuangan</div>
        <div class="stat-trend">Pemantauan operasional</div>
        <div class="stat-icon">◧</div>
    </div>
</div>

<!-- CHARTS -->
<div class="charts-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Biaya Pengeluaran per Bulan</span>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartBiaya"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Progress Setiap Proyek</span>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartProgress"></canvas>
            </div>
        </div>
    </div>
</div>

<?php endif; /* end manager-only charts */ ?>

<!-- DAFTAR PROYEK (Semua Role) -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Proyek</span>
        <a href="<?= BASE_URL ?>/public/index.php?page=proyek" class="btn btn-secondary btn-sm">Lihat Semua →</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Proyek</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th class="text-right">Total Biaya</th>
                    <th class="text-center">Pilih</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftarProyek)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:40px">Belum ada data proyek</td></tr>
                <?php else: ?>
                <?php foreach ($daftarProyek as $i => $p): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/public/index.php?page=proyek&action=detail&id=<?= $p['id'] ?>"
                           style="color:var(--black);font-weight:600;text-decoration:none;">
                            <?= htmlspecialchars($p['nama_proyek']) ?>
                        </a>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($p['lokasi']) ?></td>
                    <td>
                        <span class="badge badge-<?= $p['status'] ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex">
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" data-width="<?= $p['progress_terbaru'] ?>"></div>
                            </div>
                            <span class="progress-label"><?= $p['progress_terbaru'] ?>%</span>
                        </div>
                    </td>
                    <td class="text-right font-mono" style="font-size:13px">
                        Rp <?= number_format($p['total_biaya'], 0, ',', '.') ?>
                    </td>
                    <td class="text-center">
                        <?php $isSelected = ($selectedProjectId === (int)$p['id']); ?>
                        <?php if ($isSelected): ?>
                            <a href="<?= BASE_URL ?>/public/index.php?page=dashboard&action=clearProject"
                               class="btn btn-secondary btn-sm"
                               style="background:#7f8c8d; color:white; text-decoration:none; font-size:11px;">
                               ✓ Aktif — Batalkan
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/public/index.php?page=dashboard&action=selectProject&id=<?= $p['id'] ?>"
                               class="btn btn-primary btn-sm"
                               style="text-decoration:none; font-size:11px;">
                               Pilih
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($role === 'manager'): ?>
<script>
(function() {
    const rawBiaya = <?= json_encode($biayaPerBulan) ?>;
    const labels = rawBiaya.map(r => {
        const [y, m] = r.bulan.split('-');
        const bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return bln[parseInt(m)] + ' ' + y;
    });
    const data = rawBiaya.map(r => parseFloat(r.total));

    new Chart(document.getElementById('chartBiaya'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Pengeluaran (Rp)',
                data,
                backgroundColor: 'rgba(26,26,26,0.85)',
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID') } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } },
                y: {
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        font: { family: 'Outfit', size: 11 },
                        callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'M'
                    }
                }
            }
        }
    });
})();

(function() {
    const rawProg = <?= json_encode($progressProyek) ?>;
    const labels = rawProg.map(r => r.nama_proyek.length > 20 ? r.nama_proyek.substring(0,20)+'…' : r.nama_proyek);
    const data   = rawProg.map(r => parseInt(r.persentase));

    new Chart(document.getElementById('chartProgress'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Progress (%)',
                data,
                backgroundColor: data.map(v => v === 100 ? 'rgba(26,26,26,0.9)' : 'rgba(26,26,26,0.5)'),
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.x + '%' } }
            },
            scales: {
                x: {
                    min: 0, max: 100,
                    grid: { color: '#f0f0f0' },
                    ticks: { font: { family: 'Outfit', size: 11 }, callback: v => v + '%' }
                },
                y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11 } } }
            }
        }
    });
})();
</script>
<?php endif; /* end manager chart scripts */ ?>

<?php if ($role !== 'manager'): /* admin section */ ?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Karyawan</div>
        <div class="stat-value"><?= $totalKaryawan ?></div>
        <div class="stat-trend">Terdaftar di sistem</div>
        <div class="stat-icon">◩</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Absensi Bulan Ini</div>
        <div class="stat-value"><?= $absensiBulanIni ?></div>
        <div class="stat-trend">Catatan absensi</div>
        <div class="stat-icon">◈</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jenis Barang</div>
        <div class="stat-value"><?= $totalBarang ?></div>
        <div class="stat-trend">Item di inventori</div>
        <div class="stat-icon">◧</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Barang Keluar</div>
        <div class="stat-value"><?= number_format($totalBarangKeluar) ?></div>
        <div class="stat-trend">Total unit keluar</div>
        <div class="stat-icon">◫</div>
    </div>
</div>

<div class="charts-grid">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Rekap Status Absensi</span>
            <a href="<?= BASE_URL ?>/public/index.php?page=absensi" class="btn btn-secondary btn-sm">Kelola Absensi →</a>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="chartAbsensi"></canvas>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Stok Barang Rendah</span>
            <a href="<?= BASE_URL ?>/public/index.php?page=barang" class="btn btn-secondary btn-sm">Kelola Barang →</a>
        </div>
        <div class="card-body" style="padding:0">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Stok</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($barangStokRendah)): ?>
                        <tr><td colspan="3" class="text-center text-muted" style="padding:32px">Semua stok aman</td></tr>
                        <?php else: ?>
                        <?php foreach ($barangStokRendah as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars($b['nama_barang']) ?></td>
                            <td><span class="badge badge-pending" style="<?= $b['stok'] <= 10 ? 'background:#c0392b;color:white' : '' ?>"><?= (int) $b['stok'] ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($b['satuan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Absensi per Proyek</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Proyek</th>
                    <th>Pekerja</th>
                    <th>Hadir</th>
                    <th>Alpha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapAbsensi)): ?>
                <tr><td colspan="4" class="text-center text-muted" style="padding:40px">Belum ada data absensi</td></tr>
                <?php else: ?>
                <?php foreach ($rekapAbsensi as $r): ?>
                <tr>
                    <td style="font-weight:600"><?= htmlspecialchars($r['nama_proyek']) ?></td>
                    <td><?= (int) $r['total_pekerja'] ?></td>
                    <td><?= (int) $r['hadir'] ?></td>
                    <td><?= (int) $r['alpha'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    const raw = <?= json_encode($absensiPerStatus) ?>;
    const labelMap = { hadir: 'Hadir', izin: 'Izin', sakit: 'Sakit', alpha: 'Alpha' };
    const colors = {
        hadir: 'rgba(26,26,26,0.9)',
        izin: 'rgba(100,100,100,0.7)',
        sakit: 'rgba(150,150,150,0.7)',
        alpha: 'rgba(200,80,80,0.8)',
    };
    const labels = raw.map(r => labelMap[r.status] || r.status);
    const data = raw.map(r => parseInt(r.jumlah));
    const bg = raw.map(r => colors[r.status] || 'rgba(26,26,26,0.5)');

    new Chart(document.getElementById('chartAbsensi'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{ data, backgroundColor: bg, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Outfit', size: 12 } } }
            }
        }
    });
})();
</script>

<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
