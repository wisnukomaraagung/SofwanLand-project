<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <span class="card-title"><?= isset($progress) ? 'Edit' : 'Tambah' ?> Progress Mingguan</span>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=kurva_s&action=<?= isset($progress) ? 'update&id=' . $progress['id'] : 'store' ?>">
            
            <?php if (!isset($progress)): ?>
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
            
            <div class="form-group">
                <label>Minggu ke- *</label>
                <input type="number" name="minggu_ke" required min="1" 
                       value="<?= $progress['minggu_ke'] ?? '' ?>"
                       placeholder="Contoh: 1, 2, 3...">
                <small class="text-muted">Urutan minggu pelaksanaan proyek</small>
            </div>
            
            <div class="form-group">
                <label>Target Rencana (%) *</label>
                <input type="number" name="target_rencana" step="0.1" min="0" max="100" required
                       value="<?= $progress['target_rencana'] ?? 0 ?>"
                       placeholder="0 - 100">
                <small class="text-muted">Progress rencana kumulatif (%)</small>
            </div>
            
            <div class="form-group">
                <label>Realisasi (%) *</label>
                <input type="number" name="realisasi" step="0.1" min="0" max="100" required
                       value="<?= $progress['realisasi'] ?? 0 ?>"
                       placeholder="0 - 100">
                <small class="text-muted">Progress realisasi aktual kumulatif (%)</small>
            </div>
            
            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" 
                       value="<?= $progress['tanggal_mulai'] ?? '' ?>">
            </div>
            
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" 
                       value="<?= $progress['tanggal_selesai'] ?? '' ?>">
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="<?= BASE_URL ?>/public/index.php?page=kurva_s<?= isset($progress) ? '&id_proyek=' . $progress['id_proyek'] : '' ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>