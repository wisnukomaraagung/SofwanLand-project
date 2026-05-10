<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width:720px">
    <div class="card-header"><span class="card-title"><?= $proyek ? 'Edit' : 'Tambah' ?> Proyek</span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=proyek&action=<?= $proyek ? 'update&id=' . $proyek['id'] : 'store' ?>">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Nama Proyek *</label>
                    <input type="text" name="nama_proyek" required placeholder="Contoh: Pembangunan Gedung A"
                           value="<?= htmlspecialchars($proyek['nama_proyek'] ?? '') ?>">
                </div>
                <div class="form-group form-full">
                    <label>Lokasi *</label>
                    <input type="text" name="lokasi" required placeholder="Alamat lengkap proyek"
                           value="<?= htmlspecialchars($proyek['lokasi'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="<?= $proyek['tanggal_mulai'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="<?= $proyek['tanggal_selesai'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Nilai Kontrak (Rp)</label>
                    <input type="number" name="nilai_kontrak" placeholder="0" step="1000"
                           value="<?= $proyek['nilai_kontrak'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['aktif', 'selesai', 'pending'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($proyek['status'] ?? 'aktif') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group form-full">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" placeholder="Keterangan singkat proyek..."><?= htmlspecialchars($proyek['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">💾 Simpan Proyek</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=proyek" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
