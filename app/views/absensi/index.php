<?php require BASE_PATH . '/app/views/layouts/header.php'; 
$globalProjectId = $_SESSION['selected_project_id'] ?? null;
?>

<style>
/* Style untuk daftar proyek */
.proyek-table {
    width: 100%;
}

.proyek-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.proyek-row:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    display: inline-block;
}

.status-aktif {
    background: #e8f5e9;
    color: #4CAF50;
}

.status-selesai {
    background: #f5f5f5;
    color: #9E9E9E;
}

.progress-bar-mini {
    width: 80px;
    height: 6px;
    background: #e0e0e0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill-mini {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #8BC34A);
    border-radius: 3px;
}

.proyek-detail-container {
    display: none;
}

.proyek-detail-container.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-back {
    background: none;
    border: none;
    color: #667eea;
    cursor: pointer;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.tab-absensi {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
}

.tab-absensi-btn {
    padding: 8px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    transition: all 0.3s;
}

.tab-absensi-btn.active {
    color: #667eea;
    border-bottom: 2px solid #667eea;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
}

.stat-label {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 5px;
}

.btn-camera {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-camera-stop {
    background: #dc3545;
}

.btn-manual {
    background: #ff9800;
}

.detected-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 15px;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    padding: 20px;
}

.modal-header {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.close {
    float: right;
    cursor: pointer;
    font-size: 24px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
}

.status-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.status-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.badge-selesai {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.badge-pending {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.btn-edit-keluar {
    background: #ff9800;
    color: white;
    border: none;
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
}

.btn-edit-keluar:hover {
    background: #f57c00;
}
</style>

<?php if (!$globalProjectId): ?>
<div class="card text-center" style="padding: 40px; margin: 20px auto; max-width: 600px;">
    <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
    <h2>Belum Ada Proyek yang Dipilih</h2>
    <p class="text-muted" style="margin-top: 10px; margin-bottom: 20px;">Silakan pilih proyek terlebih dahulu pada Dashboard untuk melihat data absensi.</p>
    <a href="<?= BASE_URL ?>/public/index.php?page=dashboard" class="btn btn-primary" style="text-decoration: none;">Pilih Proyek di Dashboard</a>
</div>
<?php else: ?>

<!-- HALAMAN DAFTAR PROYEK -->
<div id="daftar-proyek-container" style="display: none;">
    <div class="page-header">
        <h1>Absen</h1>
        <p class="text-muted">Pilih proyek untuk memulai absensi</p>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="table proyek-table">
                <thead>
                    <tr><th>#</th><th>NAMA PROYEK</th><th>LOKASI</th><th>PERIODE</th><th>STATUS</th><th>PROGRESS</th><th>NILAI KONTRAK</th><th>TOTAL BIAYA</th></tr>
                </thead>
                <tbody id="proyek-tbody">
                    <tr><td colspan="8" class="text-center">Loading proyek...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- HALAMAN DETAIL PROYEK -->
<div id="detail-container" class="proyek-detail-container">
    <div class="page-header">
        <h1 id="detail-nama-proyek">-</h1>
        <p id="detail-lokasi">-</p>
    </div>

    <div class="grid-3" style="margin-bottom: 24px;">
        <div class="stat-card" style="background: linear-gradient(135deg, #000000 0%, #090909 100%);"><div class="stat-number" id="stat-karyawan">0</div><div class="stat-label">Karyawan</div></div>
        <div class="stat-card" style="background: linear-gradient(135deg, #000000 0%, #090909 100%);"><div class="stat-number" id="stat-hadir">0</div><div class="stat-label">Hadir Bulan Ini</div></div>
        <div class="stat-card" style="background: linear-gradient(135deg, #070707 0%, #0a0a0a 100%);"><div class="stat-number" id="stat-gaji">Rp 0</div><div class="stat-label">Total Gaji Bulan Ini</div></div>
    </div>

    <div class="tab-absensi">
        <button class="tab-absensi-btn active" data-tab="karyawan">👥 Daftar Karyawan</button>
        <button class="tab-absensi-btn" data-tab="absensi">📋 Riwayat Absensi</button>
        <button class="tab-absensi-btn" data-tab="face">Absen</button>
    </div>

    <!-- Tab Karyawan -->
    <div id="tab-karyawan" class="tab-pane active">
        <div class="card">
            <div class="card-header">
                <span>👥 Daftar Karyawan</span>
                <button class="btn btn-primary btn-sm" onclick="openTambahKaryawan()">+ Tambah Karyawan</button>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>NIK</th><th>Nama</th><th>Jabatan</th><th>Gaji Pokok</th><th>Status Wajah</th><th>Aksi</th></tr></thead>
                    <tbody id="karyawan-tbody"><tr><td colspan="7" class="text-center">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Riwayat Absensi (ADMIN bisa edit jam keluar) -->
    <div id="tab-absensi" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <span>📋 Riwayat Absensi</span>
                <button class="btn btn-secondary btn-sm" onclick="exportExcel()">📥 Export Excel</button>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Tanggal</th><th>Karyawan</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Lembur</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody id="absensi-tbody"><tr><td colspan="7" class="text-center">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Face Recognition Absen (Hanya Absen Masuk) -->
    <div id="tab-face" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <span>Absen</span>
                <span style="font-size: 12px; margin-left: 15px;">✅ Absen Masuk (Otomatis/Manual) | ⚠️ Absen Keluar hanya oleh ADMIN</span>
            </div>
            <div class="card-body">
                <input type="hidden" id="current-proyek-id" value="">
                
                
<div id="live-clock-box" style="text-align:center;margin-bottom:15px;">
    <div id="live-date" style="font-size:14px;font-weight:600;"></div>
    <div id="live-clock" style="font-size:32px;font-weight:700;">00:00:00</div>
</div>
<div style="position: relative; display: flex; justify-content: center; margin-bottom: 20px;">
                    <div style="position: relative;">
                        <video id="video" autoplay muted playsinline style="width: 100%; max-width: 500px; border-radius: 12px; background: #000;"></video>
                        <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
                        <div id="scan-status" style="position: absolute; bottom: 10px; left: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 8px; border-radius: 8px; text-align: center; font-size: 12px;">🔄 Menyiapkan...</div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button id="start-camera" class="btn-camera">🎥 Mulai Kamera</button>
                    <button id="stop-camera" class="btn-camera btn-camera-stop">Stop Kamera</button>
                    <button id="btnAbsenKeluar" class="btn btn-danger">
    📤 Absen Keluar
</button>
                </div>
                
                <div id="detected-info" style="display: none; margin-top: 20px;">
                    <div class="detected-card">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="font-size: 40px;">👤</span>
                            <div><h3 id="detected-name" style="margin: 0;">-</h3><p id="detected-position" style="margin: 5px 0 0;">-</p></div>
                        </div>
                    </div>
                </div>
                
                <div id="absen-form" style="display: none; margin-top: 20px;">
                    <input type="hidden" id="karyawan-id">
                    <input type="hidden" id="face-snapshot">
                    
                    <div class="form-group">
                        <label>⏰ Absen Masuk</label>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="btn-manual-masuk" class="btn-camera btn-manual" style="flex:1;">📥 Absen Masuk MANUAL</button>
                        </div>
                        <small>✅ Atau biarkan wajah terdeteksi untuk ABSEN OTOMATIS</small>
                    </div>
                    
                    <div class="form-group">
                        <label>📋 Status</label>
                        <select id="status" class="form-control">
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>📝 Keterangan</label>
                        <textarea id="keterangan" rows="2" class="form-control" placeholder="Contoh: Terlambat..."></textarea>
                    </div>
                    
                    <button id="submit-absen" class="btn btn-primary" style="width: 100%;" disabled>📥 Konfirmasi Absen Masuk</button>
                </div>
                
                <div id="status-message" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div id="modal-karyawan" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header"><span>➕ Tambah Karyawan</span><span class="close" onclick="closeModal()">&times;</span></div>
        <form id="form-karyawan">
            <div class="form-group"><label>NIK</label><input type="text" id="nik" class="form-control" required></div>
            <div class="form-group"><label>Nama</label><input type="text" id="nama" class="form-control" required></div>
            <div class="form-group"><label>Jabatan</label><input type="text" id="jabatan" class="form-control" required></div>
            <div class="form-group"><label>Gaji Pokok</label><input type="number" id="gaji_pokok" class="form-control" value="5000000"></div>
            <div class="form-group"><label>No Telepon</label><input type="text" id="no_telp" class="form-control"></div>
            <button type="submit" class="btn btn-primary" style="width:100%">Simpan</button>
        </form>
    </div>
</div>

<!-- Modal Edit Absen Keluar (Admin) -->
<div id="modal-edit-keluar" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header"><span>Edit Absen Keluar</span><span class="close" onclick="closeEditModal()">&times;</span></div>
        <form id="form-edit-keluar">
            <input type="hidden" id="edit_id_absensi">
            <div class="form-group"><label>Jam Keluar</label><input type="time" id="edit_jam_keluar" class="form-control" required></div>
            <div class="form-group"><label>Lembur (jam)</label><input type="number" id="edit_lembur" step="0.5" class="form-control" value="0"></div>
            <div class="form-group"><label>Status</label><select id="edit_status" class="form-control"><option value="hadir">Hadir</option><option value="izin">Izin</option><option value="sakit">Sakit</option></select></div>
            <div class="form-group"><label>Keterangan</label><textarea id="edit_keterangan" rows="2" class="form-control"></textarea></div>
            <button type="submit" class="btn btn-primary" style="width:100%">💾 Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- Modal Register Wajah -->
<div id="modal-register-face" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header"><span>📸 Register Wajah</span><span class="close" onclick="closeModalRegister()">&times;</span></div>
        <div class="modal-body">
            <p>Karyawan: <strong id="register-nama"></strong></p>
            <input type="hidden" id="register-karyawan-id">
            <video id="register-video" autoplay muted playsinline style="width:100%; border-radius:8px;"></video>
            <button id="capture-face" class="btn btn-primary" style="margin-top:15px; width:100%">📸 Ambil & Register Wajah</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
// ==================== VARIABLES ====================
let currentProyekId = null;
let video = document.getElementById('video');
let overlay = document.getElementById('overlay');
let scanStatus = document.getElementById('scan-status');
let stream = null;
let detectionInterval = null;
let modelsLoaded = false;
let currentKaryawan = null;
let registerStream = null;
let registerVideo = null;
let lastAutoAbsenTime = 0;
const AUTO_ABSEN_COOLDOWN = 60000;

// ==================== LOAD DAFTAR PROYEK ====================
function loadProyek() {
    fetch('<?= BASE_URL ?>/public/index.php?page=api&action=getAllProyek')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('proyek-tbody');
            if (data.success && data.data && data.data.length > 0) {
                tbody.innerHTML = data.data.map((p, i) => {
                    let tglMulai = p.tanggal_mulai ? new Date(p.tanggal_mulai).toLocaleDateString('id-ID') : '-';
                    let tglSelesai = p.tanggal_selesai ? new Date(p.tanggal_selesai).toLocaleDateString('id-ID') : '-';
                    let statusClass = p.status == 'aktif' ? 'status-aktif' : 'status-selesai';
                    return `<tr class="proyek-row" onclick="pilihProyek(${p.id})">
                        <td>${i+1}</td><td><strong>${p.nama_proyek}</strong></td>
                        <td>${(p.lokasi || '-').substring(0,40)}...</td>
                        <td>${tglMulai} – ${tglSelesai}</td>
                        <td><span class="status-badge ${statusClass}">${(p.status || 'AKTIF').toUpperCase()}</span></td>
                        <td>${p.progress || 0}%</td>
                        <td>Rp ${(p.nilai_kontrak || 0).toLocaleString('id-ID')}</td>
                        <td>Rp ${(p.total_biaya || 0).toLocaleString('id-ID')}</td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data</td></tr>';
            }
        }).catch(() => document.getElementById('proyek-tbody').innerHTML = '<tr><td colspan="8" class="text-center">Gagal memuat</td></tr>');
}

// ==================== PILIH PROYEK ====================
function pilihProyek(id) {
    currentProyekId = id;
    document.getElementById('current-proyek-id').value = id;
    document.getElementById('daftar-proyek-container').style.display = 'none';
    document.getElementById('detail-container').classList.add('active');
    
    fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=getProyek&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('detail-nama-proyek').innerHTML = `🏗️ ${data.data.nama_proyek}`;
                document.getElementById('detail-lokasi').innerHTML = `📍 ${data.data.lokasi || '-'}`;
            }
        });
    
    loadKaryawan(id);
    loadAbsensi(id);
    loadStatistik(id);
}

function kembaliKeDaftar() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    if (detectionInterval) clearInterval(detectionInterval);
    document.getElementById('daftar-proyek-container').style.display = 'block';
    document.getElementById('detail-container').classList.remove('active');
}

function loadKaryawan(proyekId) {
    fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=getKaryawanByProyek&id_proyek=${proyekId}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('karyawan-tbody');
            if (data.success && data.data && data.data.length > 0) {
                tbody.innerHTML = data.data.map((k, i) => `
                    <tr>
                        <td>${i+1}</td><td>${k.nik}</td><td><strong>${k.nama}</strong></td>
                        <td>${k.jabatan}</td><td>Rp ${(k.gaji_pokok || 5000000).toLocaleString('id-ID')}</td>
                        <td>${k.face_descriptor ? '✅ Terdaftar' : '⚠️ Belum'}</td>
                        <td>
                            <button class="btn-edit-keluar" onclick="registerFace(${k.id}, '${k.nama}')">📸 Register</button>
                            <button class="btn-edit-keluar" style="background:#dc3545;" onclick="hapusKaryawan(${k.id})">🗑️ Hapus</button>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada karyawan</td></tr>';
            }
        });
}

function loadAbsensi(proyekId) {
    fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=getAbsensiByProyek&id_proyek=${proyekId}&bulan_ini=1`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('absensi-tbody');
            if (data.success && data.data && data.data.length > 0) {
                tbody.innerHTML = data.data.map(a => {
                    let statusClass = a.status == 'hadir' ? 'badge-selesai' : 'badge-pending';
                    let isKeluarEmpty = !a.jam_keluar || a.jam_keluar == '00:00:00';
                    return `
                    <tr>
                        <td>${new Date(a.tanggal).toLocaleDateString('id-ID')}</td>
                        <td><strong>${a.nama_karyawan}</strong></td>
                        <td>${a.jam_masuk || '-'}</td>
                        <td>${a.jam_keluar || '<span class="badge-pending" style="background:#fff3cd;padding:2px 6px;">Belum absen keluar</span>'}</td>
                        <td>${a.lembur_jam ? a.lembur_jam + ' jam' : '-'}</td>
                        <td><span class="${statusClass}">${(a.status || 'hadir').toUpperCase()}</span></td>
                        <td>
                            ${isKeluarEmpty ? 
                                `<button class="btn-edit-keluar" onclick="openEditKeluar(${a.id}, '${a.nama_karyawan}')">✏️ Input Absen Keluar</button>` : 
                                `<button class="btn-edit-keluar" onclick="openEditKeluar(${a.id}, '${a.nama_karyawan}')">✏️ Edit Keluar</button>`
                            }
                        </td>
                    </tr>
                `}).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">Belum ada data absensi</td></tr>';
            }
        });
}

function loadStatistik(proyekId) {
    fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=getStatistikProyek&id_proyek=${proyekId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-karyawan').innerHTML = data.data.total_karyawan || 0;
                document.getElementById('stat-hadir').innerHTML = data.data.total_hadir || 0;
                document.getElementById('stat-gaji').innerHTML = 'Rp ' + (data.data.total_gaji || 0).toLocaleString('id-ID');
            }
        });
}

// ==================== CRUD KARYAWAN ====================
function openTambahKaryawan() { document.getElementById('modal-karyawan').style.display = 'flex'; }
function closeModal() { document.getElementById('modal-karyawan').style.display = 'none'; }

document.getElementById('form-karyawan').addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('nik', document.getElementById('nik').value);
    formData.append('nama', document.getElementById('nama').value);
    formData.append('jabatan', document.getElementById('jabatan').value);
    formData.append('gaji_pokok', document.getElementById('gaji_pokok').value);
    formData.append('no_telp', document.getElementById('no_telp').value);
    formData.append('id_proyek', currentProyekId);
    
    fetch('<?= BASE_URL ?>/public/index.php?page=api&action=addKaryawan', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => { alert(data.message); if (data.success) { closeModal(); loadKaryawan(currentProyekId); loadStatistik(currentProyekId); } });
});

function hapusKaryawan(id) {
    if (confirm('Yakin hapus?')) {
        fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=deleteKaryawan&id=${id}`)
            .then(res => res.json())
            .then(data => { alert(data.message); if (data.success) { loadKaryawan(currentProyekId); loadStatistik(currentProyekId); } });
    }
}

// ==================== EDIT ABSEN KELUAR (ADMIN) ====================
let currentEditId = null;

function openEditKeluar(id, nama) {
    currentEditId = id;
    document.getElementById('edit_id_absensi').value = id;
    document.getElementById('modal-edit-keluar').style.display = 'flex';
    document.querySelector('#modal-edit-keluar .modal-header span:first-child').innerHTML = `✏️ Edit Absen Keluar - ${nama}`;
}

function closeEditModal() { document.getElementById('modal-edit-keluar').style.display = 'none'; }

document.getElementById('form-edit-keluar').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData();
    formData.append('id', document.getElementById('edit_id_absensi').value);
    formData.append('jam_keluar', document.getElementById('edit_jam_keluar').value);
    formData.append('lembur_jam', document.getElementById('edit_lembur').value);
    formData.append('status', document.getElementById('edit_status').value);
    formData.append('keterangan', document.getElementById('edit_keterangan').value);
    
    const submitBtn = document.querySelector('#form-edit-keluar button');
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Menyimpan...';
    
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=updateAbsensi', { method: 'POST', body: formData });
        const result = await response.json();
        alert(result.message);
        if (result.success) {
            closeEditModal();
            loadAbsensi(currentProyekId);
            loadStatistik(currentProyekId);
        }
    } catch (error) { alert('Error: ' + error.message); }
    submitBtn.disabled = false;
    submitBtn.textContent = '💾 Simpan Perubahan';
});

