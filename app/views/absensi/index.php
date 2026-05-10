<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="grid-2">
    <!-- FORM INPUT ABSENSI -->
    <div class="card">
        <div class="card-header"><span class="card-title">Input Absensi</span></div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/public/index.php?page=absensi&action=store">
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>Karyawan *</label>
                        <select name="id_karyawan" required>
                            <option value="">-- Pilih Karyawan --</option>
                            <?php foreach ($karyawanList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?> — <?= htmlspecialchars($k['jabatan']) ?></option>
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
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:12px">+ Catat Absensi</button>
            </form>
        </div>
    </div>

    <!-- REKAP PER PROYEK -->
    <div class="card">
        <div class="card-header"><span class="card-title">Rekap per Proyek</span></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Proyek</th><th class="text-right">Pekerja</th><th class="text-right">Hadir</th><th class="text-right">Izin</th><th class="text-right">Sakit</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($rekapList)): ?>
                    <tr><td colspan="5" class="text-center text-muted" style="padding:24px">Belum ada data</td></tr>
                    <?php else: ?>
                    <?php foreach ($rekapList as $r): ?>
                    <tr>
                        <td style="font-size:13px"><?= htmlspecialchars($r['nama_proyek']) ?></td>
                        <td class="text-right fw-700"><?= $r['total_pekerja'] ?></td>
                        <td class="text-right"><?= $r['hadir'] ?></td>
                        <td class="text-right"><?= $r['izin'] ?></td>
                        <td class="text-right"><?= $r['sakit'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TABEL ABSENSI -->
<div class="card mt-4">
    <div class="card-header"><span class="card-title">Daftar Absensi</span></div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Tanggal</th><th>Karyawan</th><th>Jabatan</th><th>Proyek</th><th>Status</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (empty($absensiList)): ?>
                <tr><td colspan="8" class="text-center text-muted" style="padding:40px">Belum ada data absensi</td></tr>
                <?php else: ?>
                <?php foreach ($absensiList as $i => $a): ?>
                <?php
                    $statusColors = ['hadir'=>'badge-selesai','izin'=>'badge-aktif','sakit'=>'badge-aktif','alpha'=>'badge-pending'];
                    $sc = $statusColors[$a['status']] ?? 'badge-pending';
                ?>
                <tr>
                    <td class="text-muted"><?= $i+1 ?></td>
                    <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($a['tanggal'])) ?></td>
                    <td><strong><?= htmlspecialchars($a['nama_karyawan']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($a['jabatan']) ?></td>
                    <td style="font-size:13px"><?= htmlspecialchars($a['nama_proyek']) ?></td>
                    <td><span class="badge <?= $sc ?>"><?= ucfirst($a['status']) ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($a['keterangan'] ?? '-') ?></td>
                    <td>
                        <a href="javascript:void(0)"
                           onclick="confirmDelete('<?= BASE_URL ?>/public/index.php?page=absensi&action=delete&id=<?= $a['id'] ?>','absensi ini')"
                           class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
