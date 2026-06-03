<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

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

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