// ==================== REGISTER WAJAH ====================
function registerFace(id, nama) {
    document.getElementById('register-nama').innerHTML = nama;
    document.getElementById('register-karyawan-id').value = id;
    document.getElementById('modal-register-face').style.display = 'flex';
    
    if (registerStream) registerStream.getTracks().forEach(t => t.stop());
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => { registerStream = stream; registerVideo = document.getElementById('register-video'); registerVideo.srcObject = stream; registerVideo.play(); })
        .catch(err => console.error(err));
}

function closeModalRegister() {
    if (registerStream) { registerStream.getTracks().forEach(t => t.stop()); registerStream = null; }
    document.getElementById('modal-register-face').style.display = 'none';
}

document.getElementById('capture-face').addEventListener('click', async () => {
    const karyawanId = document.getElementById('register-karyawan-id').value;
    const videoReg = document.getElementById('register-video');
    if (!videoReg || !videoReg.videoWidth) { alert('Kamera belum siap'); return; }
    
    const detections = await faceapi.detectSingleFace(videoReg, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
    if (detections) {
        const descriptor = Array.from(detections.descriptor);
        fetch('<?= BASE_URL ?>/public/index.php?page=api&action=registerFace', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_karyawan: karyawanId, face_descriptor: descriptor })
        }).then(res => res.json()).then(data => { alert(data.message); if (data.success) { closeModalRegister(); loadKaryawan(currentProyekId); } });
    } else { alert('Wajah tidak terdeteksi!'); }
});

