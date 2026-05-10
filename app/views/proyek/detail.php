<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- STAT CARDS PROYEK -->
<div class="detail-stats">
    <div class="stat-card">
        <div class="stat-label">Progress Terbaru</div>
        <div class="stat-value"><?= $proyek['progress_terbaru'] ?>%</div>
        <?php if ($proyek['progress_tgl']): ?>
        <div class="stat-trend">Per <?= date('d M Y', strtotime($proyek['progress_tgl'])) ?></div>
        <?php endif; ?>
        <div class="stat-icon">◈</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pekerja</div>
        <div class="stat-value"><?= $proyek['total_pekerja'] ?></div>
        <div class="stat-trend">Karyawan tercatat</div>
        <div class="stat-icon">◧</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Barang Digunakan</div>
        <div class="stat-value"><?= number_format($proyek['total_barang_keluar']) ?></div>
        <div class="stat-trend">Total unit keluar</div>
        <div class="stat-icon">◩</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small">Rp <?= number_format($proyek['total_biaya']/1000000, 1) ?>M</div>
        <div class="stat-trend">dari Rp <?= number_format($proyek['nilai_kontrak'], 0, ',', '.') ?> kontrak</div>
        <div class="stat-icon">◪</div>
    </div>
</div>

<div class="grid-2">
    <!-- INFO PROYEK -->
    <div class="card">
        <div class="card-header"><span class="card-title">Informasi Proyek</span>
            <span class="badge badge-<?= $proyek['status'] ?>"><?= ucfirst($proyek['status']) ?></span>
        </div>
        <div class="card-body">
            <div class="info-list">
                <div class="info-item">
                    <span class="info-key">Nama Proyek</span>
                    <span class="info-val fw-700"><?= htmlspecialchars($proyek['nama_proyek']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Lokasi</span>
                    <span class="info-val"><?= htmlspecialchars($proyek['lokasi']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Tanggal Mulai</span>
                    <span class="info-val"><?= $proyek['tanggal_mulai'] ? date('d M Y', strtotime($proyek['tanggal_mulai'])) : '-' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Tanggal Selesai</span>
                    <span class="info-val"><?= $proyek['tanggal_selesai'] ? date('d M Y', strtotime($proyek['tanggal_selesai'])) : '-' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Nilai Kontrak</span>
                    <span class="info-val fw-700">Rp <?= number_format($proyek['nilai_kontrak'], 0, ',', '.') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Total Pemasukan</span>
                    <span class="info-val">Rp <?= number_format($proyek['total_pemasukan'], 0, ',', '.') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Total Pengeluaran</span>
                    <span class="info-val">Rp <?= number_format($proyek['total_biaya'], 0, ',', '.') ?></span>
                </div>
                <?php if ($proyek['deskripsi']): ?>
                <div class="info-item">
                    <span class="info-key">Deskripsi</span>
                    <span class="info-val"><?= nl2br(htmlspecialchars($proyek['deskripsi'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PROGRESS HISTORY -->
    <div class="card">
        <div class="card-header"><span class="card-title">Riwayat Progress</span></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Tanggal</th><th>Progress</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($progressHistory)): ?>
                    <tr><td colspan="3" class="text-center text-muted" style="padding:24px">Belum ada riwayat progress</td></tr>
                    <?php else: ?>
                    <?php foreach ($progressHistory as $pr): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($pr['tanggal'])) ?></td>
                        <td>
                            <div class="flex">
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill" data-width="<?= $pr['persentase'] ?>"></div>
                                </div>
                                <span class="progress-label"><?= $pr['persentase'] ?>%</span>
                            </div>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($pr['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="grid-2 mt-4">
    <!-- BARANG KELUAR -->
    <div class="card">
        <div class="card-header"><span class="card-title">Barang Keluar</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Barang</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php if (empty($barangKeluar)): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada barang keluar</td></tr>
                    <?php else: ?>
                    <?php foreach ($barangKeluar as $bk): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($bk['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($bk['nama_barang']) ?></td>
                        <td><?= number_format($bk['jumlah']) ?> <?= $bk['satuan'] ?></td>
                        <td class="text-muted"><?= htmlspecialchars($bk['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KEUANGAN HISTORY -->
    <div class="card">
        <div class="card-header"><span class="card-title">Riwayat Keuangan</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Tipe</th><th class="text-right">Jumlah</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php if (empty($keuanganHistory)): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada data keuangan</td></tr>
                    <?php else: ?>
                    <?php foreach ($keuanganHistory as $lk): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                        <td><span class="badge <?= $lk['tipe'] === 'pemasukan' ? 'badge-selesai' : 'badge-aktif' ?>"><?= ucfirst($lk['tipe']) ?></span></td>
                        <td class="text-right font-mono" style="font-size:12px">Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                        <td class="text-muted"><?= htmlspecialchars($lk['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
