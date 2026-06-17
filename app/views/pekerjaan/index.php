<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Filter Proyek -->
<div class="project-switch-bar">
    <div class="project-switch-info">
        <span>Proyek:</span>
        <strong><?= htmlspecialchars($proyek['nama_proyek'] ?? '-') ?></strong>
        <span class="text-muted" style="margin-left: 10px;">
            Progress: <?= $summary['progress_total'] ?>%
        </span>
    </div>
    <form class="project-switch-form" action="<?= BASE_URL ?>/public/index.php" method="get">
        <input type="hidden" name="page" value="pekerjaan">
        <select name="id_proyek" class="project-switch-select" onchange="this.form.submit()">
            <?php foreach ($allProyek as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == ($id_proyek ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_proyek']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Summary Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <div class="stat-label">Total Pekerjaan</div>
        <div class="stat-value"><?= $summary['total_pekerjaan'] ?></div>
        <div class="stat-trend">Item RAB</div>
        <div class="stat-icon">📋</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Selesai</div>
        <div class="stat-value" style="color: #27ae60;"><?= $summary['selesai'] ?></div>
        <div class="stat-trend">Pekerjaan</div>
        <div class="stat-icon">✓</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Dalam Proses</div>
        <div class="stat-value" style="color: #f39c12;"><?= $summary['dalam_proses'] ?></div>
        <div class="stat-trend">Pekerjaan</div>
        <div class="stat-icon">⚙️</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total RAB</div>
        <div class="stat-value small">Rp <?= number_format(($summary['total_nilai_rab'] ?? 0) / 1000000, 1) ?>M</div>
        <div class="stat-trend"><?= 'Rp ' . number_format($summary['total_nilai_rab'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-icon">💰</div>
    </div>
</div>

<!-- Progress Proyek Overall -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Progress Proyek Keseluruhan</span>
        <span class="text-muted"><?= $summary['progress_total'] ?>%</span>
    </div>
    <div class="card-body">
        <div class="progress-bar-wrap" style="height: 30px;">
            <div class="progress-bar-fill" data-width="<?= $summary['progress_total'] ?>" style="background: linear-gradient(90deg, #2c3e50, #3498db);"></div>
        </div>
    </div>
</div>

<!-- Tabel Pekerjaan -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Daftar Pekerjaan (RAB)</span>
        <?php if (roleCanManage('pekerjaan')): ?>
        <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=create&id_proyek=<?= $id_proyek ?>" class="btn btn-primary btn-sm">+ Tambah Pekerjaan</a>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table class="table" id="table-pekerjaan">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Pekerjaan</th>
                    <th>Bobot</th>
                    <th>Nilai Pekerjaan</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <?php if (roleCanManage('pekerjaan')): ?>
                    <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pekerjaanList)): ?>
                <tr>
                    <td colspan="<?= roleCanManage('pekerjaan') ? 7 : 6 ?>" class="text-center text-muted" style="padding: 40px;">
                        Belum ada data pekerjaan. 
                        <?php if (roleCanManage('pekerjaan')): ?>
                        <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=create&id_proyek=<?= $id_proyek ?>">Tambah sekarang</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($pekerjaanList as $i => $p): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($p['nama_pekerjaan']) ?></strong></td>
                    <td><?= number_format($p['bobot'], 2) ?>%</td>
                    <td class="font-mono">Rp <?= number_format($p['nilai_pekerjaan'], 0, ',', '.') ?></td>
                    <td style="min-width: 150px;">
                        <?php if (roleCanManage('pekerjaan')): ?>
                        <div class="flex" style="gap: 8px;">
                            <input type="range" class="progress-slider" data-id="<?= $p['id'] ?>" value="<?= $p['progress_pekerjaan'] ?>" min="0" max="100" step="1" style="flex: 1;">
                            <span class="progress-value" data-id="<?= $p['id'] ?>"><?= $p['progress_pekerjaan'] ?>%</span>
                        </div>
                        <?php else: ?>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" data-width="<?= $p['progress_pekerjaan'] ?>"></div>
                        </div>
                        <span class="progress-label"><?= $p['progress_pekerjaan'] ?>%</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?= $p['status_pekerjaan'] == 'selesai' ? 'selesai' : ($p['status_pekerjaan'] == 'dalam_proses' ? 'aktif' : 'pending') ?>">
                            <?= $p['status_pekerjaan'] == 'selesai' ? 'Selesai' : ($p['status_pekerjaan'] == 'dalam_proses' ? 'Dalam Proses' : 'Belum Mulai') ?>
                        </span>
                    </td>
                    <?php if (roleCanManage('pekerjaan')): ?>
                    <td>
                        <div class="flex" style="gap: 6px;">
                            <a href="<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=edit&id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=delete&id=<?= $p['id'] ?>', '<?= htmlspecialchars($p['nama_pekerjaan']) ?>')" class="btn btn-danger btn-sm">Hapus</a>
                        </div>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (roleCanManage('pekerjaan')): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Progress slider update via AJAX
    const sliders = document.querySelectorAll('.progress-slider');
    sliders.forEach(slider => {
        slider.addEventListener('change', function() {
            const id = this.dataset.id;
            const progress = this.value;
            const progressSpan = document.querySelector(`.progress-value[data-id="${id}"]`);
            
            // Update display immediately
            if (progressSpan) progressSpan.textContent = progress + '%';
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('id', id);
            formData.append('progress', progress);
            
            fetch('<?= BASE_URL ?>/public/index.php?page=pekerjaan&action=updateProgressAjax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update total progress bar if exists
                    const totalProgressBar = document.querySelector('.progress-bar-fill');
                    if (totalProgressBar && data.total_progress) {
                        totalProgressBar.style.width = data.total_progress + '%';
                        totalProgressBar.setAttribute('data-width', data.total_progress);
                        const totalProgressText = document.querySelector('.card-header .text-muted');
                        if (totalProgressText) totalProgressText.textContent = data.total_progress + '%';
                    }
                    showToast('Progress berhasil diperbarui', 'success');
                } else {
                    showToast('Gagal memperbarui progress', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan', 'error');
            });
        });
    });
});

function showToast(message, type) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; bottom: 20px; right: 20px; 
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'}; 
        color: white; padding: 12px 24px; 
        border-radius: 8px; z-index: 9999;
        animation: fadeInOut 3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
<style>
@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(20px); }
    15% { opacity: 1; transform: translateY(0); }
    85% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-20px); }
}
</style>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>