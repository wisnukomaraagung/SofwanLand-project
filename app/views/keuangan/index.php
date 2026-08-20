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
if (!in_array($activeTab, ['masuk', 'keluar', 'laporan'], true)) $activeTab = 'masuk';
?>

<!-- PILL NAV -->
<div class="pill-nav">
    <a href="?page=keuangan&tab=masuk"   class="pill-btn <?= $activeTab === 'masuk'   ? 'active' : '' ?>">↑ Keuangan Masuk</a>
    <a href="?page=keuangan&tab=keluar"  class="pill-btn <?= $activeTab === 'keluar'  ? 'active' : '' ?>">↓ Keuangan Keluar</a>
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
    <div class="card" style="border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
            <span class="card-title" style="font-weight: 600; color: #334155; font-size: 14px; letter-spacing: 0.2px;">Riwayat Pemasukan</span>
            <a href="?page=keuangan&action=exportMasukExcel" class="btn btn-secondary btn-sm" style="background: #ffffff; color: #334155; border: 1px solid #cbd5e1; text-decoration:none;">↓ Export Excel</a>
        </div>
        <div class="table-wrap" style="padding: 0; margin: 0;">
            <table style="margin: 0; border: none;">
                <thead style="background: #ffffff;">
                    <tr>
                        <th style="width: 50px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">#</th>
                        <th style="width: 120px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Tanggal</th>
                        <th class="text-right" style="width: 150px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Jumlah</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Sumber</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Keterangan</th>
                        <?php if ($canManage): ?><th style="width: 120px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($masukList)): ?>
                    <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted" style="padding: 30px; font-size: 13px; background: #ffffff;">Belum ada riwayat pemasukan</td></tr>
                    <?php else: ?>
                    <?php foreach ($masukList as $i => $lk): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                        <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= $i + 1 ?></td>
                        <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                        <td class="text-right fw-700" style="color:#27ae60; font-size: 13px; padding: 12px 20px;">+ Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                        <td style="font-size: 13px; color: #475569; padding: 12px 20px;"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                        <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                        <?php if ($canManage): ?>
                        <td style="padding: 12px 20px;">
                            <div class="flex">
                                <a href="<?= BASE_URL ?>/public/index.php?page=keuangan&action=editMasuk&id=<?= (int)$lk['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=deleteMasuk&id=<?= (int)$lk['id'] ?>','pemasukan ini')" class="btn btn-danger btn-sm">Hapus</a>
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
        <div class="card mb-3" style="margin-top: 0; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
                <span style="font-weight: 600; color: #334155; font-size: 14px; display: flex; align-items: center; gap: 10px; letter-spacing: 0.2px;">
                    <?php
                    $dotColor = '#94a3b8';
                    if ($categoryName === 'Gaji') $dotColor = '#3b82f6';
                    elseif ($categoryName === 'Pembelian Material') $dotColor = '#f59e0b';
                    elseif ($categoryName === 'Sewa Alat') $dotColor = '#10b981';
                    ?>
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: <?= $dotColor ?>;"></span>
                    <?= strtoupper($categoryName) ?>
                </span>
                <span style="background: #ffffff; color: #475569; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; border: 1px solid #e2e8f0;">
                    Total: Rp <?= number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') ?>
                </span>
            </div>
            <div class="table-wrap" style="padding: 0; margin: 0;">
                <table style="margin: 0; border: none;">
                    <thead style="background: #ffffff;">
                        <tr>
                            <th style="width: 50px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">#</th>
                            <th style="width: 120px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Tanggal</th>
                            <th class="text-right" style="width: 150px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Jumlah</th>
                            <th style="border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Kepada / Tujuan</th>
                            <th style="border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Keterangan</th>
                            <?php if ($canManage): ?><th style="width: 120px; border-top: none; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="<?= $canManage ? 6 : 5 ?>" class="text-center text-muted" style="padding: 30px; font-size: 13px; background: #ffffff;">Tidak ada transaksi untuk kategori ini</td></tr>
                        <?php else: ?>
                        <?php foreach ($items as $i => $lk): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                            <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= $i + 1 ?></td>
                            <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= date('d M Y', strtotime($lk['tanggal'])) ?></td>
                            <td class="text-right" style="color: #334155; font-weight: 500; font-size: 13px; padding: 12px 20px;">Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                            <td style="font-size: 13px; color: #475569; padding: 12px 20px;"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                            <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                            <?php if ($canManage): ?>
                            <td style="padding: 12px 20px;">
                                <div class="flex">
                                    <a href="<?= BASE_URL ?>/public/index.php?page=keuangan&action=editKeluar&id=<?= (int)$lk['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=keuangan&action=deleteKeluar&id=<?= (int)$lk['id'] ?>','pengeluaran ini')" class="btn btn-danger btn-sm">Hapus</a>
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
foreach ($categories as $c) $groupedKeluar[$c] = [];
foreach ($keluarList as $lk) {
    $cat = $lk['kategori'] ?: 'Lainnya';
    $groupedKeluar[in_array($cat, $categories) ? $cat : 'Lainnya'][] = $lk;
}
$totalPemasukan   = $summary['total_pemasukan'] ?? 0;
$totalPengeluaran = $summary['total_pengeluaran'] ?? 0;
$saldo            = $totalPemasukan - $totalPengeluaran;
?>