// ==================== FACE RECOGNITION MODELS ====================
async function loadModels() {
    scanStatus.innerHTML = '📦 Memuat model...';
    const modelUrls = ['https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/', 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights'];
    for (const url of modelUrls) {
        try {
            await Promise.all([faceapi.nets.tinyFaceDetector.loadFromUri(url), faceapi.nets.faceLandmark68Net.loadFromUri(url), faceapi.nets.faceRecognitionNet.loadFromUri(url)]);
            modelsLoaded = true; scanStatus.innerHTML = '✅ Klik Mulai Kamera'; return;
        } catch (err) { console.warn('Gagal dari:', url); }
    }
    scanStatus.innerHTML = '❌ Gagal muat model';
}

// ==================== START CAMERA ====================
document.getElementById('start-camera').addEventListener('click', async () => {
    if (!modelsLoaded) { scanStatus.innerHTML = '⏳ Tunggu model...'; return; }
    try {
        if (stream) stream.getTracks().forEach(t => t.stop());
        stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: "user" } });
        video.srcObject = stream;
        await new Promise(r => { video.onloadedmetadata = r; setTimeout(r, 2000); });
        await video.play();
        overlay.width = video.videoWidth; overlay.height = video.videoHeight;
        scanStatus.innerHTML = '🟢 Kamera aktif - Scan wajah';
        startDetection();
    } catch (err) {
        let errorMsg = err.name === 'NotAllowedError' ? 'Izin kamera ditolak!' : err.message;
        scanStatus.innerHTML = '❌ ' + errorMsg;
        alert(errorMsg);
    }
});

