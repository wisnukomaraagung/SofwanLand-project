<?php require BASE_PATH . '/app/views/layouts/header.php';
$barangForSelect = $barangList; // reuse for forms
$proyekList = (new ProyekModel())->getAll();
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
<div class="split-layout">
    <!-- LEFT: FORM -->
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
                <div class="dropzone-area" id="dropzone" onclick="document.getElementById('file-input').click()">
                    <input type="file" name="foto_kuitansi" id="file-input" style="display:none" accept="image/*" onchange="handleFileSelect(event)">
                    
                    <div id="upload-ui">
                        <div class="dropzone-icon">📄</div>
                        <div class="dropzone-text">Klik atau drag foto kuitansi</div>
                        <div class="dropzone-subtext">JPG, PNG, HEIC — Maks 10MB</div>
                    </div>
                    
                    <video id="kamera-preview" class="video-preview" autoplay playsinline></video>
                    <img id="image-preview" class="image-preview">
                </div>
                
                <!-- SCAN RESULT INDICATOR -->
                <div id="ocr-status" style="font-size:12px; color:#b8860b; text-align:center; margin-bottom:16px; display:none;">
                    Memindai kuitansi... Mohon tunggu.
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
                        <label>No. Kuitansi</label>
                        <input type="text" id="no_kuitansi" name="no_kuitansi" placeholder="INV-001 (opsional)">
                    </div>

                    <div class="form-group form-full">
                        <label>Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="2" placeholder="Catatan..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:20px; width: 100%; justify-content: center; padding: 14px; font-size: 14px;">SIMPAN BARANG MASUK</button>
            </form>
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
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($masukList)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:40px">Belum ada riwayat masuk</td></tr>
                    <?php else: ?>
                    <?php foreach ($masukList as $i => $m): ?>
                    <tr>
                        <td class="text-muted"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></td>
                        <td class="text-muted" style="font-size:13px"><?= date('d M Y', strtotime($m['tanggal'])) ?></td>
                        <td><strong><?= htmlspecialchars($m['nama_barang']) ?></strong></td>
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
                        <td>
                            <div class="flex" style="gap:4px">
                                <a href="?page=barang&action=editMasuk&id=<?= $m['id'] ?>" class="btn btn-secondary btn-sm" title="Edit" style="padding:4px 8px">✏️</a>
                                <a href="javascript:void(0)" onclick="confirmDelete('?page=barang&action=deleteMasuk&id=<?= $m['id'] ?>', 'Data Masuk: <?= htmlspecialchars($m['nama_barang'], ENT_QUOTES) ?>')" class="btn btn-secondary btn-sm" title="Hapus" style="padding:4px 8px; color: #c0392b;">🗑️</a>
                            </div>
                        </td>
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

