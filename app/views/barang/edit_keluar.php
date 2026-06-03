<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width:650px; margin: 0 auto;">
    <div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=barang&action=updateKeluar&id=<?= $keluar['id'] ?>" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Barang *</label>
                    <select name="id_barang" required>
                        <?php foreach ($barangList as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $b['id'] == $keluar['id_barang'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php $selectedProjectId = $_SESSION['selected_project_id'] ?? null; ?>
                <?php if ($selectedProjectId): ?>
                <div class="form-group form-full">
                    <label>Proyek</label>
                    <input type="hidden" name="id_proyek" value="<?= $selectedProjectId ?>">
                    <div class="project-readonly" title="Proyek aktif — otomatis terpilih"><?= htmlspecialchars($_SESSION['selected_project_name'] ?? '') ?></div>
                </div>
                <?php else: ?>
                <div class="form-group form-full">
                    <label>Proyek *</label>
                    <select name="id_proyek" required>
                        <?php foreach ($proyekList as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $keluar['id_proyek'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama_proyek']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="number" name="jumlah" min="1" required placeholder="0" value="<?= $keluar['jumlah'] ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="date" name="tanggal" required value="<?= $keluar['tanggal'] ?>">
                </div>

                <div class="form-group form-full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan..."><?= htmlspecialchars($keluar['keterangan'] ?? '') ?></textarea>
                </div>

                <div class="form-group form-full">
                    <label>Foto Bukti</label>
                    <input type="file" name="foto_bukti" accept="image/*" style="padding: 8px; border: 1.5px solid var(--border); border-radius: 14px; width: 100%; font-family:inherit;">
                    <?php if (!empty($keluar['foto_bukti'])): ?>
                        <div style="margin-top:10px;">
                            <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($keluar['foto_bukti']) ?>" style="max-height:120px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid var(--border);"><br>
                            <a href="<?= htmlspecialchars(BarangController::buktiViewUrl($keluar['foto_bukti'], 'keluar', BASE_URL . '/public/index.php?page=barang&action=editKeluar&id=' . (int)$keluar['id'])) ?>" class="btn btn-secondary btn-sm" style="font-size:11px; padding: 4px 8px; margin-top:5px; display:inline-block; text-decoration:none; color:var(--text-dark);">📄 Lihat Bukti Penuh</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top:20px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=barang&tab=keluar" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