document.getElementById('stop-camera').addEventListener('click', () => {
    if (detectionInterval) clearInterval(detectionInterval);
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; video.srcObject = null; }
    scanStatus.innerHTML = '⏹️ Kamera dimatikan';
    document.getElementById('detected-info').style.display = 'none';
    document.getElementById('absen-form').style.display = 'none';
});

function startDetection() {
    if (detectionInterval) clearInterval(detectionInterval);
    detectionInterval = setInterval(async () => {
        if (!modelsLoaded || !stream || !video.videoWidth) return;
        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptors();
        const ctx = overlay.getContext('2d'); ctx.clearRect(0, 0, overlay.width, overlay.height);
        if (detections.length > 0) {
            const resized = faceapi.resizeResults(detections, { width: video.videoWidth, height: video.videoHeight });
            faceapi.draw.drawDetections(overlay, resized);
            await recognizeFace(Array.from(detections[0].descriptor));
            takeSnapshot();
        }
    }, 1000);
}

// ==================== RECOGNIZE FACE & AUTO ABSEN MASUK ====================
async function recognizeFace(faceDescriptor) {
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=recognizeFace', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ face_descriptors: faceDescriptor })
        });
        const result = await response.json();
        if (result.success && result.karyawan) {
            currentKaryawan = result.karyawan;
            document.getElementById('detected-name').innerHTML = result.karyawan.nama;
            document.getElementById('detected-position').innerHTML = result.karyawan.jabatan;
            document.getElementById('karyawan-id').value = result.karyawan.id;
            document.getElementById('detected-info').style.display = 'block';
            document.getElementById('absen-form').style.display = 'block';
            document.getElementById('submit-absen').disabled = false;
            scanStatus.innerHTML = `✅ Dikenali: ${result.karyawan.nama}`;
            await autoAbsenMasuk(result.karyawan.id);
            currentKaryawanId = result.karyawan.id;
            takeSnapshot();
        }
    } catch (error) { console.error(error); }
}