<!-- RINGKASAN SALDO -->
<div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom: 20px;">
    <div class="stat-card" style="background: linear-gradient(135deg,#e8f5e9,#c8e6c9);">
        <div class="stat-label">Total Pemasukan</div>
        <div class="stat-value small" style="color:#2e7d32; font-size:20px;">Rp <?= number_format($totalPemasukan/1000000,1) ?>M</div>
        <div class="stat-trend">Rp <?= number_format($totalPemasukan,0,',','.') ?></div>
        <div class="stat-icon" style="color:#2e7d32;">↑</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg,#fce4ec,#f8bbd9);">
        <div class="stat-label">Total Pengeluaran</div>
        <div class="stat-value small" style="color:#c62828; font-size:20px;">Rp <?= number_format($totalPengeluaran/1000000,1) ?>M</div>
        <div class="stat-trend">Rp <?= number_format($totalPengeluaran,0,',','.') ?></div>
        <div class="stat-icon" style="color:#c62828;">↓</div>
    </div>
    <div class="stat-card" style="background: <?= $saldo >= 0 ? 'linear-gradient(135deg,#e3f2fd,#bbdefb)' : 'linear-gradient(135deg,#fff3e0,#ffe0b2)' ?>;">
        <div class="stat-label">Saldo / Laba Bruto</div>
        <div class="stat-value small" style="color:<?= $saldo >= 0 ? '#1565c0' : '#e65100' ?>; font-size:20px;">Rp <?= number_format(abs($saldo)/1000000,1) ?>M</div>
        <div class="stat-trend" style="color:<?= $saldo >= 0 ? '#1565c0' : '#e65100' ?>"><?= $saldo >= 0 ? '+ Surplus' : '- Defisit' ?></div>
        <div class="stat-icon" style="color:<?= $saldo >= 0 ? '#1565c0' : '#e65100' ?>;"><?= $saldo >= 0 ? '◎' : '⚠' ?></div>
    </div>
</div>

<!-- ── BAGIAN PEMASUKAN ── -->
<div class="card mb-3" style="margin-bottom: 24px; border: 1px solid #c8e6c9; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
    <div class="card-header" style="background: #e8f5e9; border-bottom: 1px solid #c8e6c9; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
        <span style="font-weight: 700; color: #2e7d32; font-size: 14px; display: flex; align-items: center; gap: 8px;">
            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#2e7d32;"></span>
            ↑ RIWAYAT PEMASUKAN
        </span>
        <span style="background:#ffffff; color:#2e7d32; font-size:12px; font-weight:600; padding:4px 14px; border-radius:20px; border:1px solid #a5d6a7;">
            Total: Rp <?= number_format($totalPemasukan, 0, ',', '.') ?>
        </span>
    </div>
    <div class="table-wrap" style="padding: 0; margin: 0;">
        <table style="margin: 0; border: none;">
            <thead style="background: #f1f8f1;">
                <tr>
                    <th style="width:50px; border-top:none; color:#388e3c; font-weight:600; font-size:11px; text-transform:uppercase; padding:12px 20px;">#</th>
                    <th style="width:120px; border-top:none; color:#388e3c; font-weight:600; font-size:11px; text-transform:uppercase; padding:12px 20px;">Tanggal</th>
                    <th class="text-right" style="width:160px; border-top:none; color:#388e3c; font-weight:600; font-size:11px; text-transform:uppercase; padding:12px 20px;">Jumlah</th>
                    <th style="border-top:none; color:#388e3c; font-weight:600; font-size:11px; text-transform:uppercase; padding:12px 20px;">Dari / Sumber</th>
                    <th style="border-top:none; color:#388e3c; font-weight:600; font-size:11px; text-transform:uppercase; padding:12px 20px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($masukList)): ?>
                <tr><td colspan="5" class="text-center text-muted" style="padding:30px; font-size:13px; background:#fff;">Belum ada riwayat pemasukan</td></tr>
                <?php else: ?>
                <?php foreach ($masukList as $k => $lk): ?>
                <tr style="border-bottom:1px solid #f1f5f9; background:#fff;">
                    <td class="text-muted" style="font-size:13px; padding:12px 20px;"><?= $k + 1 ?></td>
                    <td class="text-muted" style="font-size:13px; padding:12px 20px;"><?= date('d/m/Y', strtotime($lk['tanggal'])) ?></td>
                    <td class="text-right fw-700" style="color:#27ae60; font-size:13px; padding:12px 20px;">+ Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                    <td style="font-size:13px; color:#475569; padding:12px 20px;"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                    <td class="text-muted" style="font-size:13px; padding:12px 20px;"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── BAGIAN PENGELUARAN PER KATEGORI ── -->
