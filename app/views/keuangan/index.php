<?php require BASE_PATH . '/app/views/layouts/header.php';
$globalProjectId = $_SESSION['selected_project_id'] ?? null;
?>

<?php if (!$globalProjectId): ?>
<div class="card text-center" style="padding: 40px; margin: 20px auto; max-width: 600px;">
    <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
    <h2>Belum Ada Proyek yang Dipilih</h2>
    <p class="text-muted" style="margin-top: 10px; margin-bottom: 20px;">Silakan pilih proyek terlebih dahulu pada Dashboard untuk melihat data keuangan.</p>
    <a href="<?= BASE_URL ?>/public/index.php?page=dashboard" class="btn btn-primary" style="text-decoration: none;">Pilih Proyek di Dashboard</a>
</div>
<?php else:
$canManage = roleCanManage('keuangan');
$activeTab = $_GET['tab'] ?? 'masuk';
if (!$canManage) {
    $activeTab = 'laporan';
}
if (!in_array($activeTab, ['masuk', 'keluar', 'laporan'], true)) $activeTab = 'masuk';
?>

<!-- PILL NAV -->
<div class="pill-nav">
    <?php if ($canManage): ?>
    <a href="?page=keuangan&tab=masuk"   class="pill-btn <?= $activeTab === 'masuk'   ? 'active' : '' ?>">↑ Keuangan Masuk</a>
    <a href="?page=keuangan&tab=keluar"  class="pill-btn <?= $activeTab === 'keluar'  ? 'active' : '' ?>">↓ Keuangan Keluar</a>
    <?php endif; ?>
    <a href="?page=keuangan&tab=laporan" class="pill-btn <?= $activeTab === 'laporan' ? 'active' : '' ?>">≡ Laporan Keuangan</a>
</div>

<!-- ═══════════════════════════════════════════════ TAB MASUK -->
<?php if ($activeTab === 'masuk'): ?>

<div class="stats-grid" style="grid-template-columns:1fr">
    <div class="stat-card" style="background:linear-gradient(135deg,#e8f5e9,#c8e6c9)">
        <div class="stat-label">Total Pemasukan</div>
        <div class="stat-value small" style="color:#2e7d32">Rp <?= number_format(($summary['total_pemasukan'] ?? 0)/1000000, 1) ?>M</div>
        <div class="stat-trend">Rp <?= number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-icon" style="color:#2e7d32">↑</div>
    </div>
</div>

<?php if ($canManage): ?>
<div class="split-layout">
<?php else: ?>
<div>
<?php endif; ?>

    <?php if ($canManage): ?>
    <!-- FORM INPUT MASUK -->
    <div class="card">
        <div class="card-header"><span class="card-title">↑ CATAT KEUANGAN MASUK</span></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=storeMasuk">
                <input type="hidden" name="id_proyek" value="<?= (int) $globalProjectId ?>">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Jumlah (Rp) *</label>
                        <input type="number" name="jumlah" required step="any" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Dari / Sumber</label>
                        <input type="text" name="sumber" placeholder="Contoh: Klien, Termin 1">
                    </div>
                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px; width:100%; justify-content:center; padding:12px;">+ CATAT PEMASUKAN</button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div style="display:none"></div>
    <?php endif; ?>

    <!-- TABEL RIWAYAT MASUK -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Riwayat Pemasukan</span>
            <a href="?page=keuangan&action=exportMasukExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Excel</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th class="text-right">Jumlah</th>
                        <th>Sumber</th>
                        <th>Keterangan</th>
                        <?php if ($canManage): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($masukList)): ?>
                    <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted" style="padding:32px">Belum ada riwayat pemasukan</td></tr>
                    <?php else: ?>
                    <?php foreach ($masukList as $i => $lk): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                        <td class="text-right fw-700" style="color:#27ae60">+ Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                        <td class="text-muted"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                        <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                        <?php if ($canManage): ?>
                        <td>
                            <a href="javascript:void(0)"
                               onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=deleteMasuk&id=<?= (int)$lk['id'] ?>','pemasukan ini')"
                               class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════ TAB KELUAR -->
<?php elseif ($activeTab === 'keluar'): ?>