<?php elseif ($activeTab === 'keluar'): ?>
<!-- BARANG KELUAR -->
<div class="grid-2">
    <div class="card">
        <div class="card-header"><span class="card-title">↑ Input Barang Keluar</span></div>
        <div class="card-body">
            <div class="toggle-tabs">
                <div class="toggle-tab active" id="tab-upload" onclick="switchUploadMode('upload')">📁 UPLOAD</div>
                <div class="toggle-tab" id="tab-kamera" onclick="switchUploadMode('kamera')">📷 KAMERA</div>
            </div>

            <form method="POST" action="?page=barang&action=storeKeluar" enctype="multipart/form-data" id="form-keluar">
                
                <!-- UPLOAD/CAMERA AREA -->
                <div class="dropzone-area" id="dropzone" onclick="document.getElementById('file-input').click()">
                    <input type="file" name="foto_bukti" id="file-input" style="display:none" accept="image/*" onchange="handleFileSelect(event)">
                    
                    <div id="upload-ui">
                        <div class="dropzone-icon">📄</div>
                        <div class="dropzone-text">Klik atau drag foto bukti (opsional)</div>
                        <div class="dropzone-subtext">JPG, PNG, HEIC — Maks 10MB</div>
                    </div>
                    
                    <video id="kamera-preview" class="video-preview" autoplay playsinline></video>
                    <img id="image-preview" class="image-preview">
                </div>

                <!-- SCAN RESULT INDICATOR -->
                <div id="ocr-status" style="font-size:12px; color:#b8860b; text-align:center; margin-bottom:16px; display:none;">
                    Memindai gambar... Mohon tunggu.
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
        </div>
    </div>
    <div class="card">
        <div class="card-header"><span class="card-title">Riwayat Keluar</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Barang</th><th>Proyek</th><th class="text-right">Jumlah</th><th>Keterangan</th></tr></thead>
                <tbody>
                    <?php if (empty($keluarList)): ?>
                    <tr><td colspan="5" class="text-center text-muted" style="padding:24px">Belum ada data</td></tr>
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
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- SCRIPTS FOR CAMERA AND OCR -->
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js'></script>
<script>
    let currentMode = 'upload';
    let stream = null;

    function switchUploadMode(mode) {
        currentMode = mode;
        const tabUpload = document.getElementById('tab-upload');
        const tabKamera = document.getElementById('tab-kamera');
        if (tabUpload) tabUpload.classList.toggle('active', mode === 'upload');
        if (tabKamera) tabKamera.classList.toggle('active', mode === 'kamera');
        
        const ui = document.getElementById('upload-ui');
        const video = document.getElementById('kamera-preview');
        const img = document.getElementById('image-preview');
        const dropzone = document.getElementById('dropzone');
        if (!dropzone) return;

        if (mode === 'kamera') {
            ui.style.display = 'none';
            img.style.display = 'none';
            video.style.display = 'block';
            dropzone.onclick = takeSnapshot;
            startCamera();
        } else {
            stopCamera();
            video.style.display = 'none';
            if (img.src && img.src !== window.location.href) {
                img.style.display = 'block';
                ui.style.display = 'none';
            } else {
                ui.style.display = 'block';
                img.style.display = 'none';
            }
            dropzone.onclick = () => document.getElementById('file-input').click();
        }
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            const video = document.getElementById('kamera-preview');
            if (video) video.srcObject = stream;
        } catch (err) {
            alert('Tidak dapat mengakses kamera: ' + err.message);
            switchUploadMode('upload');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function takeSnapshot() {
        const video = document.getElementById('kamera-preview');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        const dataUrl = canvas.toDataURL('image/jpeg');
        document.getElementById('image-preview').src = dataUrl;
        
        fetch(dataUrl)
            .then(res => res.blob())
            .then(blob => {
                const file = new File([blob], "snapshot.jpg", { type: "image/jpeg" });
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('file-input').files = dt.files;
                
                switchUploadMode('upload');
                runOCR(file);
            });
    }

    function handleFileSelect(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgPreview = document.getElementById('image-preview');
                const uploadUi = document.getElementById('upload-ui');
                if (imgPreview) {
                    imgPreview.src = e.target.result;
                    imgPreview.style.display = 'block';
                }
                if (uploadUi) uploadUi.style.display = 'none';
                runOCR(file);
            }
            reader.readAsDataURL(file);
        }
    }

    const dropzone = document.getElementById('dropzone');
    if(dropzone) {
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files[0] && currentMode === 'upload') {
                document.getElementById('file-input').files = e.dataTransfer.files;
                handleFileSelect({target: document.getElementById('file-input')});
            }
        });
    }

    async function runOCR(imageFile) {
        const status = document.getElementById('ocr-status');
        if(!status) return;
        status.style.display = 'block';
        status.innerText = 'Memindai gambar... Mohon tunggu.';
        
        try {
            const worker = await Tesseract.createWorker('ind');
            const ret = await worker.recognize(imageFile);
            const text = ret.data.text;
            console.log('OCR Result:', text);
            await worker.terminate();
            
            status.style.color = '#27ae60';
            status.innerText = 'Pemindaian selesai. Memproses data...';
            
            parseReceiptText(text);
            
            setTimeout(() => { status.style.display = 'none'; status.style.color = '#b8860b'; }, 3000);
        } catch (err) {
            console.error(err);
            status.style.color = '#c0392b';
            status.innerText = 'Gagal memindai kuitansi/bukti.';
        }
    }

    function parseReceiptText(text) {
        const priceMatch = text.match(/(?:Rp|Total)\s*[.:]?\s*(\d{1,3}(?:[.,]\d{3})*)/i);
        if (priceMatch) {
            const price = priceMatch[1].replace(/[^0-9]/g, '');
            const hargaSatuan = document.getElementById('harga_satuan');
            if (hargaSatuan) {
                hargaSatuan.value = price;
            }
        }

        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 2);
        if (lines.length > 0) {
            const supplier = document.getElementById('supplier');
            if (supplier && !/\d/.test(lines[0]) && supplier.value === '') {
                supplier.value = lines[0];
            }
            
            const keterangan = document.getElementById('keterangan');
            if (keterangan) {
                keterangan.value = lines.slice(0, 3).join('\n');
            }
        }
        
        alert('Data berhasil dipindai. Harap periksa dan lengkapi sisa kolom yang kosong.');
    }

    function syncBarangId(input) {
        const datalist = document.getElementById('barang-list');
        if (!datalist) return;
        const options = datalist.options;
        const hiddenInput = document.getElementById('id_barang');
        if(!hiddenInput) return;
        
        hiddenInput.value = 0;
        
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === input.value) {
                hiddenInput.value = options[i].getAttribute('data-id');
                break;
            }
        }
    }
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
