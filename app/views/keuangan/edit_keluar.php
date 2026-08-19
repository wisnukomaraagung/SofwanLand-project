<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header"><span class="card-title">Edit Keuangan Keluar</span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=updateKeluar&id=<?= $data['id'] ?>">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Jumlah (Rp) *</label>
                    <input type="number" name="jumlah" required step="any" value="<?= htmlspecialchars($data['jumlah']) ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($data['tanggal']) ?>">
                </div>
                <div class="form-group">
                    <label>Kategori *</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Gaji" <?= ($data['kategori'] === 'Gaji') ? 'selected' : '' ?>>Gaji</option>
                        <option value="Pembelian Material" <?= ($data['kategori'] === 'Pembelian Material') ? 'selected' : '' ?>>Pembelian Material</option>
                        <option value="Sewa Alat" <?= ($data['kategori'] === 'Sewa Alat') ? 'selected' : '' ?>>Sewa Alat</option>
                        <option value="Lainnya" <?= ($data['kategori'] === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                <div class="form-group form-full">
                    <label>Kepada / Tujuan</label>
                    <input type="text" name="sumber" value="<?= htmlspecialchars($data['sumber'] ?? '') ?>" placeholder="Contoh: Vendor, Mandor, Biaya">
                </div>
                <div class="form-group form-full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Opsional..."><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex" style="gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="background:#c0392b;">Simpan Perubahan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=keuangan&tab=keluar" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
