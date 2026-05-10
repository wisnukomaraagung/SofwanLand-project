<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Proyek</th>
                    <th>Lokasi</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th class="text-right">Nilai Kontrak</th>
                    <th class="text-right">Total Biaya</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proyekList)): ?>
                <tr><td colspan="9" class="empty-state"><div class="empty-state-icon">◫</div><p>Belum ada proyek</p></td></tr>
                <?php else: ?>
                <?php foreach ($proyekList as $i => $p): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($p['nama_proyek']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($p['lokasi']) ?></td>
                    <td class="text-muted" style="font-size:12px">
                        <?= $p['tanggal_mulai'] ? date('d M Y', strtotime($p['tanggal_mulai'])) : '-' ?>
                        <br>→ <?= $p['tanggal_selesai'] ? date('d M Y', strtotime($p['tanggal_selesai'])) : '-' ?>
                    </td>
                    <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                    <td>
                        <div class="flex">
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" data-width="<?= $p['progress_terbaru'] ?>"></div>
                            </div>
                            <span class="progress-label"><?= $p['progress_terbaru'] ?>%</span>
                        </div>
                    </td>
                    <td class="text-right" style="font-size:13px">Rp <?= number_format($p['nilai_kontrak'], 0, ',', '.') ?></td>
                    <td class="text-right" style="font-size:13px">Rp <?= number_format($p['total_biaya'], 0, ',', '.') ?></td>
                    <td>
                        <div class="flex">
                            <a href="<?= BASE_URL ?>/public/index.php?page=proyek&action=detail&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Detail</a>
                            <a href="javascript:void(0)"
                               onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=proyek&action=delete&id=<?= $p['id'] ?>', '<?= htmlspecialchars($p['nama_proyek'], ENT_QUOTES) ?>')"
                               class="btn btn-danger btn-sm">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
