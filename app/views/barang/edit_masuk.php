<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width:600px; margin: 0 auto;">
    <div class="card-header"><span class="card-title"><?= $pageTitle ?></span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=barang&action=updateMasuk&id=<?= $masuk['id'] ?>">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Barang *</label>
                    <select name="id_barang" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($barangList as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $b['id'] == $masuk['id_barang'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= $b['stok'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jumlah *</label>
                    <input type="number" name="jumlah" min="1" required placeholder="0" value="<?= $masuk['jumlah'] ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="date" name="tanggal" required value="<?= $masuk['tanggal'] ?>">
                </div>
                
                <div class="form-group">
                    <label>Harga Satuan (Rp)</label>
                    <input type="number" name="harga_satuan" placeholder="0" value="<?= $masuk['harga_satuan'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>Supplier / Toko</label>
                    <input type="text" name="supplier" placeholder="Contoh: Toko Besi Jaya" value="<?= htmlspecialchars($masuk['supplier'] ?? '') ?>">
                </div>
                
                <div class="form-group form-full">
                    <label>No. Kuitansi</label>
                    <input type="text" name="no_kuitansi" placeholder="INV-001 (opsional)" value="<?= htmlspecialchars($masuk['no_kuitansi'] ?? '') ?>">
                </div>

                <div class="form-group form-full">
                    <label>Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Catatan..."><?= htmlspecialchars($masuk['keterangan'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div style="margin-top:20px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=barang&tab=masuk" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
