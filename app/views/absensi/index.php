<?php require BASE_PATH . '/app/views/layouts/header.php';
$canManageAbsensi = roleCanManage('absensi');
$activeTab = $_GET['tab'] ?? ($canManageAbsensi ? 'absen-tab' : 'karyawan-tab');
?>

<style>
/* ============ STYLE SEMUA PUTIH ============ */
body { background: #f5f5f5; }
.card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
.card-header { padding: 15px 20px; border-bottom: 1px solid #eee; background: white; }
.card-title { font-weight: 600; color: #333; }
.card-body { padding: 20px; }
.btn-primary { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.btn-danger { background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; }
.btn-sm { padding: 4px 8px; font-size: 12px; }
.btn-success { background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #333; }
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
.table th { background: #f8f9fa; font-weight: 600; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
.badge-hadir { background: #d4edda; color: #155724; }
.badge-izin { background: #fff3cd; color: #856404; }
.badge-sakit { background: #d1ecf1; color: #0c5460; }
.badge-alpha { background: #f8d7da; color: #721c24; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.filter-bar { display: flex; gap: 10px; margin: 15px 0; flex-wrap: wrap; }
.filter-bar input, .filter-bar select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.tab-buttons { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.tab-btn { padding: 12px 24px; background: white; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; }
.tab-btn.active { background: #007bff; color: white; border-color: #007bff; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
.modal-content { background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; }
.close-modal { float: right; cursor: pointer; font-size: 24px; }
.face-recognition-section { position: relative; }
#video { width: 100%; max-width: 500px; border-radius: 8px; border: 1px solid #ddd; background: #000; }
#overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
.scanning-status { position: absolute; bottom: 20px; left: 20px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 8px; border-radius: 4px; text-align: center; font-size: 12px; }
.detected-card { background: #f0f0f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
.btn-camera { padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; border: none; }
.btn-camera-start { background: #007bff; color: white; }
.btn-camera-stop { background: #6c757d; color: white; }
.status-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-top: 15px; }
.status-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-top: 15px; }
.btn-excel { background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
</style>

<div class="tab-buttons">
    <?php if ($canManageAbsensi): ?>
        <button class="tab-btn <?= $activeTab === 'absen-tab' ? 'active' : '' ?>" onclick="openTab('absen-tab')">📸 ABSEN WAJAH</button>
    <?php endif; ?>
    <button class="tab-btn <?= $activeTab === 'karyawan-tab' ? 'active' : '' ?>" onclick="openTab('karyawan-tab')">👥 DAFTAR KARYAWAN</button>
    <button class="tab-btn <?= $activeTab === 'rekap-tab' ? 'active' : '' ?>" onclick="openTab('rekap-tab')">📊 REKAP ABSENSI</button>
    <button class="tab-btn <?= $activeTab === 'gaji-tab' ? 'active' : '' ?>" onclick="openTab('gaji-tab')">💰 REKAP GAJI</button>
</div>

<!-- TAB 1: ABSENSI WAJAH -->
<div id="absen-tab" class="tab-content <?= $activeTab === 'absen-tab' ? 'active' : '' ?>">
    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <span class="card-title">🎯 Scan Wajah Absensi</span>
            </div>
            <div class="card-body">
                <?php if ($canManageAbsensi): ?>
                <div style="position: relative; display: flex; justify-content: center;">
                    <div style="position: relative;">
                        <video id="video" autoplay muted playsinline style="width:100%; max-width:500px; border-radius:8px;"></video>
                        <canvas id="overlay" style="position:absolute; top:0; left:0; width:100%; height:100%;"></canvas>
                        <div id="scanning-status" class="scanning-status">🔍 Mulai kamera untuk scan</div>
                    </div>
                </div>
                
                <div style="text-align: center; margin: 15px 0;">
                    <button id="start-camera" class="btn-camera btn-camera-start">🎥 Mulai Kamera</button>
                    <button id="stop-camera" class="btn-camera btn-camera-stop">⏹️ Stop Kamera</button>
                </div>
                
                <div id="detected-info" style="display: none;"></div>
                <?php else: ?>
                <div style="padding: 24px; color: #555;">
                    Hanya admin yang dapat mencatat absensi. Anda dapat melihat daftar karyawan dan rekap dari tab berikutnya.
                </div>
                <?php endif; ?>
                <div class="detected-card">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="font-size: 48px;">👤</div>
                            <div>
                                <h3 id="detected-name" style="margin:0">-</h3>
                                <p id="detected-position" style="margin:5px 0 0">-</p>
                                <p id="detected-phone" style="margin:5px 0 0; font-size:12px;">📞 -</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($canManageAbsensi): ?>
                <div id="absen-form" style="display: none;">
                    <input type="hidden" id="recognized-id">
                    <input type="hidden" id="face-snapshot">
                    <input type="hidden" id="absen-type" value="masuk">
                    
                    <div class="form-group">
                        <label>📍 Pilih Proyek</label>
                        <select id="id_proyek" class="form-control" required>
                            <option value="">-- Pilih Proyek --</option>
                            <?php foreach ($proyekList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>⏰ Tipe Absensi</label>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="btn-masuk" style="flex:1; padding:10px; background:#007bff; color:white; border:none; border-radius:4px;">📥 Absen Masuk</button>
                            <button type="button" id="btn-keluar" style="flex:1; padding:10px; background:#6c757d; color:white; border:none; border-radius:4px;">📤 Absen Keluar</button>
                        </div>
                    </div>
                    
                    <div id="overtime-box" style="display:none; background:#f8f9fa; padding:15px; border-radius:4px; margin:10px 0;">
                        <label>⏰ Lembur (Jam)</label>
                        <input type="number" id="lembur" step="0.5" min="0" max="12" class="form-control" placeholder="Contoh: 2.5">
                        <small>Lembur dihitung setelah jam 17:00 (1.5x gaji)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Keterangan</label>
                        <textarea id="keterangan" rows="2" class="form-control" placeholder="Opsional..."></textarea>
                    </div>
                    
                    <button id="submit-absen" class="btn-primary" style="width:100%; margin-top:15px;">✅ Absen Sekarang</button>
                </div>
                <?php endif; ?>
                
                <div id="status-message"></div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 Status Absen Hari Ini</span>
            </div>
            <div class="card-body">
                <div id="status-hari-ini" style="display: flex; gap: 15px;">
                    <div style="flex:1; text-align:center; padding:15px; background:#f8f9fa; border-radius:8px;">
                        <div style="font-size:24px; font-weight:bold;" id="total-hadir">0</div>
                        <div>Hadir Hari Ini</div>
                    </div>
                    <div style="flex:1; text-align:center; padding:15px; background:#f8f9fa; border-radius:8px;">
                        <div style="font-size:24px; font-weight:bold;" id="total-belum-absen">0</div>
                        <div>Belum Absen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: DAFTAR KARYAWAN -->
<div id="karyawan-tab" class="tab-content">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span class="card-title">👥 Daftar Karyawan</span>
            <?php if ($canManageAbsensi): ?>
            <button onclick="showModalKaryawan()" class="btn-primary">+ Tambah Karyawan</button>
            <?php endif; ?>
        </div>
        <div class="table-wrap" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>No. Telepon</th>
                        <th>Status Wajah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="karyawan-tbody">
                    <!-- Data akan diisi via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 3: REKAP ABSENSI -->
<div id="rekap-tab" class="tab-content">
    <div class="card">
        <div class="card-header">
            <span class="card-title">📊 Rekap Absensi Karyawan</span>
            <div class="filter-bar">
                <input type="date" id="filter-tanggal" placeholder="Pilih Tanggal">
                <button id="cari-tanggal" class="btn-primary">🔍 Cari</button>
                <button id="export-excel-harian" class="btn-excel">📊 Export Excel</button>
            </div>
        </div>
        <div class="table-wrap" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Total Jam</th>
                        <th>Lembur</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody id="rekap-harian-tbody">
                    <tr><td colspan="10" style="text-align:center; padding:40px;">Pilih tanggal untuk melihat rekap</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 4: REKAP GAJI -->
<div id="gaji-tab" class="tab-content">
    <div class="grid-2">
        <div class="card">
            <div class="card-header">
                <span class="card-title">💰 Ringkasan Gaji</span>
            </div>
            <div class="card-body">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align:center;">
                    <h3>Total Gaji Bulan Ini</h3>
                    <div style="font-size: 32px; font-weight: bold;" id="total-gaji">Rp 0</div>
                </div>
                <div class="filter-bar">
                    <select id="gaji-bulan" class="form-control" style="width: auto;">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                    <select id="gaji-tahun" class="form-control" style="width: auto;">
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                    <button id="filter-gaji" class="btn-primary">Filter</button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 Detail Gaji Karyawan</span>
            </div>
            <div class="table-wrap" style="max-height: 500px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpha</th>
                            <th>Lembur</th>
                            <th>Total Gaji</th>
                        </tr>
                    </thead>
                    <tbody id="detail-gaji-body">
                        <tr><td colspan="7" style="text-align:center; padding:40px;">Pilih bulan dan tahun</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH KARYAWAN -->
<div id="modal-karyawan" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModalKaryawan()">&times;</span>
        <h3>Tambah Karyawan Baru</h3>
        <form id="form-karyawan" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" id="nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Jabatan *</label>
                <input type="text" id="jabatan" class="form-control" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" id="no_telp" class="form-control" placeholder="081234567890">
            </div>
            <div class="form-group">
                <label>Gaji per Hari (Rp) *</label>
                <input type="number" id="gaji_per_hari" class="form-control" required placeholder="50000">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">Simpan Karyawan</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
// ============ BASE URL ============
const BASE_URL = window.location.origin + '/SofwanLand-project/SofwanLand-project/public';
const canManageAbsensi = <?= $canManageAbsensi ? 'true' : 'false' ?>;

// ============ TAB NAVIGATION ============
function openTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    if (event && event.target) event.target.classList.add('active');
}

function showMessage(msg, type) {
    const div = document.getElementById('status-message');
    if (div) {
        div.innerHTML = msg;
        div.className = `status-${type}`;
        div.style.display = 'block';
        setTimeout(() => div.style.display = 'none', 3000);
    } else {
        alert(msg);
    }
}

// ============ LOAD KARYAWAN ============
async function loadKaryawan() {
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=getKaryawan`);
        const data = await res.json();
        
        if (data.success && data.data) {
            const tbody = document.getElementById('karyawan-tbody');
            tbody.innerHTML = '';
            
            data.data.forEach(k => {
                const row = `
                    <tr>
                        <td>${k.id}</td>
                        <td><strong>${k.nama}</strong></td>
                        <td>${k.jabatan}</td>
                        <td>${k.no_telp || '-'}</td>
                        <td>${k.face_descriptor ? '<span class="badge-hadir">✅ Terdaftar</span>' : '<span class="badge-alpha">❌ Belum</span>'}
                        <td>
                            ${canManageAbsensi ? `
                                <button onclick="registerFace(${k.id})" class="btn-primary btn-sm">📸 Reg Wajah</button>
                                <button onclick="deleteKaryawan(${k.id})" class="btn-danger btn-sm">Hapus</button>
                            ` : '<span class="text-muted" style="font-size:12px;">Readonly</span>'}
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }
    } catch(err) {
        console.error('Load karyawan error:', err);
    }
}

// ============ TAMBAH KARYAWAN ============
function showModalKaryawan() {
    document.getElementById('modal-karyawan').style.display = 'flex';
}

function closeModalKaryawan() {
    document.getElementById('modal-karyawan').style.display = 'none';
}

document.getElementById('form-karyawan').onsubmit = async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('nama', document.getElementById('nama').value);
    formData.append('jabatan', document.getElementById('jabatan').value);
    formData.append('no_telp', document.getElementById('no_telp').value);
    formData.append('gaji_per_hari', document.getElementById('gaji_per_hari').value);
    
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=addKaryawan`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            alert('✅ Karyawan berhasil ditambahkan!');
            closeModalKaryawan();
            document.getElementById('form-karyawan').reset();
            loadKaryawan();
        } else {
            alert('❌ Gagal: ' + (data.message || 'Unknown error'));
        }
    } catch(err) {
        alert('❌ Error: ' + err.message);
    }
};

// ============ HAPUS KARYAWAN ============
async function deleteKaryawan(id) {
    if (confirm('Yakin hapus karyawan ini?')) {
        try {
            const res = await fetch(`${BASE_URL}/api.php?action=deleteKaryawan&id=${id}`);
            const data = await res.json();
            if (data.success) {
                alert('✅ Karyawan dihapus!');
                loadKaryawan();
            } else {
                alert('❌ Gagal hapus');
            }
        } catch(err) {
            alert('❌ Error: ' + err.message);
        }
    }
}

// ============ LOAD REKAP HARIAN ============
async function loadRekapHarian(tanggal) {
    if (!tanggal) {
        const today = new Date().toISOString().split('T')[0];
        tanggal = today;
        document.getElementById('filter-tanggal').value = today;
    }
    
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=getRekapHarian&tanggal=${tanggal}`);
        const data = await res.json();
        
        const tbody = document.getElementById('rekap-harian-tbody');
        
        if (data.success && data.data && data.data.length > 0) {
            tbody.innerHTML = '';
            data.data.forEach((a, i) => {
                const statusClass = a.status == 'hadir' ? 'badge-hadir' : (a.status == 'izin' ? 'badge-izin' : (a.status == 'sakit' ? 'badge-sakit' : 'badge-alpha'));
                const row = `
                    <tr>
                        <td>${i+1}</td>
                        <td>${a.tanggal}</td>
                        <td><strong>${a.nama_karyawan}</strong></td>
                        <td>${a.jabatan}</td>
                        <td>${a.jam_masuk || '-'}</td>
                        <td>${a.jam_keluar || '-'}</td>
                        <td>${a.total_jam || '0'} jam</td>
                        <td>${a.lembur_jam || '0'} jam</td>
                        <td><span class="${statusClass}">${a.status}</span></td>
                        <td>${a.keterangan || '-'}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; padding:40px;">Tidak ada data absensi untuk tanggal ini</td></tr>';
        }
    } catch(err) {
        console.error('Load rekap error:', err);
    }
}

// ============ LOAD GAJI ============
async function loadGaji(bulan, tahun) {
    if (!bulan) bulan = new Date().getMonth() + 1;
    if (!tahun) tahun = new Date().getFullYear();
    
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=getGaji&bulan=${bulan}&tahun=${tahun}`);
        const data = await res.json();
        
        if (data.success) {
            document.getElementById('total-gaji').innerHTML = `Rp ${Number(data.total_gaji).toLocaleString('id-ID')}`;
            
            const tbody = document.getElementById('detail-gaji-body');
            tbody.innerHTML = '';
            
            if (data.detail && data.detail.length > 0) {
                data.detail.forEach(g => {
                    const row = `
                        <tr>
                            <td>${g.nama}</td>
                            <td>${g.hadir}</td>
                            <td>${g.izin}</td>
                            <td>${g.sakit}</td>
                            <td>${g.alpha}</td>
                            <td>${Number(g.total_lembur).toFixed(1)} jam</td>
                            <td>Rp ${Number(g.total_gaji).toLocaleString('id-ID')}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px;">Belum ada data gaji</td></tr>';
            }
        }
    } catch(err) {
        console.error('Load gaji error:', err);
    }
}

// ============ LOAD STATUS HARI INI ============
async function loadStatusHariIni() {
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=getStatusHariIni`);
        const data = await res.json();
        if (data.success) {
            document.getElementById('total-hadir').innerHTML = data.hadir || 0;
            document.getElementById('total-belum-absen').innerHTML = data.belum_absen || 0;
        }
    } catch(err) {}
}

// ============ EXPORT EXCEL ============
document.getElementById('export-excel-harian').onclick = async () => {
    const tanggal = document.getElementById('filter-tanggal').value;
    if (!tanggal) {
        alert('Pilih tanggal terlebih dahulu!');
        return;
    }
    
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=getRekapHarian&tanggal=${tanggal}`);
        const data = await res.json();
        
        if (data.success && data.data && data.data.length > 0) {
            const wsData = [['No', 'Tanggal', 'Nama', 'Jabatan', 'Jam Masuk', 'Jam Keluar', 'Total Jam', 'Lembur', 'Status', 'Keterangan']];
            
            data.data.forEach((a, i) => {
                wsData.push([
                    i+1, a.tanggal, a.nama_karyawan, a.jabatan,
                    a.jam_masuk || '-', a.jam_keluar || '-',
                    a.total_jam || '0', a.lembur_jam || '0',
                    a.status, a.keterangan || '-'
                ]);
            });
            
            const ws = XLSX.utils.aoa_to_sheet(wsData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, `Absensi_${tanggal}`);
            XLSX.writeFile(wb, `absensi_${tanggal}.xlsx`);
        } else {
            alert('Tidak ada data untuk diexport');
        }
    } catch(err) {
        alert('Error export: ' + err.message);
    }
};

// ============ FACE RECOGNITION ============
let video = document.getElementById('video');
let overlay = document.getElementById('overlay');
let stream = null;
let scanning = false;
let interval = null;
let modelsLoaded = false;
let isRegisterMode = false;
let currentUserId = null;

async function loadModels() {
    const statusDiv = document.getElementById('scanning-status');
    if (statusDiv) statusDiv.innerHTML = '📦 Memuat model...';
    
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights');
        await faceapi.nets.faceLandmark68Net.loadFromUri('https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights');
        await faceapi.nets.faceRecognitionNet.loadFromUri('https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights');
        
        modelsLoaded = true;
        if (statusDiv) statusDiv.innerHTML = '✅ Siap. Klik Mulai Kamera';
    } catch(err) {
        if (statusDiv) statusDiv.innerHTML = '❌ Gagal load model';
        console.error(err);
    }
}

async function startCamera() {
    if (stream) stopCamera();
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
        video.srcObject = stream;
        await video.play();
        
        overlay.width = video.videoWidth;
        overlay.height = video.videoHeight;
        
        scanning = true;
        startDetection();
        document.getElementById('scanning-status').innerHTML = '🔍 Scan wajah...';
    } catch(err) {
        alert('Gagal akses kamera: ' + err.message);
    }
}

function stopCamera() {
    scanning = false;
    if (interval) clearInterval(interval);
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    video.srcObject = null;
    document.getElementById('scanning-status').innerHTML = '⏹️ Kamera stop';
}

function startDetection() {
    if (interval) clearInterval(interval);
    
    interval = setInterval(async () => {
        if (!scanning || !modelsLoaded || !video.videoWidth) return;
        
        try {
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors();
            
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            
            if (detections.length > 0) {
                const displaySize = { width: video.videoWidth, height: video.videoHeight };
                const resized = faceapi.resizeResults(detections, displaySize);
                faceapi.draw.drawDetections(overlay, resized);
                
                const descriptor = Array.from(detections[0].descriptor);
                
                if (isRegisterMode && currentUserId) {
                    await registerFaceDescriptor(currentUserId, descriptor);
                } else {
                    await recognizeFace(descriptor);
                }
            } else if (!isRegisterMode) {
                document.getElementById('detected-info').style.display = 'none';
                document.getElementById('absen-form').style.display = 'none';
            }
        } catch(err) {
            console.error(err);
        }
    }, 1000);
}

async function recognizeFace(descriptor) {
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=recognizeFace`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ face_descriptor: descriptor })
        });
        const data = await res.json();
        
        if (data.success && data.karyawan) {
            document.getElementById('detected-name').innerHTML = data.karyawan.nama;
            document.getElementById('detected-position').innerHTML = data.karyawan.jabatan;
            document.getElementById('detected-phone').innerHTML = `📞 ${data.karyawan.no_telp || '-'}`;
            document.getElementById('recognized-id').value = data.karyawan.id;
            document.getElementById('detected-info').style.display = 'block';
            document.getElementById('absen-form').style.display = 'block';
            document.getElementById('scanning-status').innerHTML = `✅ ${data.karyawan.nama}`;
            
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            document.getElementById('face-snapshot').value = canvas.toDataURL('image/jpeg', 0.8);
        } else {
            document.getElementById('detected-info').style.display = 'none';
            document.getElementById('absen-form').style.display = 'none';
            document.getElementById('scanning-status').innerHTML = '❌ Wajah tidak dikenali';
        }
    } catch(err) {
        console.error(err);
    }
}

async function registerFace(userId) {
    currentUserId = userId;
    isRegisterMode = true;
    document.getElementById('scanning-status').innerHTML = '📸 Registrasi wajah...';
    if (!stream) await startCamera();
}

async function registerFaceDescriptor(userId, descriptor) {
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=registerFace`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId, face_descriptor: descriptor })
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ Registrasi wajah berhasil!');
            isRegisterMode = false;
            loadKaryawan();
        } else {
            alert('❌ Gagal: ' + data.message);
        }
    } catch(err) {
        alert('❌ Error: ' + err.message);
    }
}

// ============ SUBMIT ABSEN ============
document.getElementById('submit-absen').onclick = async () => {
    const id_karyawan = document.getElementById('recognized-id').value;
    const id_proyek = document.getElementById('id_proyek').value;
    const absenType = document.getElementById('absen-type').value;
    const lembur = document.getElementById('lembur')?.value || 0;
    const snapshot = document.getElementById('face-snapshot').value;
    const keterangan = document.getElementById('keterangan').value;
    
    if (!id_karyawan) { alert('Scan wajah dulu!'); return; }
    if (!id_proyek) { alert('Pilih proyek!'); return; }
    
    const btn = document.getElementById('submit-absen');
    btn.disabled = true;
    btn.textContent = '⏳ Menyimpan...';
    
    const formData = new FormData();
    formData.append('id_karyawan', id_karyawan);
    formData.append('id_proyek', id_proyek);
    formData.append('absensi_type', absenType);
    formData.append('lembur_jam', lembur);
    formData.append('face_snapshot', snapshot);
    formData.append('keterangan', keterangan);
    
    try {
        const res = await fetch(`${BASE_URL}/api.php?action=storeAbsensi`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ ' + data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            alert('❌ ' + data.message);
            btn.disabled = false;
            btn.textContent = '✅ Absen';
        }
    } catch(err) {
        alert('❌ Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = '✅ Absen';
    }
};

document.getElementById('btn-masuk').onclick = () => {
    document.getElementById('absen-type').value = 'masuk';
    document.getElementById('overtime-box').style.display = 'none';
};
document.getElementById('btn-keluar').onclick = () => {
    document.getElementById('absen-type').value = 'keluar';
    document.getElementById('overtime-box').style.display = 'block';
};

// ============ EVENT LISTENERS ============
document.getElementById('start-camera').onclick = async () => {
    if (!modelsLoaded) await loadModels();
    await startCamera();
};
document.getElementById('stop-camera').onclick = stopCamera;
document.getElementById('cari-tanggal').onclick = () => {
    const tanggal = document.getElementById('filter-tanggal').value;
    loadRekapHarian(tanggal);
};
document.getElementById('filter-gaji').onclick = () => {
    const bulan = document.getElementById('gaji-bulan').value;
    const tahun = document.getElementById('gaji-tahun').value;
    loadGaji(bulan, tahun);
};

// ============ INIT ============
loadModels();
loadKaryawan();
loadStatusHariIni();
setInterval(loadStatusHariIni, 30000);

// Set default tanggal hari ini
const today = new Date().toISOString().split('T')[0];
document.getElementById('filter-tanggal').value = today;
loadRekapHarian(today);

// Set default bulan/tahun
const now = new Date();
document.getElementById('gaji-bulan').value = now.getMonth() + 1;
document.getElementById('gaji-tahun').value = now.getFullYear();
loadGaji(now.getMonth() + 1, now.getFullYear());
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>