<div class="stats-grid" style="grid-template-columns:1fr">
    <div class="stat-card" style="background:linear-gradient(135deg,#fce4ec,#f8bbd9)">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small" style="color:#c62828">Rp <?= number_format(($summary['total_pengeluaran'] ?? 0)/1000000, 1) ?>M</div>
        <div class="stat-trend">Rp <?= number_format($summary['total_pengeluaran'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-icon" style="color:#c62828">↓</div>
    </div>
</div>

<?php if ($canManage): ?>
<div class="split-layout">
<?php else: ?>
<div>
<?php endif; ?>

    <?php if ($canManage): ?>
    <!-- FORM INPUT KELUAR -->
    <div class="card">
        <div class="card-header"><span class="card-title">↓ CATAT KEUANGAN KELUAR</span></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=keuangan&action=storeKeluar">
                <input type="hidden" name="id_proyek" value="<?= (int) $globalProjectId ?>">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Jumlah (Rp) *</label>
                        <input type="number" name="jumlah" required step="any" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Gaji">Gaji</option>
                            <option value="Pembelian Material">Pembelian Material</option>
                            <option value="Sewa Alat">Sewa Alat</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Kepada / Tujuan</label>
                        <input type="text" name="sumber" placeholder="Contoh: Vendor, Mandor, Biaya">
                    </div>
                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px; width:100%; justify-content:center; padding:12px; background:#c0392b;">+ CATAT PENGELUARAN</button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div style="display:none"></div>
    <?php endif; ?>

    <!-- WRAPPER UNTUK TABEL PER KATEGORI AGAR TIDAK MERUSAK SPLIT-LAYOUT -->
    <div style="flex: 1; min-width: 0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0; font-size:18px; color:#2c3e50;">Riwayat Pengeluaran Per Kategori</h3>
            <a href="?page=keuangan&action=exportKeluarExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Export Semua Excel</a>
        </div>

        <?php
        $categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
        $groupedKeluar = [];
        foreach ($categories as $c) {
            $groupedKeluar[$c] = [];
        }
        foreach ($keluarList as $lk) {
            $cat = $lk['kategori'] ?: 'Lainnya';
            if (!in_array($cat, $categories)) {
                $groupedKeluar['Lainnya'][] = $lk;
            } else {
                $groupedKeluar[$cat][] = $lk;
            }
        }
        ?>

        <?php foreach ($groupedKeluar as $categoryName => $items): ?>
        <div class="card mb-3" style="margin-top: 0;">
            <div class="card-header" style="border-left: 4px solid <?php
                $bg = '#7f8c8d';
                if ($categoryName === 'Gaji') $bg = '#3498db';
                elseif ($categoryName === 'Pembelian Material') $bg = '#e67e22';
                elseif ($categoryName === 'Sewa Alat') $bg = '#1abc9c';
                echo $bg;
            ?>;">
                <span class="card-title">
                    <?php
                    $icon = '📁';
                    if ($categoryName === 'Gaji') $icon = '👤';
                    elseif ($categoryName === 'Pembelian Material') $icon = '🧱';
                    elseif ($categoryName === 'Sewa Alat') $icon = '⚙️';
                    echo $icon . ' ' . $categoryName;
                    ?>
                </span>
                <span class="badge" style="background:<?php
                    $bg = '#7f8c8d';
                    if ($categoryName === 'Gaji') $bg = '#3498db';
                    elseif ($categoryName === 'Pembelian Material') $bg = '#e67e22';
                    elseif ($categoryName === 'Sewa Alat') $bg = '#1abc9c';
                    echo $bg;
                ?>; color:#fff; font-size:11px; font-weight:700;">
                    Total: Rp <?= number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') ?>
                </span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th class="text-right" style="width: 180px;">Jumlah</th>
                            <th>Kepada / Tujuan</th>
                            <th>Keterangan</th>
                            <?php if ($canManage): ?><th style="width: 80px;">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted" style="padding:16px;">Tidak ada transaksi untuk kategori ini</td></tr>
                        <?php else: ?>
                        <?php foreach ($items as $i => $lk): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                            <td class="text-right fw-700" style="color:#c0392b">- Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                            <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                            <?php if ($canManage): ?>
                            <td>
                                <a href="javascript:void(0)"
                                   onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=deleteKeluar&id=<?= (int)$lk['id'] ?>','pengeluaran ini')"
                                   class="btn btn-danger btn-sm">Hapus</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ TAB LAPORAN -->
<?php elseif ($activeTab === 'laporan'): ?>

<!-- TOMBOL EXPORT -->
<div class="d-flex justify-content-end mb-3">
    <a href="?page=keuangan&action=exportLaporanExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Export Excel Laporan</a>
</div>


<?php
$categories = ['Gaji', 'Pembelian Material', 'Sewa Alat', 'Lainnya'];
$groupedKeluar = [];
foreach ($categories as $c) {
    $groupedKeluar[$c] = [];
}
foreach ($keluarList as $lk) {
    $cat = $lk['kategori'] ?: 'Lainnya';
    if (!in_array($cat, $categories)) {
        $groupedKeluar['Lainnya'][] = $lk;
    } else {
        $groupedKeluar[$cat][] = $lk;
    }
}
?>

<?php foreach ($groupedKeluar as $categoryName => $items): ?>
<div class="card mt-3">
    <div class="card-header" style="background:#f8f9fa; border-bottom:2px solid #bdc3c7; display:flex; justify-content:space-between; align-items:center;">
        <span class="card-title" style="font-weight:700;">📁 <?= strtoupper($categoryName) ?></span>
        <span class="badge" style="font-size:14px; padding:6px 12px; background:#c0392b; color:#fff; margin-left:auto;">
            Total: Rp <?= number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') ?>
        </span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th><th>Tanggal</th><th>Jumlah</th><th>Kepada / Tujuan</th><th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="5" class="text-center text-muted" style="padding:24px">Tidak ada transaksi untuk kategori ini</td></tr>
                <?php else: ?>
                <?php foreach ($items as $k => $lk): ?>
                <tr>
                    <td class="text-muted"><?= $k + 1 ?></td>
                    <td class="text-muted" style="font-size:12px"><?= date('d/m/Y', strtotime($lk['tanggal'])) ?></td>
                    <td class="fw-700">Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                    <td class="text-muted"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                    <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