// ==================== AUTO ABSEN MASUK (OTOMATIS) ====================
async function autoAbsenMasuk(id_karyawan) {
    const id_proyek = document.getElementById('current-proyek-id').value;
    if (!id_proyek) { showMessage('Pilih proyek dulu', 'info'); return; }
    const now = Date.now();
    if (now - lastAutoAbsenTime < AUTO_ABSEN_COOLDOWN) return;
    
    const formData = new FormData();
    formData.append('id_karyawan', id_karyawan);
    formData.append('id_proyek', id_proyek);
    formData.append('absensi_type', 'masuk');
    formData.append('status', 'hadir');
    formData.append('keterangan', 'Absen masuk otomatis');
    formData.append('lembur_jam', 0);
    formData.append('face_snapshot', document.getElementById('face-snapshot').value);
    
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) { lastAutoAbsenTime = now; showMessage('✅ Absen Masuk Otomatis Berhasil!', 'success'); loadStatistik(currentProyekId); loadAbsensi(currentProyekId); }
        else if (!result.message.includes('sudah absen masuk')) console.log(result.message);
    } catch (error) { console.error(error); }
}

async function absenKeluar() {

    if (!currentKaryawanId) {
        showMessage('Scan wajah terlebih dahulu', 'error');
        return;
    }

    navigator.geolocation.getCurrentPosition(async function(position){

        const formData = new FormData();

        formData.append('id_karyawan', currentKaryawanId);
        formData.append('id_proyek', currentProyekId);
        formData.append('absensi_type', 'keluar');

        formData.append(
            'latitude',
            position.coords.latitude
        );

        formData.append(
            'longitude',
            position.coords.longitude
        );

        try {

            const response = await fetch(
                '<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi',
                {
                    method: 'POST',
                    body: formData
                }
            );

            const result = await response.json();

            if (result.success) {

                showMessage(
                    '✅ Absen Keluar Berhasil!',
                    'success'
                );

                loadStatistik(currentProyekId);
                loadAbsensi(currentProyekId);

            } else {

                showMessage(
                    result.message,
                    'error'
                );

            }

        } catch(error) {

            console.error(error);

            showMessage(
                'Gagal melakukan absen keluar',
                'error'
            );

        }

    }, function(){

        showMessage(
            'Aktifkan GPS terlebih dahulu',
            'error'
        );

    });

}

