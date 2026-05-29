<?php require BASE_PATH . '/app/views/layouts/header.php';
$selectedProyek = $selectedProyek ?? null;
$canManage = roleCanManage('keuangan');
$exportUrl = '?page=keuangan&action=exportExcel' . ($selectedProyek ? '&id_proyek=' . (int) $selectedProyek : '');
?>

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

<?php if ($canManage): ?>
<div class="card mt-4">
    <div class="card-header"><span class="card-title">Input Transaksi</span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=store<?= $selectedProyek ? '&id_proyek=' . (int) $selectedProyek : '' ?>">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Proyek *</label>
                    <select name="id_proyek" required>
                        <option value="">-- Pilih Proyek --</option>
                        <?php foreach ($proyekList as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $selectedProyek === (int) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama_proyek']) ?>
                        </option>
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

<!-- FILTER PROYEK -->
<div class="pill-nav mt-4">
    <a href="?page=keuangan" class="pill-btn <?= $selectedProyek === null ? 'active' : '' ?>">Semua Proyek</a>
    <?php foreach ($proyekList as $p): ?>
    <a href="?page=keuangan&id_proyek=<?= (int) $p['id'] ?>" class="pill-btn <?= $selectedProyek === (int) $p['id'] ? 'active' : '' ?>">
        <?= htmlspecialchars($p['nama_proyek']) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- RIWAYAT PER PROYEK -->
<div class="d-flex justify-content-between align-items-center mt-4 mb-2" style="flex-wrap:wrap; gap:12px;">
    <h2 style="font-size:16px; font-weight:700; margin:0;">Riwayat Transaksi per Proyek</h2>
    <a href="<?= htmlspecialchars($exportUrl) ?>" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Excel</a>
</div>

<?php if (empty($riwayatPerProyek)): ?>
<div class="card">
    <div class="card-body text-center text-muted" style="padding:40px">Belum ada proyek terdaftar</div>
</div>
<?php else: ?>
    <?php foreach ($riwayatPerProyek as $group):
        $proyek = $group['proyek'];
        $transaksi = $group['transaksi'];
        $pemasukan = (float) ($proyek['pemasukan'] ?? 0);
        $pengeluaran = (float) ($proyek['pengeluaran'] ?? 0);
        $saldoProyek = $pemasukan - $pengeluaran;
        $filterParam = $selectedProyek ? '&id_proyek=' . (int) $selectedProyek : '';
    ?>
    <div class="card mt-3">
        <div class="card-header" style="flex-wrap:wrap; gap:12px;">
            <div>
                <span class="card-title"><?= htmlspecialchars($proyek['nama_proyek']) ?></span>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">
                    <?= (int) ($proyek['jumlah_transaksi'] ?? 0) ?> transaksi
                </div>
            </div>
            <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:12px;">
                <span><span class="text-muted">Pemasukan:</span> <strong style="color:#27ae60">Rp <?= number_format($pemasukan, 0, ',', '.') ?></strong></span>
                <span><span class="text-muted">Pengeluaran:</span> <strong style="color:#c0392b">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></strong></span>
                <span><span class="text-muted">Saldo:</span> <strong>Rp <?= number_format($saldoProyek, 0, ',', '.') ?></strong></span>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th class="text-right">Jumlah</th>
                        <th>Keterangan</th>
                        <?php if ($canManage): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transaksi)): ?>
                    <tr>
                        <td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted" style="padding:24px">
                            Belum ada transaksi untuk proyek ini
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($transaksi as $i => $lk): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                        <td>
                            <span class="badge <?= $lk['tipe'] === 'pemasukan' ? 'badge-selesai' : 'badge-aktif' ?>">
                                <?= ucfirst($lk['tipe']) ?>
                            </span>
                        </td>
                        <td class="text-right font-mono fw-700" style="font-size:13px">
                            <?= $lk['tipe'] === 'pemasukan' ? '+' : '-' ?> Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($lk['keterangan'] ?? '-') ?></td>
                        <?php if ($canManage): ?>
                        <td>
                            <a href="javascript:void(0)"
                               onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=delete&id=<?= (int) $lk['id'] ?><?= $filterParam ?>','transaksi ini')"
                               class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
