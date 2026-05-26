<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<style>
/* Tambahan style untuk face recognition */
.face-recognition-section {
    position: relative;
}

#video {
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    border: 3px solid #ddd;
    background: #000;
}

#overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
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

.detected-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn-camera {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    margin: 5px;
}

.btn-camera:hover {
    background: #45a049;
}

.btn-camera-stop {
    background: #dc3545;
}

.btn-camera-stop:hover {
    background: #c82333;
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

.face-pulse {
    animation: pulse 1s ease-out;
}

@keyframes pulse {
    0%, 100% { 
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
    }
    50% { 
        transform: scale(1.02);
        box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
    }
}
</style>

<div class="grid-2">
    <!-- FACE RECOGNITION SECTION -->
    <div class="card face-recognition-section">
        <div class="card-header">
            <span class="card-title">🎯 Absensi Face Recognition</span>
            <span style="font-size: 12px; color: #666;">Scan wajah langsung absen</span>
        </div>
        <div class="card-body">
            <!-- Video Container -->
            <div style="position: relative; display: flex; justify-content: center; margin-bottom: 20px;">
                <div style="position: relative;">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
                    <div id="scanning-status" class="scanning-status">
                        🔄 Menyiapkan kamera...
                    </div>
                </div>
            </div>
            
            <!-- Tombol Kontrol Kamera -->
            <div style="text-align: center; margin-bottom: 20px;">
                <button type="button" id="start-camera-btn" class="btn-camera" style="display: none;">🎥 Mulai Kamera</button>
                <button type="button" id="stop-camera-btn" class="btn-camera btn-camera-stop">⏹️ Stop Kamera</button>
            </div>
            
            <!-- Informasi Karyawan Terdeteksi -->
            <div id="detected-info" style="display: none;">
                <div class="detected-card face-pulse" style="padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 48px;">👤</div>
                        <div style="flex: 1;">
                            <h3 id="detected-name" style="margin: 0 0 5px 0;">-</h3>
                            <p id="detected-position" style="margin: 0; opacity: 0.9;">-</p>
                            <small id="detected-confidence" style="opacity: 0.8;">Confidence: -</small>
                        </div>
                        <div style="font-size: 40px;" id="status-icon">✅</div>
                    </div>
                </div>
            </div>
            
            <!-- Form Absensi Otomatis -->
            <div id="absensi-form-container" style="display: none; margin-top: 20px;">
                <input type="hidden" id="recognized-id">
                <input type="hidden" id="face-snapshot-data">
                
                <div class="form-grid">
                    <div class="form-group form-full">
                        <label>📍 Pilih Proyek *</label>
                        <select id="id_proyek" class="form-control" required>
                            <option value="">-- Pilih Proyek --</option>
                            <?php foreach ($proyekList as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_proyek']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>📋 Status</label>
                        <select id="status" class="form-control">
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="form-group form-full">
                        <label>📝 Keterangan (Opsional)</label>
                        <textarea id="keterangan" rows="2" class="form-control" placeholder="Contoh: Terlambat 15 menit, dll..."></textarea>
                    </div>
                </div>
                
                <button type="button" id="submit-absensi" class="btn btn-primary" style="width: 100%;">
                    ✅ Absen Sekarang
                </button>
            </div>
            
            <!-- Status Message -->
            <div id="status-message" style="margin-top: 16px; padding: 12px; border-radius: 8px; display: none;"></div>
        </div>
    </div>

    <!-- REKAP PER PROYEK -->
    <div class="card">
        <div class="card-header"><span class="card-title">📊 Rekap per Proyek (Bulan Ini)</span></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Proyek</th>
                        <th class="text-right">Pekerja</th>
                        <th class="text-right">Hadir</th>
                        <th class="text-right">Izin</th>
                        <th class="text-right">Sakit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekapList)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding:24px">Belum ada data</td>
                    </tr>
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

<!-- TABEL DAFTAR ABSENSI -->
<div class="card mt-4">
    <div class="card-header">
        <span class="card-title">📋 Daftar Absensi Hari Ini</span>
        <a href="<?= BASE_URL ?>/public/index.php?page=absensi&action=export" class="btn btn-sm btn-secondary">📥 Export</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th><th>Tanggal</th><th>Karyawan</th><th>Jabatan</th><th>Proyek</th><th>Status</th><th>Foto</th><th>Keterangan</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($absensiList)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding:40px">Belum ada data absensi hari ini</td>
                </tr>
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
                    <td>
                        <?php if (!empty($a['foto_absensi'])): ?>
                            <a href="<?= BASE_URL ?>/uploads/absensi/<?= $a['foto_absensi'] ?>" target="_blank">
                                <img src="<?= BASE_URL ?>/uploads/absensi/<?= $a['foto_absensi'] ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
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

<!-- Script Face Recognition -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
// ==================== FACE RECOGNITION SYSTEM ====================
let video = document.getElementById('video');
let overlay = document.getElementById('overlay');
let scanningStatus = document.getElementById('scanning-status');
let detectedInfo = document.getElementById('detected-info');
let absensiFormContainer = document.getElementById('absensi-form-container');
let startCameraBtn = document.getElementById('start-camera-btn');
let stopCameraBtn = document.getElementById('stop-camera-btn');
let submitBtn = document.getElementById('submit-absensi');
let statusMessageDiv = document.getElementById('status-message');

let stream = null;
let scanning = true;
let detectionInterval = null;
let modelsLoaded = false;

// Load Face API Models
async function loadModels() {
    showStatus('📦 Memuat model face recognition...', 'info');
    scanningStatus.innerHTML = '📦 Memuat model face recognition...';
    
    try {
        // Pastikan path models sesuai dengan lokasi folder models Anda
        await faceapi.nets.tinyFaceDetector.loadFromUri('/public/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/public/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/public/models');
        
        modelsLoaded = true;
        showStatus('✅ Model siap! Memulai kamera...', 'success');
        scanningStatus.innerHTML = '✅ Model siap! Memulai kamera...';
        
        startCamera();
    } catch (err) {
        console.error('Error loading models:', err);
        showStatus('❌ Gagal memuat model: ' + err.message, 'error');
        scanningStatus.innerHTML = '❌ Gagal memuat model. Cek folder /public/models';
    }
}

// Start Camera
async function startCamera() {
    try {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        
        stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        });
        
        video.srcObject = stream;
        
        await new Promise((resolve) => {
            video.onloadedmetadata = () => {
                resolve();
            };
        });
        
        await video.play();
        
        // Set overlay size
        overlay.width = video.videoWidth;
        overlay.height = video.videoHeight;
        
        scanningStatus.innerHTML = '🔍 Men-scan wajah... Arahkan wajah ke kamera';
        startDetection();
        
    } catch (err) {
        console.error('Camera error:', err);
        scanningStatus.innerHTML = '❌ Gagal mengakses kamera: ' + err.message;
        showStatus('❌ Gagal mengakses kamera. Pastikan izin kamera diberikan.', 'error');
    }
}