document
.getElementById('btnAbsenKeluar')
.addEventListener('click', absenKeluar);

// ==================== MANUAL ABSEN MASUK ====================
document.getElementById('btn-manual-masuk').addEventListener('click', async () => {
    const id_karyawan = document.getElementById('karyawan-id').value;
    const id_proyek = document.getElementById('current-proyek-id').value;
    if (!id_karyawan) { showMessage('Scan wajah dulu', 'error'); return; }
    if (!id_proyek) { showMessage('Pilih proyek', 'error'); return; }
    
    const formData = new FormData();
    formData.append('id_karyawan', id_karyawan);
    formData.append('id_proyek', id_proyek);
    formData.append('absensi_type', 'masuk');
    formData.append('status', document.getElementById('status').value);
    formData.append('keterangan', document.getElementById('keterangan').value || 'Absen masuk manual');
    formData.append('lembur_jam', 0);
    formData.append('face_snapshot', document.getElementById('face-snapshot').value);
    
    const btn = document.getElementById('submit-absen');
    btn.disabled = true; btn.textContent = '⏳ Menyimpan...';
    
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi', { method: 'POST', body: formData });
        const result = await response.json();
        showMessage(result.message, result.success ? 'success' : 'error');
        if (result.success) { loadStatistik(currentProyekId); loadAbsensi(currentProyekId); }
        btn.disabled = false; btn.textContent = '📥 Konfirmasi Absen Masuk';
    } catch (error) { showMessage('Error: ' + error.message, 'error'); btn.disabled = false; btn.textContent = '📥 Konfirmasi Absen Masuk'; }
});

function takeSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth; canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    document.getElementById('face-snapshot').value = canvas.toDataURL('image/jpeg', 0.8);
}

function showMessage(msg, type) {
    const div = document.getElementById('status-message');
    div.textContent = msg; div.style.display = 'block';
    div.className = type === 'success' ? 'status-success' : (type === 'error' ? 'status-error' : 'status-info');
    setTimeout(() => div.style.display = 'none', 4000);
}

// ==================== TAB & EXPORT ====================
document.querySelectorAll('.tab-absensi-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-absensi-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
    });
});

function exportExcel() { window.location.href = `<?= BASE_URL ?>/public/index.php?page=api&action=exportExcel&proyek_id=${currentProyekId}`; }


function updateRealtimeClock(){
    const now=new Date();
    const jam=String(now.getHours()).padStart(2,'0');
    const menit=String(now.getMinutes()).padStart(2,'0');
    const detik=String(now.getSeconds()).padStart(2,'0');

    const tanggal=now.toLocaleDateString('id-ID',{
        weekday:'long',
        day:'2-digit',
        month:'long',
        year:'numeric'
    });

    const c=document.getElementById('live-clock');
    const d=document.getElementById('live-date');

    if(c) c.innerHTML=`${jam}:${menit}:${detik}`;
    if(d) d.innerHTML=tanggal;
}

setInterval(updateRealtimeClock,1000);
updateRealtimeClock();


// ==================== INIT ====================
<?php if ($globalProjectId): ?>
pilihProyek(<?= (int)$globalProjectId ?>);
<?php else: ?>
loadProyek();
<?php endif; ?>
loadModels();
window.onclick = (event) => { if (event.target.classList.contains('modal')) event.target.style.display = 'none'; }
let currentKaryawanId = null;
</script>

<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>