<div style="font-weight:700; color:#c62828; font-size:15px; margin:24px 0 12px; display:flex; align-items:center; gap:8px;">
    <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#c62828;"></span>
    ↓ RIWAYAT PENGELUARAN PER KATEGORI
    <span style="margin-left:auto; font-size:13px; font-weight:600; color:#475569;">
        Total: Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?>
    </span>
</div>

<?php foreach ($groupedKeluar as $categoryName => $items): ?>
<div class="card mb-3" style="margin-top: 0; border: 1px solid #ffcdd2; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden;">
    <div class="card-header" style="background: #fff5f5; border-bottom: 1px solid #ffcdd2; display: flex; justify-content: space-between; align-items: center; padding: 14px 20px;">
        <span style="font-weight: 600; color: #b71c1c; font-size: 14px; display: flex; align-items: center; gap: 10px; letter-spacing: 0.2px;">
            <?php
            $dotColor = '#94a3b8';
            if ($categoryName === 'Gaji') $dotColor = '#3b82f6';
            elseif ($categoryName === 'Pembelian Material') $dotColor = '#f59e0b';
            elseif ($categoryName === 'Sewa Alat') $dotColor = '#10b981';
            ?>
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: <?= $dotColor ?>;"></span>
            <?= strtoupper($categoryName) ?>
        </span>
        <span style="background: #ffffff; color: #b71c1c; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; border: 1px solid #ffcdd2;">
            Total: Rp <?= number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') ?>
        </span>
    </div>
    <div class="table-wrap" style="padding: 0; margin: 0;">
        <table style="margin: 0; border: none;">
            <thead style="background: #fff5f5;">
                <tr>
                    <th style="width: 50px; border-top: none; color: #c62828; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">No</th>
                    <th style="width: 120px; border-top: none; color: #c62828; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Tanggal</th>
                    <th class="text-right" style="width: 150px; border-top: none; color: #c62828; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Jumlah</th>
                    <th style="border-top: none; color: #c62828; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Kepada / Tujuan</th>
                    <th style="border-top: none; color: #c62828; font-weight: 600; font-size: 11px; text-transform: uppercase; padding: 12px 20px;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="5" class="text-center text-muted" style="padding: 30px; font-size: 13px; background: #ffffff;">Tidak ada transaksi untuk kategori ini</td></tr>
                <?php else: ?>
                <?php foreach ($items as $k => $lk): ?>
                <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                    <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= $k + 1 ?></td>
                    <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= date('d/m/Y', strtotime($lk['tanggal'])) ?></td>
                    <td class="text-right" style="color:#c62828; font-weight:500; font-size: 13px; padding: 12px 20px;">- Rp <?= number_format($lk['jumlah'], 0, ',', '.') ?></td>
                    <td style="font-size: 13px; color: #475569; padding: 12px 20px;"><?= htmlspecialchars($lk['sumber'] ?? '—') ?></td>
                    <td class="text-muted" style="font-size: 13px; padding: 12px 20px;"><?= htmlspecialchars($lk['keterangan'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($items)): ?>
            <tfoot>
                <tr style="background: #fff0f0; border-top: 2px solid #ffcdd2;">
                    <td colspan="2" style="font-size: 13px; font-weight: 700; color: #b71c1c; padding: 10px 20px; text-align: right;">Subtotal <?= strtoupper($categoryName) ?></td>
                    <td class="text-right" style="font-size: 13px; font-weight: 700; color: #b71c1c; padding: 10px 20px;">- Rp <?= number_format(array_sum(array_column($items, 'jumlah')), 0, ',', '.') ?></td>
                    <td colspan="2" style="padding: 10px 20px;"></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php endforeach; ?>

<!-- GRAND TOTAL PENGELUARAN -->
<div style="background: linear-gradient(135deg, #b71c1c, #c62828); border-radius: 8px; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
    <span style="color: #ffffff; font-weight: 700; font-size: 15px;">TOTAL SELURUH PENGELUARAN</span>
    <span style="color: #ffffff; font-weight: 700; font-size: 18px;">- Rp <?= number_format($totalPengeluaran, 0, ',', '.') ?></span>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
