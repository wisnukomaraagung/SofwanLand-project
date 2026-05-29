<?php require BASE_PATH . '/app/views/layouts/header.php';
$barangForSelect = $barangList; // reuse for forms
$proyekList = (new ProyekModel())->getAll();
$canManageBarang = roleCanManage('barang');
?>

<!-- SUMMARY CARDS -->
<div class="stats-grid">
    <div class="stat-card highlight-stat">
        <div class="stat-label">Pengeluaran Bulan Ini</div>
        <div class="stat-value">Rp <?= number_format($summary['pengeluaran_bulan_ini'] ?? 0, 0, ',', '.') ?></div>
        <div class="stat-trend">pembelian material</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Jenis Barang</div>
        <div class="stat-value"><?= number_format($summary['jenis_barang'] ?? 0) ?></div>
        <div class="stat-trend">item terdaftar</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Transaksi Masuk</div>
        <div class="stat-value"><?= number_format($summary['transaksi_masuk'] ?? 0) ?></div>
        <div class="stat-trend">nota pembelian</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Transaksi Keluar</div>
        <div class="stat-value"><?= number_format($summary['transaksi_keluar'] ?? 0) ?></div>
        <div class="stat-trend">distribusi barang</div>
    </div>
</div>

<!-- TABS (PILL NAV) -->
<?php
$activeTab = $_GET['tab'] ?? 'masuk';
?>
<div class="pill-nav">
    <a href="?page=barang&tab=masuk" class="pill-btn <?= $activeTab === 'masuk' ? 'active' : '' ?>">↓ Barang Masuk</a>
    <a href="?page=barang&tab=keluar" class="pill-btn <?= $activeTab === 'keluar' ? 'active' : '' ?>">↑ Barang Keluar</a>
    <a href="?page=barang&tab=stok" class="pill-btn <?= $activeTab === 'stok' ? 'active' : '' ?>">≡ Master Barang</a>
</div>

