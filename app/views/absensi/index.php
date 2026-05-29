<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<style>
    /* Warna Putih Abu-Abu */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: #f5f5f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .dashboard-header h1 {
        font-size: 28px;
        color: #333;
        margin-bottom: 5px;
    }

    .dashboard-header p {
        color: #666;
        font-size: 14px;
    }

    .nav-menu {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 10px;
    }

    .nav-item {
        padding: 10px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        color: #666;
        transition: all 0.3s;
        border-radius: 8px;
    }

    .nav-item:hover {
        background: #e0e0e0;
        color: #333;
    }

    .nav-item.active {
        background: #607d8b;
        color: white;
    }

    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e0e0e0;
        font-weight: 600;
        font-size: 18px;
        color: #333;
        background: #fafafa;
    }

    .card-body {
        padding: 24px;
    }

    .video-container {
        position: relative;
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
        background: #333;
        border-radius: 12px;
        overflow: hidden;
    }

    #video, #regis-video {
        width: 100%;
        max-width: 500px;
        background: #333;
        border-radius: 12px;
    }

    #overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .scanning-status {
        position: absolute;
        bottom: 20px;
        left: 20px;
        right: 20px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        font-size: 14px;
        z-index: 10;
    }

    .btn-camera {
        background: #607d8b;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        margin: 5px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .btn-camera:hover {
        background: #455a64;
    }

    .btn-camera-stop {
        background: #9e9e9e;
    }

    .btn-camera-stop:hover {
        background: #757575;
    }

    .btn-success {
        background: #4caf50;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-danger {
        background: #f44336;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }

    .status-card {
        background: #fafafa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }

    .status-card h3 {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }

    .status-card .status-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .status-card.hadir {
        border-left: 4px solid #4caf50;
    }

    .status-card.belum {
        border-left: 4px solid #ff9800;
    }

    .weather-widget {
        background: #fafafa;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .weather-temp {
        font-size: 48px;
        font-weight: bold;
        color: #333;
    }

    .weather-info {
        text-align: right;
        color: #666;
    }

    .weather-icon {
        font-size: 48px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .table th {
        background: #fafafa;
        font-weight: 600;
        color: #555;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-selesai {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .badge-warning {
        background: #fff3e0;
        color: #ef6c00;
    }

    .badge-danger {
        background: #ffebee;
        color: #c62828;
    }

    .text-right {
        text-align: right;
    }

    .mt-4 {
        margin-top: 20px;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        padding: 24px;
    }

    .modal-header {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e0e0e0;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>

    <div class="nav-menu">
        <button class="nav-item active" data-page="absensi">📸 ABSEN WAJAH</button>
        <button class="nav-item" data-page="karyawan">👥 DAFTAR KARYAWAN</button>
        <button class="nav-item" data-page="rekap">📊 REKAP ABSENSI</button>
        <button class="nav-item" data-page="gaji">💰 REKAP GAJI</button>
    </div>

    <div id="page-content">
        <!-- Halaman Absensi -->
        <div id="absensi-page">
            <div class="grid-2">
                <div class="card">
                    <div class="card-header">📸 Scan Wajah Absensi</div>
                    <div class="card-body">
                        <div class="video-container">
                            <video id="video" autoplay muted playsinline></video>
                            <canvas id="overlay"></canvas>
                            <div id="scanning-status" class="scanning-status">🟡 Siap. Klik Mulai Kamera</div>
                        </div>
                        <div style="text-align: center;">
                            <button type="button" id="start-camera-btn" class="btn-camera">🎥 Mulai Kamera</button>
                            <button type="button" id="stop-camera-btn" class="btn-camera btn-camera-stop">⏹️ Stop Kamera</button>
                        </div>
                        <div id="detected-info" style="display: none; margin-top: 20px;">
                            <div style="background: #e8f5e9; padding: 15px; border-radius: 12px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="font-size: 48px;">👤</div>
                                    <div>
                                        <h4 id="detected-name" style="margin: 0;">-</h4>
                                        <p id="detected-position" style="margin: 5px 0 0; color: #666;">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="absensi-form" style="display: none; margin-top: 20px;">
                            <input type="hidden" id="recognized-id">
                            <input type="hidden" id="face-snapshot-data">
                            <select id="id_proyek" class="form-control" style="margin-bottom: 10px;">
                                <option value="">-- Pilih Proyek --</option>
                                <?php foreach ($proyekList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                                <button type="button" id="btn-masuk" class="btn-camera" style="flex:1;">📥 Absen Masuk</button>
                                <button type="button" id="btn-keluar" class="btn-camera" style="flex:1; background:#ff9800;">📤 Absen Keluar</button>
                            </div>
                            <button type="button" id="submit-absen" class="btn-camera" style="width:100%;">✅ Konfirmasi Absen</button>
                        </div>
                        <div id="status-message" style="margin-top: 15px; padding: 10px; border-radius: 8px; display: none;"></div>
                    </div>
                </div>
                
                <div>
                    <div class="card" style="margin-bottom: 20px;">
                        <div class="card-header">📊 Status Absen Hari Ini</div>
                        <div class="card-body">
                            <div class="status-card hadir" style="margin-bottom: 15px;">
                                <h3>Total Hadir Hari Ini</h3>
                                <div class="status-value" id="hadir-count">0</div>
                            </div>
                            <div class="status-card belum">
                                <h3>Status Anda</h3>
                                <div class="status-value" id="my-status">Belum Absen</div>
                            </div>
                        </div>
                    </div>
                    <div class="weather-widget">
                        <div>
                            <div id="current-time">--:--</div>
                            <div id="current-date">--/--/----</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">📋 Daftar Absensi Hari Ini</div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Proyek</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="absensi-today-body">
                            <tr><td colspan="7" style="text-align:center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Halaman Karyawan -->
        <div id="karyawan-page" style="display: none;">
            <div class="card">
                <div class="card-header">
                    👥 Daftar Karyawan
                    <button onclick="showTambahKaryawan()" style="float:right; background:#607d8b; color:white; border:none; padding:5px 15px; border-radius:5px; cursor:pointer;">+ Tambah</button>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>ID</th><th>NIK</th><th>Nama</th><th>Jabatan</th><th>Status Wajah</th><th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="karyawan-body">
                            <?php if (!empty($karyawanList) && is_array($karyawanList)): ?>
                                <?php foreach ($karyawanList as $k): ?>
                                <tr>
                                    <td><?= $k['id'] ?? '' ?></td>
                                    <td><?= htmlspecialchars($k['nik'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($k['nama'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($k['jabatan'] ?? '-') ?></td>
                                    <td><?= !empty($k['face_descriptor']) ? '✅ Terdaftar' : '❌ Belum' ?></td>
                                    <td>
                                        <button onclick="openRegistrasiWajah(<?= $k['id'] ?>)" class="btn-success" style="padding:5px 10px; border:none; border-radius:4px; cursor:pointer;">📸 Registrasi Wajah</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center">Belum ada data karyawan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Halaman Rekap Absensi -->
        <div id="rekap-page" style="display: none;">
            <div class="card">
                <div class="card-header">📊 Rekap Absensi per Proyek (Bulan Ini)</div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>Proyek</th><th>Pekerja</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rekapList)): ?>
                                <?php foreach ($rekapList as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['nama_proyek'] ?? '-') ?></td>
                                    <td class="text-right"><?= $r['total_pekerja'] ?? 0 ?></td>
                                    <td class="text-right"><?= $r['hadir'] ?? 0 ?></td>
                                    <td class="text-right"><?= $r['izin'] ?? 0 ?></td>
                                    <td class="text-right"><?= $r['sakit'] ?? 0 ?></td>
                                    <td class="text-right"><?= $r['alpha'] ?? 0 ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center">Belum ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Halaman Rekap Gaji -->
        <div id="gaji-page" style="display: none;">
            <div class="card">
                <div class="card-header">💰 Rekap Gaji Karyawan (Bulan Ini)</div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Hadir</th><th>Lembur</th><th>Total Gaji</th>
                            </tr>
                        </thead>
                        <tbody id="gaji-body">
                            <tr><td colspan="6" style="text-align:center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">📅 Filter & Export Data</div>
    <div class="card-body">
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label>Tanggal Mulai</label>
                <input type="date" id="filter_mulai" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div>
                <label>Tanggal Selesai</label>
                <input type="date" id="filter_selesai" class="form-control" value="<?= date('Y-m-t') ?>">
            </div>
            <div>
                <label>Proyek</label>
                <select id="filter_proyek" class="form-control">
                    <option value="">Semua Proyek</option>
                    <?php foreach ($proyekList as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button id="btn-export-excel" class="btn-camera" style="background:#4caf50;">📊 Export Excel</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div id="modalKaryawan" class="modal">
    <div class="modal-content">
        <div class="modal-header">Tambah Karyawan</div>
        <form id="formTambahKaryawan">
            <div class="form-group"><label>NIK</label><input type="text" name="nik" class="form-control" required></div>
            <div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" required></div>
            <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan" class="form-control" required></div>
            <div class="form-group"><label>Gaji Pokok</label><input type="number" name="gaji_pokok" class="form-control" value="5000000"></div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeModal()" class="btn-camera-stop">Batal</button>
                <button type="submit" class="btn-camera">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrasi Wajah -->
<div id="modalRegistrasi" class="modal">
    <div class="modal-content">
        <div class="modal-header">📸 Registrasi Wajah</div>
        <div style="text-align: center; margin-bottom: 15px;">
            <video id="regis-video" autoplay muted playsinline style="width:100%; max-width:400px; border-radius:12px; background:#333;"></video>
        </div>
        <p id="regis-status" style="text-align: center; margin: 10px 0; color: #666;">🟡 Arahkan wajah ke kamera</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="closeRegisModal()" class="btn-camera-stop">Batal</button>
            <button onclick="ambilDanRegistrasiWajah()" class="btn-camera">📸 Ambil & Simpan</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
// ==================== VARIABLES ====================
let video = document.getElementById('video');
let overlay = document.getElementById('overlay');
let scanningStatus = document.getElementById('scanning-status');
let stream = null;
let detectionInterval = null;
let modelsLoaded = false;

let regisVideo = null;
let regisStream = null;
let currentRegisId = null;

// Base URL untuk API
const API_URL = '<?= BASE_URL ?>/public/index.php?page=api&action=';

// ==================== NAVIGASI ====================
document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
        const page = this.dataset.page;
        document.getElementById('absensi-page').style.display = page === 'absensi' ? 'block' : 'none';
        document.getElementById('karyawan-page').style.display = page === 'karyawan' ? 'block' : 'none';
        document.getElementById('rekap-page').style.display = page === 'rekap' ? 'block' : 'none';
        document.getElementById('gaji-page').style.display = page === 'gaji' ? 'block' : 'none';
        if (page === 'gaji') loadGajiData();
    });
});

// ==================== WEATHER & TIME ====================
function updateTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
    document.getElementById('current-date').textContent = now.toLocaleDateString('id-ID');
}
setInterval(updateTime, 1000);
updateTime();

// ==================== MODAL KARYAWAN ====================
function showTambahKaryawan() {
    document.getElementById('modalKaryawan').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalKaryawan').style.display = 'none';
}

document.getElementById('formTambahKaryawan').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(API_URL + 'addKaryawan', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    alert(result.message);
    if (result.success) location.reload();
});

// ==================== MODAL REGISTRASI WAJAH ====================
function openRegistrasiWajah(idKaryawan) {
    currentRegisId = idKaryawan;
    document.getElementById('modalRegistrasi').style.display = 'flex';
    
    setTimeout(async () => {
        regisVideo = document.getElementById('regis-video');
        try {
            if (regisStream) regisStream.getTracks().forEach(t => t.stop());
            regisStream = await navigator.mediaDevices.getUserMedia({ video: true });
            regisVideo.srcObject = regisStream;
            await regisVideo.play();
            document.getElementById('regis-status').innerHTML = '🟢 Kamera aktif - Arahkan wajah ke kamera';
            document.getElementById('regis-status').style.color = '#4caf50';
        } catch(e) {
            console.error(e);
            document.getElementById('regis-status').innerHTML = '🔴 Gagal akses kamera';
            document.getElementById('regis-status').style.color = '#f44336';
        }
    }, 500);
}

function closeRegisModal() {
    if (regisStream) {
        regisStream.getTracks().forEach(t => t.stop());
        regisStream = null;
    }
    document.getElementById('modalRegistrasi').style.display = 'none';
    currentRegisId = null;
}

async function ambilDanRegistrasiWajah() {
    if (!regisVideo || !regisVideo.videoWidth) {
        alert('Kamera belum siap, tunggu sebentar');
        return;
    }
    
    document.getElementById('regis-status').innerHTML = '🟡 Memproses deteksi wajah...';
    
    try {
        const detections = await faceapi.detectAllFaces(regisVideo, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptors();
        
        if (detections.length === 0) {
            alert('❌ Wajah tidak terdeteksi! Arahkan wajah ke kamera dengan posisi yang jelas');
            document.getElementById('regis-status').innerHTML = '🔴 Wajah tidak terdeteksi, coba lagi';
            document.getElementById('regis-status').style.color = '#f44336';
            return;
        }
        
        if (detections.length > 1) {
            alert('⚠️ Terdeteksi lebih dari satu wajah, pastikan hanya wajah Anda yang terlihat');
            return;
        }
        
        const faceDescriptor = Array.from(detections[0].descriptor);
        
        const response = await fetch(API_URL + 'registerFace', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id_karyawan: currentRegisId, 
                face_descriptor: faceDescriptor 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ ' + result.message);
            closeRegisModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('❌ ' + result.message);
            document.getElementById('regis-status').innerHTML = '🔴 Registrasi gagal: ' + result.message;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
        document.getElementById('regis-status').innerHTML = '🔴 Error: ' + error.message;
    }
}

// ==================== FACE RECOGNITION MODELS ====================
async function loadModels() {
    scanningStatus.innerHTML = '🟡 Memuat model face recognition...';
    try {
        const modelUrl = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl)
        ]);
        
        modelsLoaded = true;
        scanningStatus.innerHTML = '🟢 Model siap. Klik Mulai Kamera';
        console.log('Models loaded successfully');
    } catch (err) {
        console.error('Error loading models:', err);
        scanningStatus.innerHTML = '🔴 Gagal muat model: ' + err.message;
    }
}

// ==================== START CAMERA ====================
document.getElementById('start-camera-btn').addEventListener('click', async () => {
    if (!modelsLoaded) {
        scanningStatus.innerHTML = '🟡 Tunggu model selesai dimuat...';
        return;
    }
    
    try {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { width: { ideal: 640 }, height: { ideal: 480 } } 
        });
        
        video.srcObject = stream;
        
        await new Promise((resolve) => {
            video.onloadedmetadata = () => resolve();
        });
        
        await video.play();
        
        overlay.width = video.videoWidth;
        overlay.height = video.videoHeight;
        
        scanningStatus.innerHTML = '🟢 Kamera aktif - Scan wajah...';
        startDetection();
    } catch (err) {
        console.error('Camera error:', err);
        scanningStatus.innerHTML = '🔴 Gagal akses kamera: ' + err.message;
    }
});