// Start Detection Loop
function startDetection() {
    if (detectionInterval) clearInterval(detectionInterval);
    
    detectionInterval = setInterval(async () => {
        if (!scanning || !modelsLoaded || !video.videoWidth) return;
        
        try {
            // Detect face with descriptors
            const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptors();
            
            // Clear overlay
            const ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            
            if (detections.length > 0) {
                // Draw bounding box
                const displaySize = { width: video.videoWidth, height: video.videoHeight };
                const resizedDetections = faceapi.resizeResults(detections, displaySize);
                faceapi.draw.drawDetections(overlay, resizedDetections);
                
                // Get face descriptor
                const faceDescriptor = Array.from(detections[0].descriptor);
                
                // Send to server for recognition
                await recognizeFace(faceDescriptor);
                
                scanningStatus.innerHTML = '✅ Wajah terdeteksi! Memverifikasi...';
            } else {
                scanningStatus.innerHTML = '🔍 Tidak ada wajah. Arahkan wajah ke kamera.';
                hideDetectedInfo();
            }
            
        } catch (error) {
            console.error('Detection error:', error);
        }
    }, 1000);
}

// Recognize Face via Backend API
async function recognizeFace(faceDescriptor) {
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=recognizeFace', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                face_descriptors: faceDescriptor
            })
        });
        
        const result = await response.json();
        
        if (result.success && result.karyawan) {
            showDetectedInfo(result.karyawan, result.match);
            takeFaceSnapshot();
        } else {
            hideDetectedInfo();
            scanningStatus.innerHTML = '❌ Wajah tidak dikenali. Hubungi admin untuk registrasi.';
        }
        
    } catch (error) {
        console.error('Recognition error:', error);
    }
}

