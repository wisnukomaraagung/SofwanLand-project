<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- SUMMARY CARDS -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card">
        <div class="stat-label">Total Pemasukan</div>
        <div class="stat-value small">Rp <?= number_format(($summary['total_pemasukan'] ?? 0)/1000000, 1) ?>M</div>
        <div class="stat-trend"><?= 'Rp ' . number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-icon">↑</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small">Rp <?= number_format(($summary['total_pengeluaran'] ?? 0)/1000000, 1) ?>M</div>
        <div class="stat-trend"><?= 'Rp ' . number_format($summary['total_pengeluaran'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-icon">↓</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Saldo</div>
        <?php $saldo = ($summary['total_pemasukan'] ?? 0) - ($summary['total_pengeluaran'] ?? 0); ?>
        <div class="stat-value small">Rp <?= number_format(abs($saldo)/1000000, 1) ?>M</div>
        <div class="stat-trend"><?= $saldo >= 0 ? 'Surplus' : 'Defisit' ?></div>
        <div class="stat-icon">⊟</div>
    </div>
</div>

<div class="grid-2">
    <!-- FORM INPUT -->
    <?php if (roleCanManage('keuangan')): ?>
    <div class="card">
        <div class="card-header"><span class="card-title">Input Transaksi</span></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=store">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Proyek *</label>
                        <select name="id_proyek" required>
                            <option value="">-- Pilih Proyek --</option>
                            <?php foreach ($proyekList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipe</label>
                        <select name="tipe">
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran" selected>Pengeluaran</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group form-full">
                        <label>Jumlah (Rp) *</label>
                        <input type="number" name="jumlah" required min="1" step="1000" placeholder="0">
                    </div>
                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px">+ Catat Transaksi</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- SUMMARY PER PROYEK -->
    <div class="card">
        <div class="card-header"><span class="card-title">Ringkasan per Proyek</span></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Proyek</th><th class="text-right">Pemasukan</th><th class="text-right">Pengeluaran</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($summaryPerProyek as $sp): ?>
                    <tr>
                        <td style="font-size:13px"><?= htmlspecialchars($sp['nama_proyek']) ?></td>
                        <td class="text-right font-mono" style="font-size:12px">Rp <?= number_format($sp['pemasukan'],0,',','.') ?></td>
                        <td class="text-right font-mono" style="font-size:12px">Rp <?= number_format($sp['pengeluaran'],0,',','.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DAFTAR TRANSAKSI -->
<div class="card mt-4">
    <div class="card-header">
        <span class="card-title">Daftar Transaksi</span>
        <a href="?page=keuangan&action=exportExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Excel</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Tanggal</th><th>Proyek</th><th>Tipe</th><th class="text-right">Jumlah</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($keuanganList)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:40px">Belum ada data transaksi</td></tr>
                <?php else: ?>
                <?php foreach ($keuanganList as $i => $lk): ?>
                <tr>
                    <td class="text-muted"><?= $i+1 ?></td>
                    <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                    <td style="font-size:13px"><?= htmlspecialchars($lk['nama_proyek']) ?></td>
                    <td>
                        <span class="badge <?= $lk['tipe'] === 'pemasukan' ? 'badge-selesai' : 'badge-aktif' ?>">
                            <?= ucfirst($lk['tipe']) ?>
                        </span>
                    </td>
                    <td class="text-right font-mono fw-700" style="font-size:13px">
                        <?= $lk['tipe'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($lk['jumlah'],0,',','.') ?>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($lk['keterangan'] ?? '-') ?></td>
                    <td>
                        <?php if (roleCanManage('keuangan')): ?>
                        <a href="javascript:void(0)"
                           onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=delete&id=<?= $lk['id'] ?>','transaksi ini')"
                           class="btn btn-danger btn-sm">Hapus</a>
                        <?php else: ?>
                        <span class="text-muted" style="font-size:12px;">Readonly</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
