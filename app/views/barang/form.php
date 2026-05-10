<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width:500px">
    <div class="card-header"><span class="card-title"><?= $barang ? 'Edit' : 'Tambah' ?> Barang</span></div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=barang&action=<?= $barang ? 'update&id='.$barang['id'] : 'store' ?>">
            <div class="form-grid">
                <div class="form-group form-full">
                    <label>Nama Barang *</label>
                    <input type="text" name="nama_barang" required placeholder="Contoh: Semen Portland"
                           value="<?= htmlspecialchars($barang['nama_barang'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" placeholder="Sak / M3 / Buah"
                           value="<?= htmlspecialchars($barang['satuan'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Harga Satuan (Rp)</label>
                    <input type="number" name="harga_satuan" placeholder="0" step="500"
                           value="<?= $barang['harga_satuan'] ?? 0 ?>">
                </div>
                <?php if (!$barang): ?>
                <div class="form-group">
                    <label>Stok Awal</label>
                    <input type="number" name="stok" placeholder="0" value="0">
                </div>
                <?php endif; ?>
            </div>
            <div style="margin-top:20px;display:flex;gap:10px">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=barang" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
