<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- STAT CARDS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Proyek</div>
        <div class="stat-value"><?= $totalProyek ?></div>
        <div class="stat-trend">Semua proyek terdaftar</div>
        <div class="stat-icon">◫</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pekerja</div>
        <div class="stat-value"><?= $totalPekerja ?></div>
        <div class="stat-trend">Karyawan tercatat di absensi</div>
        <div class="stat-icon">◧</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Barang Keluar</div>
        <div class="stat-value"><?= number_format($totalBarangKeluar) ?></div>
        <div class="stat-trend">Total unit semua proyek</div>
        <div class="stat-icon">◩</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small">Rp <?= number_format($totalBiaya / 1000000, 1) ?>M</div>
        <div class="stat-trend"><?= 'Rp ' . number_format($totalBiaya, 0, ',', '.') ?></div>
        <div class="stat-icon">◪</div>
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

<!-- DAFTAR PROYEK -->
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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($daftarProyek)): ?>
                <tr><td colspan="6" class="text-center text-muted" style="padding:40px">Belum ada data proyek</td></tr>
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
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ---- Chart: Biaya per Bulan ----
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
                tooltip: {
                    callbacks: {
                        label: ctx => ' Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
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

// ---- Chart: Progress per Proyek ----
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

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