<?php if ($activeTab === 'masuk'): ?>
<!-- BARANG MASUK -->
<?php if ($canManageBarang): ?>
<div class="split-layout">
<?php else: ?>
<div>
<?php endif; ?>
    <!-- LEFT: FORM -->
    <?php if ($canManageBarang): ?>
    <div class="card">
        <div class="card-header">
            <span class="card-title">↓ CATAT BARANG MASUK</span>
        </div>
        <div class="card-body">
            <div class="toggle-tabs">
                <div class="toggle-tab active" id="tab-upload" onclick="switchUploadMode('upload')">📁 UPLOAD</div>
                <div class="toggle-tab" id="tab-kamera" onclick="switchUploadMode('kamera')">📷 KAMERA</div>
            </div>

            <form method="POST" action="?page=barang&action=storeMasuk" enctype="multipart/form-data" id="form-masuk">
                
                <!-- UPLOAD/CAMERA AREA -->
                <div class="dropzone-area" id="dropzone" role="button" tabindex="0">
                    <input type="file" name="foto_kuitansi" id="file-input" style="display:none" accept="image/*">
                    
                    <div id="upload-ui">
                        <div class="dropzone-icon">📄</div>
                        <div class="dropzone-text">Klik atau drag foto kuitansi</div>
                        <div class="dropzone-subtext">JPG, PNG, HEIC — Maks 10MB</div>
                    </div>
                    
                    <video id="kamera-preview" class="video-preview" autoplay playsinline></video>
                    <img id="image-preview" class="image-preview">
                </div>
                
                <div id="ocr-status" style="font-size:12px; color:#b8860b; text-align:center; margin-bottom:16px; display:none;" aria-live="polite">
                    Siap memindai kuitansi.
                </div>

                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Nama Barang</label>
                        <div class="input-with-button">
                            <input type="text" id="nama_barang_baru" name="nama_barang_baru" placeholder="Ketik nama barang..." list="barang-list" onchange="syncBarangId(this)">
                            <datalist id="barang-list">
                                <?php foreach ($barangForSelect as $b): ?>
                                    <option data-id="<?= $b['id'] ?>" value="<?= htmlspecialchars($b['nama_barang']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <input type="hidden" id="id_barang" name="id_barang" value="0">
                            <button type="button" class="btn btn-add-new" onclick="document.getElementById('nama_barang_baru').focus()">+ Daftarkan barang baru</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" id="jumlah" name="jumlah" min="1" required placeholder="1">
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <input type="text" id="satuan" name="satuan" placeholder="Mis: Sak, Batang">
                    </div>

                    <div class="form-group form-full">
                        <label>Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label>Harga Satuan (Rp)</label>
                        <input type="number" id="harga_satuan" name="harga_satuan" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Supplier / Toko</label>
                        <input type="text" id="supplier" name="supplier" placeholder="Toko Bangunan...">
                    </div>



                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="2" placeholder="Catatan..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:20px; width: 100%; justify-content: center; padding: 14px; font-size: 14px;">SIMPAN BARANG MASUK</button>
            </form>
            <?php else: ?>
            <div style="padding: 24px; color: #555;">Hanya admin yang dapat mencatat barang masuk dan mengedit riwayat masuk.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: TABLE -->
    <div class="card">
        <div class="card-header">
            <span class="card-title" style="text-transform: uppercase;">Riwayat Masuk</span>
            <div style="display:flex; gap:10px;">
                <input type="text" placeholder="🔍 Cari..." style="width: 150px; padding: 6px 12px; font-size: 13px;">
                <a href="?page=barang&action=exportMasukExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Excel</a>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tgl</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Harga Sat.</th>
                        <th>Total</th>
                        <th>Supplier</th>
                        <th>Keterangan</th>
                        <?php if ($canManageBarang): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($masukList)): ?>
                    <tr><td colspan="<?= $canManageBarang ? 9 : 8 ?>" class="text-center text-muted" style="padding:40px">Belum ada riwayat masuk</td></tr>
                    <?php else: ?>
                    <?php foreach ($masukList as $i => $m): ?>
                    <tr>
                        <td class="text-muted"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                        <td class="text-muted" style="font-size:13px"><?= date('d M Y', strtotime($m['tanggal'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($m['nama_barang']) ?></strong>
                            <?php if (!empty($m['foto_kuitansi'])): ?>
                                <a href="<?= htmlspecialchars(BarangController::buktiViewUrl($m['foto_kuitansi'], 'masuk')) ?>" title="Lihat Kuitansi" style="text-decoration:none; margin-left:6px; font-size:14px;">🖼️</a>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= number_format($m['jumlah']) ?></strong> <span class="text-muted"><?= $m['satuan'] ?></span></td>
                        <?php if (isset($m['harga_satuan']) && $m['harga_satuan'] > 0): ?>
                            <td class="text-muted">Rp <?= number_format($m['harga_satuan'],0,',','.') ?></td>
                            <td><strong>Rp <?= number_format($m['harga_satuan'] * $m['jumlah'],0,',','.') ?></strong></td>
                        <?php else: ?>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                        <?php endif; ?>
                        <td class="text-muted"><?= htmlspecialchars($m['supplier'] ?? '—') ?></td>
                        <td class="text-muted" style="font-size:12px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($m['keterangan'] ?? '') ?>">
                            <?= htmlspecialchars($m['keterangan'] ?? '—') ?>
                        </td>
                        <?php if ($canManageBarang): ?>
                        <td>
                            <div class="flex">
                                <a href="?page=barang&action=editMasuk&id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete('?page=barang&action=deleteMasuk&id=<?= $m['id'] ?>','<?= htmlspecialchars($m['nama_barang'],ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">Hapus</a>
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

<!-- SCRIPTS FOR CAMERA AND OCR MOVED TO BOTTOM -->

<?php elseif ($activeTab === 'stok'): ?>
<!-- STOK BARANG (Master Data) -->
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nama Barang</th><th>Satuan</th><th class="text-right">Harga Satuan</th>
                    <th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Stok</th>
                    <?php if ($canManageBarang): ?><th>Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($barangList)): ?>
                <tr><td colspan="<?= $canManageBarang ? 8 : 7 ?>" class="text-center text-muted" style="padding:40px">Belum ada barang</td></tr>
                <?php else: ?>
                <?php foreach ($barangList as $i => $b): ?>
                <tr>
                    <td class="text-muted"><?= $i+1 ?></td>
                    <td><strong><?= htmlspecialchars($b['nama_barang']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($b['satuan']) ?></td>
                    <td class="text-right font-mono" style="font-size:13px">Rp <?= number_format($b['harga_satuan'],0,',','.') ?></td>
                    <td class="text-right"><?= number_format($b['total_masuk']) ?></td>
                    <td class="text-right"><?= number_format($b['total_keluar']) ?></td>
                    <td class="text-right fw-700" style="<?= $b['stok'] < 10 ? 'color:#c0392b' : '' ?>"><?= number_format($b['stok']) ?></td>
                    <?php if ($canManageBarang): ?>
                    <td>
                        <div class="flex">
                            <a href="?page=barang&action=edit&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="javascript:void(0)" onclick="confirmDelete('?page=barang&action=delete&id=<?= $b['id'] ?>','<?= htmlspecialchars($b['nama_barang'],ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">Hapus</a>
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

<?php elseif ($activeTab === 'keluar'): ?>
<!-- BARANG KELUAR -->
<?php if ($canManageBarang): ?>
<div class="grid-2">
<?php else: ?>
<div>
<?php endif; ?>
    <?php if ($canManageBarang): ?>
    <div class="card">
        <div class="card-header"><span class="card-title">↑ Input Barang Keluar</span></div>
        <div class="card-body">
            <div class="toggle-tabs">
                <div class="toggle-tab active" id="tab-upload" onclick="switchUploadMode('upload')">📁 UPLOAD</div>
                <div class="toggle-tab" id="tab-kamera" onclick="switchUploadMode('kamera')">📷 KAMERA</div>
            </div>
            <form method="POST" action="?page=barang&action=storeKeluar" enctype="multipart/form-data" id="form-keluar">
                
                <!-- UPLOAD/CAMERA AREA -->
                <div class="dropzone-area" id="dropzone" role="button" tabindex="0">
                    <input type="file" name="foto_bukti" id="file-input" style="display:none" accept="image/*">
                    
                    <div id="upload-ui">
                        <div class="dropzone-icon">📄</div>
                        <div class="dropzone-text">Klik atau drag foto bukti (opsional)</div>
                        <div class="dropzone-subtext">JPG, PNG, HEIC — Maks 10MB</div>
                    </div>
                    
                    <video id="kamera-preview" class="video-preview" autoplay playsinline></video>
                    <img id="image-preview" class="image-preview">
                </div>

                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Barang *</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($barangForSelect as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> (Stok: <?= $b['stok'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Proyek *</label>
                        <select name="id_proyek" required>
                            <option value="">-- Pilih Proyek --</option>
                            <?php foreach ($proyekList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah *</label>
                        <input type="number" name="jumlah" min="1" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px">Simpan Barang Keluar</button>
            </form>
            <?php else: ?>
            <div style="padding: 24px; color: #555;">Hanya admin yang dapat mencatat barang keluar.</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <span class="card-title">Riwayat Keluar</span>
            <a href="?page=barang&action=exportKeluarExcel" class="btn btn-secondary btn-sm" style="text-decoration:none;">↓ Excel</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Proyek</th>
                        <th class="text-right">Jumlah</th>
                        <th>Keterangan</th>
                        <?php if ($canManageBarang): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keluarList)): ?>
                    <tr><td colspan="<?= $canManageBarang ? 6 : 5 ?>" class="text-center text-muted" style="padding:24px">Belum ada data</td></tr>
                    <?php else: ?>
                    <?php foreach ($keluarList as $k): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($k['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($k['nama_barang']) ?></td>
                        <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($k['nama_proyek']) ?></td>
                        <td class="text-right"><?= number_format($k['jumlah']) ?> <?= $k['satuan'] ?></td>
                        <td class="text-muted" style="font-size:12px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($k['keterangan'] ?? '') ?>">
                            <?= htmlspecialchars($k['keterangan'] ?? '—') ?>
                        </td>
                        <?php if ($canManageBarang): ?>
                        <td>
                            <div class="flex">
                                <a href="?page=barang&action=editKeluar&id=<?= $k['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete('?page=barang&action=deleteKeluar&id=<?= $k['id'] ?>','<?= htmlspecialchars($k['nama_barang'],ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">Hapus</a>
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
<?php endif; ?>

<?php if ($canManageBarang && in_array($activeTab, ['masuk', 'keluar'], true)): ?>
<?php if ($activeTab === 'masuk'): ?>
<script>window.BARANG_OCR_ASSETS = <?= json_encode(rtrim(BASE_URL, '/') . '/public/assets/tesseract') ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.1/dist/tesseract.min.js"></script>
<?php endif; ?>
<script src="<?= BASE_URL ?>/public/assets/js/barang-kuitansi.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    BarangKuitansi.init({
        ocrEnabled: <?= $activeTab === 'masuk' ? 'true' : 'false' ?>,
        assetBase: window.BARANG_OCR_ASSETS || ''
    });
});
</script>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