document.getElementById('stop-camera-btn').addEventListener('click', () => {
    if (detectionInterval) clearInterval(detectionInterval);
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    scanningStatus.innerHTML = '🟡 Kamera dimatikan';
    document.getElementById('detected-info').style.display = 'none';
    document.getElementById('absensi-form').style.display = 'none';
});

// ==================== DETECTION LOOP ====================
function startDetection() {
    if (detectionInterval) clearInterval(detectionInterval);
    
    detectionInterval = setInterval(async () => {
        if (!modelsLoaded || !stream || !video.videoWidth) return;
        
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
                
                const faceDescriptor = Array.from(detections[0].descriptor);
                await recognizeFace(faceDescriptor);
                takeSnapshot();
            } else {
                document.getElementById('detected-info').style.display = 'none';
                document.getElementById('absensi-form').style.display = 'none';
            }
        } catch (error) {
            console.error('Detection error:', error);
        }
    }, 1500);
}

// ==================== RECOGNIZE FACE ====================
async function recognizeFace(faceDescriptor) {
    try {
        const response = await fetch(API_URL + 'recognizeFace', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ face_descriptors: faceDescriptor })
        });
        
        const result = await response.json();
        
        if (result.success && result.karyawan) {
            document.getElementById('detected-name').textContent = result.karyawan.nama;
            document.getElementById('detected-position').textContent = result.karyawan.jabatan;
            document.getElementById('recognized-id').value = result.karyawan.id;
            document.getElementById('detected-info').style.display = 'block';
            document.getElementById('absensi-form').style.display = 'block';
            scanningStatus.innerHTML = `✅ Dikenali: ${result.karyawan.nama}`;
        }
    } catch (error) {
        console.error('Recognition error:', error);
    }
}

function takeSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    document.getElementById('face-snapshot-data').value = canvas.toDataURL('image/jpeg', 0.8);
}

// ==================== SUBMIT ABSEN ====================
let currentAbsenType = 'masuk';

document.getElementById('btn-masuk').addEventListener('click', () => { 
    currentAbsenType = 'masuk'; 
    showMessage('Pilih proyek lalu klik Konfirmasi', 'info');
});

document.getElementById('btn-keluar').addEventListener('click', () => { 
    currentAbsenType = 'keluar'; 
    showMessage('Pilih proyek lalu klik Konfirmasi', 'info');
});

document.getElementById('submit-absen').addEventListener('click', async () => {
    const id_karyawan = document.getElementById('recognized-id').value;
    const id_proyek = document.getElementById('id_proyek').value;
    
    if (!id_karyawan) { 
        showMessage('Silakan scan wajah terlebih dahulu', 'error'); 
        return; 
    }
    if (!id_proyek) { 
        showMessage('Pilih proyek terlebih dahulu', 'error'); 
        return; 
    }
    
    const formData = new FormData();
    formData.append('id_karyawan', id_karyawan);
    formData.append('id_proyek', id_proyek);
    formData.append('absensi_type', currentAbsenType);
    formData.append('face_snapshot', document.getElementById('face-snapshot-data').value);
    
    const submitBtn = document.getElementById('submit-absen');
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Memproses...';
    
    try {
        const response = await fetch(API_URL + 'storeAbsensi', { 
            method: 'POST', 
            body: formData 
        });
        const result = await response.json();
        
        if (result.success) { 
            showMessage(result.message, 'success'); 
            setTimeout(() => location.reload(), 1500);
        } else { 
            showMessage(result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = '✅ Konfirmasi Absen';
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = '✅ Konfirmasi Absen';
    }
});

function showMessage(msg, type) {
    const div = document.getElementById('status-message');
    div.textContent = msg;
    div.style.display = 'block';
    div.style.background = type === 'success' ? '#e8f5e9' : type === 'error' ? '#ffebee' : '#e3f2fd';
    div.style.color = type === 'success' ? '#2e7d32' : type === 'error' ? '#c62828' : '#1565c0';
    div.style.padding = '10px';
    div.style.borderRadius = '8px';
    setTimeout(() => div.style.display = 'none', 3000);
}

// ==================== LOAD DATA ====================
async function loadTodayAbsensi() {
    try {
        const response = await fetch(API_URL + 'getTodayAbsensi');
        const result = await response.json();
        if (result.success) {
            const tbody = document.getElementById('absensi-today-body');
            tbody.innerHTML = '';
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center">Belum ada data absensi hari ini</td></tr>';
            } else {
                result.data.forEach((a, i) => {
                    let statusBadge = '';
                    if (a.status === 'hadir') statusBadge = 'badge-selesai';
                    else if (a.status === 'izin') statusBadge = 'badge-warning';
                    else statusBadge = 'badge-danger';
                    
                    tbody.innerHTML += `<tr>
                        <td>${i+1}</td>
                        <td>${a.nama_karyawan}</td>
                        <td>${a.jabatan}</td>
                        <td>${a.nama_proyek}</td>
                        <td>${a.jam_masuk || '-'}</td>
                        <td>${a.jam_keluar || '-'}</td>
                        <td><span class="badge ${statusBadge}">${a.status.toUpperCase()}</span></td>
                    </tr>`;
                });
            }
            document.getElementById('hadir-count').textContent = result.data.filter(a => a.status === 'hadir').length;
        }
    } catch(e) { 
        console.error(e);
        document.getElementById('absensi-today-body').innerHTML = '<tr><td colspan="7" style="text-align:center">Gagal memuat data</td></tr>';
    }
}

async function loadGajiData() {
    try {
        const response = await fetch(API_URL + 'getRekapGaji');
        const result = await response.json();
        if (result.success) {
            const tbody = document.getElementById('gaji-body');
            tbody.innerHTML = '';
            if (result.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center">Belum ada data gaji</tr>';
            } else {
                result.data.forEach((g, i) => {
                    tbody.innerHTML += `<tr>
                        <td>${i+1}</td>
                        <td>${g.nama}</td>
                        <td>${g.jabatan}</td>
                        <td>${g.total_hadir} hari</td>
                        <td>${g.total_lembur} jam</td>
                        <td>Rp ${parseInt(g.total_gaji).toLocaleString('id-ID')}</td>
                    </tr>`;
                });
            }
        }
    } catch(e) { console.error(e); }
}

// ==================== INIT ====================
loadModels();
loadTodayAbsensi();

document.getElementById('btn-export-excel').addEventListener('click', function() {
    const mulai = document.getElementById('filter_mulai').value;
    const selesai = document.getElementById('filter_selesai').value;
    const proyek_id = document.getElementById('filter_proyek').value;
    
    let url = API_URL + 'exportExcel&mulai=' + mulai + '&selesai=' + selesai;
    if (proyek_id) {
        url += '&proyek_id=' + proyek_id;
    }
    
    window.location.href = url;
});

</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>

