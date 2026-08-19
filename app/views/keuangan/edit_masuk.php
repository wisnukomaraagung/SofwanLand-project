<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header"><span class="card-title">Edit Keuangan Masuk</span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=updateMasuk&id=<?= $data['id'] ?>">
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
                    <label>Dari / Sumber</label>
                    <input type="text" name="sumber" value="<?= htmlspecialchars($data['sumber'] ?? '') ?>" placeholder="Contoh: Klien, Termin 1">
                </div>
                <div class="form-group form-full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="3" placeholder="Opsional..."><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex" style="gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=keuangan&tab=masuk" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
