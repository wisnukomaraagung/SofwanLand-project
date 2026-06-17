<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title"><?= isset($pekerjaan) ? 'Edit' : 'Tambah' ?> Pekerjaan</span>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=<?= isset($pekerjaan) ? 'update&id=' . $pekerjaan['id'] : 'store' ?>">
            
            <div class="form-grid">
                <?php if (!isset($pekerjaan)): ?>
                <div class="form-group form-full">
                    <label>Proyek *</label>
                    <select name="id_proyek" required>
                        <option value="">-- Pilih Proyek --</option>
                        <?php foreach ($allProyek as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (($id_proyek ?? 0) == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nama_proyek']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="form-group form-full">
                    <label>Nama Pekerjaan *</label>
                    <input type="text" name="nama_pekerjaan" required 
                           value="<?= htmlspecialchars($pekerjaan['nama_pekerjaan'] ?? '') ?>"
                           placeholder="Contoh: Pekerjaan Pondasi, Pekerjaan Dinding, dll">
                </div>
                
                <div class="form-group">
                    <label>Bobot Pekerjaan (%) *</label>
                    <input type="number" name="bobot" step="0.01" min="0" max="100" required
                           value="<?= $pekerjaan['bobot'] ?? 0 ?>"
                           placeholder="0 - 100">
                    <small class="text-muted">Persentase bobot terhadap total proyek</small>
                </div>
                
                <div class="form-group">
                    <label>Nilai Pekerjaan (Rp) *</label>
                    <input type="text" name="nilai_pekerjaan" required
                           value="<?= isset($pekerjaan) ? number_format($pekerjaan['nilai_pekerjaan'], 0, ',', '.') : '' ?>"
                           placeholder="0"
                           onkeyup="formatRupiah(this)"
                           onblur="formatRupiah(this)">
                    <small class="text-muted">Nilai anggaran untuk pekerjaan ini</small>
                </div>
                
                <div class="form-group">
                    <label>Progress Pekerjaan (%)</label>
                    <input type="number" name="progress_pekerjaan" step="1" min="0" max="100"
                           value="<?= $pekerjaan['progress_pekerjaan'] ?? 0 ?>">
                </div>
                
                <div class="form-group">
                    <label>Status Pekerjaan</label>
                    <select name="status_pekerjaan">
                        <option value="belum_mulai" <?= (($pekerjaan['status_pekerjaan'] ?? '') == 'belum_mulai') ? 'selected' : '' ?>>Belum Mulai</option>
                        <option value="dalam_proses" <?= (($pekerjaan['status_pekerjaan'] ?? '') == 'dalam_proses') ? 'selected' : '' ?>>Dalam Proses</option>
                        <option value="selesai" <?= (($pekerjaan['status_pekerjaan'] ?? '') == 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan<?= isset($pekerjaan) ? '&id_proyek=' . $pekerjaan['id_proyek'] : '' ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function formatRupiah(element) {
    let value = element.value.replace(/[^0-9]/g, '');
    if (value) {
        element.value = new Intl.NumberFormat('id-ID').format(value);
    }
}
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>