<?php require BASE_PATH . '/app/views/layouts/header.php';
$barangForSelect = $barangList; // reuse for forms
$proyekList = (new ProyekModel())->getAll();
?>

<!-- TABS -->
<div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:2px solid #f0f0f0;padding-bottom:0">
    <?php
    $tabs = ['stok'=>'Stok Barang','masuk'=>'Barang Masuk','keluar'=>'Barang Keluar'];
    $activeTab = $_GET['tab'] ?? 'stok';
    foreach ($tabs as $k => $label): ?>
    <a href="?page=barang&tab=<?= $k ?>"
       style="padding:10px 20px;text-decoration:none;font-size:14px;font-weight:600;border-bottom:2px solid <?= $activeTab===$k ? '#0a0a0a' : 'transparent' ?>;color:<?= $activeTab===$k ? '#0a0a0a' : '#888' ?>;margin-bottom:-2px;transition:all .2s">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($activeTab === 'stok'): ?>
<!-- STOK BARANG -->
<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Nama Barang</th><th>Satuan</th><th class="text-right">Harga Satuan</th>
                    <th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Stok</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($barangList)): ?>
                <tr><td colspan="8" class="text-center text-muted" style="padding:40px">Belum ada barang</td></tr>
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
                    <td>
                        <div class="flex">
                            <a href="?page=barang&action=edit&id=<?= $b['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                            <a href="javascript:void(0)" onclick="confirmDelete('?page=barang&action=delete&id=<?= $b['id'] ?>','<?= htmlspecialchars($b['nama_barang'],ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">Hapus</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($activeTab === 'masuk'): ?>
<!-- BARANG MASUK -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Input Barang Masuk</span></div>
        <div class="card-body">
            <form method="POST" action="?page=barang&action=storeMasuk">
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
                <button type="submit" class="btn btn-primary" style="margin-top:12px">+ Catat Masuk</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Riwayat Masuk</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Barang</th><th class="text-right">Jumlah</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php if (empty($masukList)): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada data</td></tr>
                    <?php else: ?>
                    <?php foreach ($masukList as $m): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($m['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($m['nama_barang']) ?></td>
                        <td class="text-right"><?= number_format($m['jumlah']) ?> <?= $m['satuan'] ?></td>
                        <td class="text-muted"><?= htmlspecialchars($m['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'keluar'): ?>
<!-- BARANG KELUAR -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">Input Barang Keluar</span></div>
        <div class="card-body">
            <form method="POST" action="?page=barang&action=storeKeluar">
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
                <button type="submit" class="btn btn-primary" style="margin-top:12px">+ Catat Keluar</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Riwayat Keluar</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Barang</th><th>Proyek</th><th class="text-right">Jumlah</th></tr></thead>
                <tbody>
                    <?php if (empty($keluarList)): ?>
                    <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada data</td></tr>
                    <?php else: ?>
                    <?php foreach ($keluarList as $k): ?>
                    <tr>
                        <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($k['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($k['nama_barang']) ?></td>
                        <td class="text-muted" style="font-size:12px"><?= htmlspecialchars($k['nama_proyek']) ?></td>
                        <td class="text-right"><?= number_format($k['jumlah']) ?> <?= $k['satuan'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
