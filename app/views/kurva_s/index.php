<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Filter Proyek -->
<div class="project-switch-bar">
    <div class="project-switch-info">
        <span>Proyek:</span>
        <strong><?= htmlspecialchars($proyek['nama_proyek'] ?? '-') ?></strong>
    </div>
    <form class="project-switch-form" action="<?= BASE_URL ?>/public/index.php" method="get">
        <input type="hidden" name="page" value="kurva_s">
        <select name="id_proyek" class="project-switch-select" onchange="this.form.submit()">
            <?php foreach ($allProyek as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == ($id_proyek ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_proyek']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-label">Total Minggu</div>
        <div class="stat-value"><?= count($kurvaData) ?></div>
        <div class="stat-trend">Periode berjalan</div>
        <div class="stat-icon">📅</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Target Akhir</div>
        <div class="stat-value"><?= round($totalTarget, 1) ?>%</div>
        <div class="stat-trend">Rencana kumulatif</div>
        <div class="stat-icon">🎯</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Realisasi Akhir</div>
        <div class="stat-value <?= $selisihAkhir >= 0 ? '' : 'text-danger' ?>">
            <?= round($totalRealisasi, 1) ?>%
        </div>
        <div class="stat-trend">Realisasi kumulatif</div>
        <div class="stat-icon">✅</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Selisih</div>
        <div class="stat-value <?= $selisihAkhir >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= $selisihAkhir >= 0 ? '+' : '' ?><?= round($selisihAkhir, 1) ?>%
        </div>
        <div class="stat-trend">Target vs Realisasi</div>
        <div class="stat-icon">📊</div>
    </div>
</div>

<!-- CHART KURVA S -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📈 Kurva S - Progress Rencana vs Realisasi</span>
        <div>
            <button onclick="exportChartAsImage()" class="btn btn-secondary btn-sm">📸 Download Grafik</button>
            <?php if (roleCanManage('kurva_s')): ?>
            <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=create&id_proyek=<?= $id_proyek ?>" class="btn btn-primary btn-sm">+ Tambah Data</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="chart-container" style="height: 400px;">
            <canvas id="kurvaSChart"></canvas>
        </div>
    </div>
</div>

<!-- Tabel Data Progress Mingguan -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Data Progress Mingguan</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Minggu ke-</th>
                    <th>Target Rencana</th>
                    <th>Realisasi</th>
                    <th>Selisih</th>
                    <th>Periode</th>
                    <?php if (roleCanManage('kurva_s')): ?>
                    <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($progressList)): ?>
                <tr>
                    <td colspan="<?= roleCanManage('kurva_s') ? 6 : 5 ?>" class="text-center text-muted" style="padding: 40px;">
                        Belum ada data progress mingguan.
                        <?php if (roleCanManage('kurva_s')): ?>
                        <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=create&id_proyek=<?= $id_proyek ?>">Tambah sekarang</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($progressList as $p): 
                    $selisih = $p['realisasi'] - $p['target_rencana'];
                ?>
                <tr>
                    <td><strong><?= $p['minggu_ke'] ?></strong></td>
                    <td><?= number_format($p['target_rencana'], 1) ?>%</td>
                    <td><?= number_format($p['realisasi'], 1) ?>%</td>
                    <td class="<?= $selisih >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $selisih >= 0 ? '+' : '' ?><?= number_format($selisih, 1) ?>%
                    </td>
                    <td class="text-muted" style="font-size: 12px;">
                        <?= date('d/m/Y', strtotime($p['tanggal_mulai'])) ?> - 
                        <?= date('d/m/Y', strtotime($p['tanggal_selesai'])) ?>
                    </td>
                    <?php if (roleCanManage('kurva_s')): ?>
                    <td>
                        <div class="flex" style="gap: 6px;">
                            <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=kurva_s&action=delete&id=<?= $p['id'] ?>', 'minggu ke-<?= $p['minggu_ke'] ?>')" class="btn btn-danger btn-sm">Hapus</a>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
// Data untuk Chart.js
const kurvaData = <?= json_encode($kurvaData) ?>;

// Siapkan labels dan datasets
const labels = kurvaData.map(item => 'Minggu ' + item.minggu_ke);
const targetData = kurvaData.map(item => parseFloat(item.target_rencana));
const realisasiData = kurvaData.map(item => parseFloat(item.realisasi));
const selisihData = kurvaData.map(item => parseFloat(item.selisih));

// Buat chart
let kurvaChart;

function initChart() {
    const ctx = document.getElementById('kurvaSChart').getContext('2d');
    
    kurvaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Rencana (Target)',
                    data: targetData,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#3498db',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Realisasi (Aktual)',
                    data: realisasiData,
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#27ae60',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Selisih (Gap)',
                    data: selisihData,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    borderDash: [5, 5],
                    pointRadius: 3,
                    pointBackgroundColor: '#e74c3c',
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.raw;
                            if (context.dataset.label === 'Selisih (Gap)') {
                                return label + ': ' + (value >= 0 ? '+' : '') + value.toFixed(1) + '%';
                            }
                            return label + ': ' + value.toFixed(1) + '%';
                        }
                    }
                },
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 10,
                        font: { size: 12 }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Periode (Minggu)',
                        font: { weight: 'bold', size: 12 }
                    },
                    grid: { display: false }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Progress Kumulatif (%)',
                        font: { weight: 'bold', size: 12 }
                    },
                    min: 0,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: { color: '#ecf0f1' }
                },
                y1: {
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Selisih (%)',
                        font: { weight: 'bold', size: 12 }
                    },
                    grid: { display: false },
                    ticks: {
                        callback: function(value) {
                            return (value >= 0 ? '+' : '') + value + '%';
                        }
                    }
                }
            }
        }
    });
}

// Download chart as image
function exportChartAsImage() {
    const canvas = document.getElementById('kurvaSChart');
    const link = document.createElement('a');
    link.download = 'kurva_s_' + new Date().toISOString().slice(0, 19) + '.png';
    link.href = canvas.toDataURL();
    link.click();
}

// Initialize chart when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (kurvaData.length > 0) {
        initChart();
    } else {
        document.getElementById('kurvaSChart').getContext('2d');
        document.getElementById('kurvaSChart').getContext('2d').fillText('Belum ada data untuk ditampilkan', 50, 50);
    }
});

// Resize chart when window resizes
window.addEventListener('resize', function() {
    if (kurvaChart) {
        kurvaChart.resize();
    }
});
</script>

<style>
.text-success { color: #27ae60 !important; }
.text-danger { color: #e74c3c !important; }
.chart-container { position: relative; width: 100%; min-height: 400px; }
</style>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>