// Take Snapshot for Evidence
function takeFaceSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    const snapshotData = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('face-snapshot-data').value = snapshotData;
}

// Show Detected Employee Info
function showDetectedInfo(karyawan, confidence) {
    document.getElementById('detected-name').textContent = karyawan.nama;
    document.getElementById('detected-position').textContent = karyawan.jabatan;
    document.getElementById('detected-confidence').textContent = `Confidence: ${Math.round(confidence * 100)}%`;
    document.getElementById('recognized-id').value = karyawan.id;
    
    detectedInfo.style.display = 'block';
    absensiFormContainer.style.display = 'block';
    
    // Animasi pulse
    const detectedCard = document.querySelector('.detected-card');
    if (detectedCard) {
        detectedCard.classList.remove('face-pulse');
        setTimeout(() => detectedCard.classList.add('face-pulse'), 10);
    }
    
    scanningStatus.innerHTML = `✅ Dikenali sebagai: ${karyawan.nama}`;
}

// Hide Detected Info
function hideDetectedInfo() {
    detectedInfo.style.display = 'none';
    absensiFormContainer.style.display = 'none';
    document.getElementById('recognized-id').value = '';
}

// Submit Absensi
submitBtn.addEventListener('click', async () => {
    const id_karyawan = document.getElementById('recognized-id').value;
    const id_proyek = document.getElementById('id_proyek').value;
    const status = document.getElementById('status').value;
    const keterangan = document.getElementById('keterangan').value;
    const faceSnapshot = document.getElementById('face-snapshot-data').value;
    
    if (!id_karyawan) {
        showStatus('⚠️ Wajah tidak terdeteksi. Silakan scan ulang.', 'error');
        return;
    }
    
    if (!id_proyek) {
        showStatus('⚠️ Silakan pilih proyek terlebih dahulu.', 'error');
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Menyimpan...';
    
    const formData = new FormData();
    formData.append('id_karyawan', id_karyawan);
    formData.append('id_proyek', id_proyek);
    formData.append('status', status);
    formData.append('keterangan', keterangan);
    formData.append('face_snapshot', faceSnapshot);
    
    try {
        const response = await fetch('<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showStatus('✅ ' + result.message, 'success');
            
            // Refresh page after 1.5 seconds
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showStatus('❌ ' + result.message, 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = '✅ Absen Sekarang';
        }
        
    } catch (error) {
        showStatus('❌ Error: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = '✅ Absen Sekarang';
    }
});

// Stop Camera
stopCameraBtn.addEventListener('click', () => {
    scanning = false;
    if (detectionInterval) clearInterval(detectionInterval);
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    scanningStatus.innerHTML = '⏹️ Kamera dimatikan';
    showStatus('Kamera dimatikan', 'info');
});

// Show Status Message
function showStatus(msg, type) {
    statusMessageDiv.textContent = msg;
    statusMessageDiv.style.display = 'block';
    statusMessageDiv.className = '';
    
    if (type === 'error') {
        statusMessageDiv.classList.add('status-error');
    } else if (type === 'success') {
        statusMessageDiv.classList.add('status-success');
    } else {
        statusMessageDiv.classList.add('status-info');
    }
    
    setTimeout(() => {
        if (statusMessageDiv) statusMessageDiv.style.display = 'none';
    }, 3000);
}

// Start the application
loadModels();
</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>