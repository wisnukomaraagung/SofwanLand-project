<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Jakarta');
require BASE_PATH . '/app/views/layouts/header.php';

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
    color: #ffffff;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #ffffff !important;
}

.stat-label {
    font-size: 11px;
    margin-top: 5px;
    color: #ffffff !important;
    font-weight: 700;
    opacity: 1;
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

/* ==================== REKAP ABSENSI ==================== */
.rekap-filter {
    display: flex;
    gap: 12px;
    align-items: end;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.rekap-filter .form-group {
    margin-bottom: 0;
    min-width: 170px;
}

.rekap-filter label {
    font-size: 12px;
    font-weight: 600;
}

.rekap-summary {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.rekap-summary-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}

.rekap-summary-number {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.rekap-summary-label {
    color: #6b7280;
    font-size: 11px;
    margin-top: 5px;
}

.rekap-period {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 15px;
}

.rekap-table th {
    white-space: nowrap;
}

.rekap-table td {
    vertical-align: middle;
}

.badge-hadir {
    background: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-izin {
    background: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-sakit {
    background: #f8d7da;
    color: #721c24;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

@media (max-width: 900px) {
    .rekap-summary {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .rekap-summary {
        grid-template-columns: 1fr;
    }

    .rekap-filter .form-group {
        width: 100%;
    }
}

</style>

<?php if (!$globalProjectId): ?>
<div class="card text-center" style="padding: 40px; margin: 20px auto; max-width: 600px;">
    <div style="font-size: 48px; margin-bottom: 20px;"></div>
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
        <h1 id="detail-nama-proyek"></h1>
        <p id="detail-lokasi"></p>
    </div>

    <div class="grid-3" style="margin-bottom: 24px;">
        <div class="stat-card" style="background: linear-gradient(135deg, #000000 0%, #090909 100%);"><div class="stat-number" id="stat-karyawan">0</div><div class="stat-label">Karyawan</div></div>
        <div class="stat-card" style="background: linear-gradient(135deg, #000000 0%, #090909 100%);"><div class="stat-number" id="stat-hadir">0</div><div class="stat-label">Hadir Bulan Ini</div></div>
        <div class="stat-card" style="background: linear-gradient(135deg, #070707 0%, #0a0a0a 100%);"><div class="stat-number" id="stat-gaji">Rp 0</div><div class="stat-label">Total Gaji Bulan Ini</div></div>
    </div>

    <div id="live-clock-box" style="text-align:center;margin-bottom:16px;padding:12px;background:#fff;border-radius:12px;border:1px solid #e5e7eb;">
        <div id="live-date" style="font-size:13px;font-weight:600;color:#555;"></div>
        <div id="live-clock" style="font-size:36px;font-weight:700;color:#111;letter-spacing:2px;">00:00:00</div>
    </div>

    <div class="tab-absensi">
        <button class="tab-absensi-btn active" data-tab="karyawan"> Daftar Karyawan</button>
        <button class="tab-absensi-btn" data-tab="absensi"> Riwayat Absensi</button>
        <button class="tab-absensi-btn" data-tab="gaji"> Monitoring Gaji</button>
        <button class="tab-absensi-btn" data-tab="face">Absen</button>
    </div>

    <!-- Tab Karyawan -->
    <div id="tab-karyawan" class="tab-pane active">
        <div class="card">
            <div class="card-header">
                <span> Daftar Karyawan</span>
                <button class="btn btn-primary btn-sm" onclick="openTambahKaryawan()">+ Tambah Karyawan</button>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>No</th><th>NIK</th><th>Nama</th><th>Jabatan</th><th>Gaji Pokok</th><th>Status Wajah</th><th>Aksi</th></tr></thead>
                    <tbody id="karyawan-tbody"><tr><td colspan="8" class="text-center">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Riwayat Absensi (ADMIN bisa edit jam keluar) -->
    <div id="tab-absensi" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <span> Riwayat Absensi</span>
                <button class="btn btn-secondary btn-sm" onclick="exportExcel()"> Export Excel</button>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>No</th><th>Tanggal</th><th>Karyawan</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Lembur</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody id="absensi-tbody"><tr><td colspan="8" class="text-center">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Tab Rekapitulasi Absensi -->
    <div id="tab-rekap" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <span> Rekapitulasi Absensi</span>
                <button class="btn btn-secondary btn-sm" onclick="exportRekapCSV()"> Export Rekap</button>
            </div>

            <div class="card-body">
                <div class="rekap-filter">
                    <div class="form-group">
                        <label for="rekap-start">Tanggal Mulai</label>
                        <input type="date" id="rekap-start" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="rekap-end">Tanggal Selesai</label>
                        <input type="date" id="rekap-end" class="form-control">
                    </div>

                    <button class="btn btn-primary" onclick="terapkanFilterRekap()">
                         Tampilkan Rekap
                    </button>

                    <button class="btn btn-secondary" onclick="setPeriode14Hari()">
                         14 Hari Terakhir
                    </button>
                </div>

                <div id="rekap-period" class="rekap-period">
                    Periode belum dipilih
                </div>

                <div class="rekap-summary">
                    <div class="rekap-summary-card">
                        <div class="rekap-summary-number" id="rekap-total-karyawan">0</div>
                        <div class="rekap-summary-label">Karyawan</div>
                    </div>
                    <div class="rekap-summary-card">
                        <div class="rekap-summary-number" id="rekap-total-hadir">0</div>
                        <div class="rekap-summary-label">Hadir</div>
                    </div>
                    <div class="rekap-summary-card">
                        <div class="rekap-summary-number" id="rekap-total-izin">0</div>
                        <div class="rekap-summary-label">Izin</div>
                    </div>
                    <div class="rekap-summary-card">
                        <div class="rekap-summary-number" id="rekap-total-sakit">0</div>
                        <div class="rekap-summary-label">Sakit</div>
                    </div>
                    <div class="rekap-summary-card">
                        <div class="rekap-summary-number" id="rekap-total-lembur">0</div>
                        <div class="rekap-summary-label">Total Lembur (Jam)</div>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table rekap-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>Nama Karyawan</th>
                                <th>Hadir</th>
                                <th>Izin</th>
                                <th>Sakit</th>
                                <th>Total Hari Tercatat</th>
                                <th>Total Lembur</th>
                                <th>Persentase Hadir</th>
                            </tr>
                        </thead>
                        <tbody id="rekap-tbody">
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data rekap</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Monitoring Data Gaji Berdasarkan Kehadiran -->
<div id="tab-gaji" class="tab-pane">

    <div class="card">

        <div class="card-header">
            <span>Monitoring Data Gaji Berdasarkan Kehadiran</span>
        </div>

        <div class="card-body">

            <div style="
                display:flex;
                gap:12px;
                align-items:end;
                flex-wrap:wrap;
                margin-bottom:20px;
            ">

                <div>
                    <label>Tanggal Mulai</label>
                    <input type="date"
                           id="gaji-start"
                           class="form-control">
                </div>

                <div>
                    <label>Tanggal Selesai</label>
                    <input type="date"
                           id="gaji-end"
                           class="form-control">
                </div>

                <button class="btn btn-primary"
                        onclick="tampilkanMonitoringGaji()">
                    Tampilkan
                </button>

                <button class="btn btn-secondary"
                        onclick="periodeGaji14Hari()">
                    14 Hari Terakhir
                </button>

            </div>

            <div id="gaji-period"
                 style="margin-bottom:15px;color:#777;">
            </div>

            <div style="
                display:grid;
                grid-template-columns:repeat(4,1fr);
                gap:12px;
                margin-bottom:20px;
            ">

                <div class="stat-card">
                    <div class="stat-number"
                         id="gaji-jumlah-karyawan">0</div>
                    <div class="stat-label">
                        Karyawan
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"
                         id="gaji-jumlah-hadir">0</div>
                    <div class="stat-label">
                        Total Hadir
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"
                         id="gaji-jumlah-lembur">0</div>
                    <div class="stat-label">
                        Total Lembur
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-number"
                         id="gaji-total">Rp 0</div>
                    <div class="stat-label">
                        Total Gaji
                    </div>
                </div>

            </div>

            <div class="table-wrap">

                <table class="table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIK</th>
                            <th>Nama Karyawan</th>
                            <th>Jabatan</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Lembur</th>
                            <th>Gaji Pokok</th>
                            <th>Total Gaji</th>
                        </tr>
                    </thead>

                    <tbody id="gaji-tbody">

                        <tr>
                            <td colspan="10"
                                class="text-center">
                                Pilih periode terlebih dahulu
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div style="
                margin-top:12px;
                font-size:12px;
                color:#777;
            ">
                * Data gaji yang ditampilkan merupakan
                gaji pokok yang telah tersimpan pada data
                karyawan. Sistem hanya menghubungkan data
                gaji dengan informasi kehadiran pada periode
                yang dipilih.
            </div>

        </div>

    </div>

</div>

    <!-- Tab Face Recognition Absen (Hanya Absen Masuk) -->
    <div id="tab-face" class="tab-pane">
        <div class="card">
            <div class="card-header">
                <span>Absen</span>
                <span style="font-size: 12px; margin-left: 15px;"> Absen Masuk (Otomatis/Manual) | ⚠️ Absen Keluar hanya oleh ADMIN</span>
            </div>
            <div class="card-body">
                <input type="hidden" id="current-proyek-id" value="">
                
<div style="position: relative; display: flex; justify-content: center; margin-bottom: 20px;">
                    <div style="position: relative;">
                        <video id="video" autoplay muted playsinline style="width: 100%; max-width: 500px; border-radius: 12px; background: #000; transform: scaleX(-1);"></video>
                        <canvas id="overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></canvas>
                        <div id="scan-status" style="position: absolute; bottom: 10px; left: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 8px; border-radius: 8px; text-align: center; font-size: 12px;">🔄 Menyiapkan...</div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button id="start-camera" class="btn-camera"> Mulai Kamera</button>
                    <button id="stop-camera" class="btn-camera btn-camera-stop">Stop Kamera</button>
                    <button id="btnAbsenKeluar" class="btn btn-danger">
     Absen Keluar
</button>
                </div>
                
                <div id="detected-info" style="display: none; margin-top: 20px;">
                    <div class="detected-card">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="font-size: 40px;"></span>
                            <div><h3 id="detected-name" style="margin: 0;">-</h3><p id="detected-position" style="margin: 5px 0 0;">-</p></div>
                        </div>
                    </div>
                </div>
                
                <div id="absen-form" style="display: none; margin-top: 20px;">
                    <input type="hidden" id="karyawan-id">
                    <input type="hidden" id="face-snapshot">
                    
                    <div class="form-group">
                        <label> Absen Masuk</label>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" id="btn-manual-masuk" class="btn-camera btn-manual" style="flex:1;"> Absen Masuk MANUAL</button>
                        </div>
                        <small> Atau biarkan wajah terdeteksi untuk ABSEN OTOMATIS</small>
                    </div>
                    
                    <div class="form-group">
                        <label> Status</label>
                        <select id="status" class="form-control">
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label> Keterangan</label>
                        <textarea id="keterangan" rows="2" class="form-control" placeholder="Contoh: Terlambat..."></textarea>
                    </div>
                    
                    <button id="submit-absen" class="btn btn-primary" style="width: 100%;" disabled> Konfirmasi Absen Masuk</button>
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
            <button type="submit" class="btn btn-primary" style="width:100%"> Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- Modal Register Wajah -->
<div id="modal-register-face" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header"><span> Register Wajah</span><span class="close" onclick="closeModalRegister()">&times;</span></div>
        <div class="modal-body">
            <p>Karyawan: <strong id="register-nama"></strong></p>
            <input type="hidden" id="register-karyawan-id">
            <video id="register-video" autoplay muted playsinline style="width:100%; border-radius:8px;"></video>
            <button id="capture-face" class="btn btn-primary" style="margin-top:15px; width:100%"> Ambil & Register Wajah</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<script>
/* ============================================================
   VARIABLES
============================================================ */

let currentProyekId = null;
let currentKaryawanId = null;
let currentKaryawan = null;

let stream = null;
let registerStream = null;
let registerVideo = null;

let detectionInterval = null;

let modelsLoaded = false;
let lastAutoAbsenTime = 0;

const AUTO_ABSEN_COOLDOWN = 60000;


/* ============================================================
   ELEMENT
============================================================ */

const video = document.getElementById('video');
const overlay = document.getElementById('overlay');
const scanStatus = document.getElementById('scan-status');

function pilihProyek(proyekId) {

    if (!proyekId) {
        console.error('ID proyek tidak tersedia');
        return;
    }

    currentProyekId = proyekId;

    // Simpan ID proyek
    const inputProyek = document.getElementById('current-proyek-id');

    if (inputProyek) {
        inputProyek.value = proyekId;
    }

    // =========================
    // TAMPILKAN DETAIL PROYEK
    // =========================
    const daftarProyek =
        document.getElementById('daftar-proyek-container');

    const detailProyek =
        document.getElementById('detail-container');

    if (daftarProyek) {
        daftarProyek.style.display = 'none';
    }

    if (detailProyek) {
        detailProyek.classList.add('active');
        detailProyek.style.display = 'block';
    }

    console.log('Proyek dipilih:', currentProyekId);

    // =========================
    // LOAD DATA
    // =========================
    loadKaryawan(currentProyekId);
    loadAbsensi(currentProyekId);
    loadStatistik(currentProyekId);

}


/* ============================================================
   LOAD PROYEK
============================================================ */

function loadProyek() {

    fetch(
        '<?= BASE_URL ?>/public/index.php?page=api&action=getAllProyek'
    )
    .then(response => {

        if (!response.ok) {
            throw new Error(
                'HTTP Error: ' + response.status
            );
        }

        return response.json();
    })
    .then(data => {

        console.log(
            'DATA PROYEK:',
            data
        );

        if (!data.success) {
            console.error(
                'Gagal mengambil proyek:',
                data.message
            );

            return;
        }

        const select =
            document.getElementById('proyek');

        if (!select) return;

        select.innerHTML =
            '<option value="">-- Pilih Proyek --</option>';

        const proyekList =
            Array.isArray(data.data)
                ? data.data
                : [];

        proyekList.forEach(proyek => {

            const option =
                document.createElement('option');

            option.value = proyek.id;
            option.textContent =
                proyek.nama_proyek ||
                proyek.nama ||
                ('Proyek #' + proyek.id);

            select.appendChild(option);
        });

        select.addEventListener(
            'change',
            function () {

                if (this.value) {
                    pilihProyek(this.value);
                }
            }
        );
    })
    .catch(error => {

        console.error(
            'ERROR LOAD PROYEK:',
            error
        );
    });
}


/* ============================================================
   LOAD KARYAWAN
============================================================ */

function loadKaryawan(proyekId) {

    if (!proyekId) {
        console.error(
            'ID proyek tidak tersedia'
        );

        return;
    }

    fetch(
        `<?= BASE_URL ?>/public/index.php?page=api&action=getKaryawanByProyek&id_proyek=${proyekId}`
    )
    .then(response => {

        if (!response.ok) {
            throw new Error(
                'HTTP Error: ' + response.status
            );
        }

        return response.json();
    })
    .then(data => {

        console.log(
            'DATA KARYAWAN:',
            data
        );

        const tbody =
            document.getElementById(
                'karyawan-tbody'
            );

        if (!tbody) return;

        // Update jumlah karyawan pada kartu statistik
const statKaryawan = document.getElementById('stat-karyawan');

if (statKaryawan) {
    statKaryawan.textContent = Array.isArray(data.data)
        ? data.data.length
        : 0;
}

        if (
            !data.success ||
            !Array.isArray(data.data) ||
            data.data.length === 0
        ) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="text-center">
                        Belum ada data karyawan
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML = data.data.map((karyawan, index) => {

    return `
        <tr>
            <td>${index + 1}</td>

            <td>${karyawan.nik || '-'}</td>

            <td>
                <strong>${karyawan.nama || '-'}</strong>
            </td>

            <td>${karyawan.jabatan || '-'}</td>

            <td>
                Rp ${Number(karyawan.gaji_pokok || 0)
                    .toLocaleString('id-ID')}
            </td>

            <td>${karyawan.no_telp || '-'}</td>

            <td>
                ${
                    karyawan.face_descriptor
                        ? '✅ Terdaftar'
                        : '❌ Belum'
                }
            </td>

            <td>
                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="registerFace(
                        ${karyawan.id},
                        '${String(karyawan.nama || '')
                            .replace(/'/g, "\\'")}'
                    )">
                    Register Wajah
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="hapusKaryawan(${karyawan.id})">
                    Hapus
                </button>
            </td>
        </tr>
    `;

}).join('');
    })
    .catch(error => {

        console.error(
            'ERROR LOAD KARYAWAN:',
            error
        );

        const tbody =
            document.getElementById(
                'karyawan-tbody'
            );

        if (tbody) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="text-center">
                        Gagal memuat data karyawan
                    </td>
                </tr>
            `;
        }
    });
}


/* ============================================================
   TAMBAH KARYAWAN
============================================================ */

function openTambahKaryawan() {

    const modal =
        document.getElementById(
            'modal-karyawan'
        );

    if (modal) {
        modal.style.display = 'flex';
    }
}


function closeModal() {

    const modal =
        document.getElementById(
            'modal-karyawan'
        );

    if (modal) {
        modal.style.display = 'none';
    }
}


const formKaryawan =
    document.getElementById(
        'form-karyawan'
    );

if (formKaryawan) {

    formKaryawan.addEventListener(
        'submit',
        function(e) {

            e.preventDefault();

            if (!currentProyekId) {

                alert(
                    'Pilih proyek terlebih dahulu.'
                );

                return;
            }

            const formData =
                new FormData();

            formData.append(
                'nik',
                document.getElementById(
                    'nik'
                ).value
            );

            formData.append(
                'nama',
                document.getElementById(
                    'nama'
                ).value
            );

            formData.append(
                'jabatan',
                document.getElementById(
                    'jabatan'
                ).value
            );

            formData.append(
                'gaji_pokok',
                document.getElementById(
                    'gaji_pokok'
                ).value
            );

            formData.append(
                'no_telp',
                document.getElementById(
                    'no_telp'
                ).value
            );

            formData.append(
                'id_proyek',
                currentProyekId
            );

            fetch(
                '<?= BASE_URL ?>/public/index.php?page=api&action=addKaryawan',
                {
                    method: 'POST',
                    body: formData
                }
            )
            .then(response => response.json())
            .then(data => {

                alert(
                    data.message
                );

                if (data.success) {

                    closeModal();

                    formKaryawan.reset();

                    loadKaryawan(
                        currentProyekId
                    );

                    loadStatistik(
                        currentProyekId
                    );
                }
            })
            .catch(error => {

                console.error(
                    'ERROR TAMBAH KARYAWAN:',
                    error
                );

                alert(
                    'Terjadi kesalahan saat menambahkan karyawan.'
                );
            });
        }
    );
}


/* ============================================================
   HAPUS KARYAWAN
============================================================ */

function hapusKaryawan(id) {

    if (
        !confirm(
            'Yakin ingin menghapus karyawan ini?'
        )
    ) {
        return;
    }

    fetch(
        `<?= BASE_URL ?>/public/index.php?page=api&action=deleteKaryawan&id=${id}`
    )
    .then(response => response.json())
    .then(data => {

        alert(
            data.message
        );

        if (data.success) {

            loadKaryawan(
                currentProyekId
            );

            loadStatistik(
                currentProyekId
            );
        }
    })
    .catch(error => {

        console.error(
            'ERROR HAPUS KARYAWAN:',
            error
        );

        alert(
            'Terjadi kesalahan saat menghapus karyawan.'
        );
    });
}


/* ============================================================
   LOAD STATISTIK
============================================================ */

function loadStatistik(proyekId) {

    if (!proyekId) {

        console.error(
            'ID proyek tidak tersedia'
        );

        return;
    }

    fetch(
        `<?= BASE_URL ?>/public/index.php?page=api&action=getStatistikProyek&id_proyek=${proyekId}`
    )
    .then(response => {

        if (!response.ok) {
            throw new Error(
                'HTTP Error: ' + response.status
            );
        }

        return response.json();
    })
    .then(data => {

        console.log(
            'STATISTIK:',
            data
        );

        if (
            data.success &&
            data.data
        ) {

            const statKaryawan =
                document.getElementById(
                    'stat-karyawan'
                );

            const statHadir =
                document.getElementById(
                    'stat-hadir'
                );

            const statGaji =
                document.getElementById(
                    'stat-gaji'
                );

            if (statKaryawan) {
                statKaryawan.textContent =
                    Number(
                        data.data.total_karyawan || 0
                    );
            }

            if (statHadir) {
                statHadir.textContent =
                    Number(
                        data.data.total_hadir || 0
                    );
            }

            if (statGaji) {
                const gajiVal = Math.round(Number(data.data.total_gaji || 0));
                statGaji.textContent = 'Rp ' + gajiVal.toLocaleString('id-ID');
            }

        } else {

            console.error(
                'Gagal mengambil statistik:',
                data.message
            );
        }
    })
    .catch(error => {

        console.error(
            'ERROR LOAD STATISTIK:',
            error
        );
    });
}


/* ============================================================
   LOAD ABSENSI
============================================================ */

function loadAbsensi(proyekId) {

    if (!proyekId) {
        return;
    }

    fetch(
        `<?= BASE_URL ?>/public/index.php?page=api&action=getAbsensiByProyek&id_proyek=${proyekId}`
    )
    .then(response => {

        if (!response.ok) {
            throw new Error(
                'HTTP Error: ' + response.status
            );
        }

        return response.json();
    })
    .then(data => {

        console.log(
            'DATA ABSENSI:',
            data
        );

        const tbody =
            document.getElementById(
                'absensi-tbody'
            );

        if (!tbody) return;

        if (
            !data.success ||
            !Array.isArray(data.data) ||
            data.data.length === 0
        ) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="text-center">
                        Belum ada data absensi
                    </td>
                </tr>
            `;

            return;
        }

        tbody.innerHTML =
            data.data.map(
                (item, index) => {

                    return `
                        <tr>

                            <td>
                                ${index + 1}
                            </td>

                            <td>
                                ${item.tanggal || '-'}
                            </td>

                            <td>
                                ${item.nama_karyawan || '-'}
                            </td>

                            <td>
                                ${item.jam_masuk || '-'}
                            </td>

                            <td>
                                ${item.jam_keluar || '-'}
                            </td>

                            <td>
                                ${item.status || '-'}
                            </td>

                            <td>
                                ${item.keterangan || '-'}
                            </td>

                            <td>
                                ${
                                    item.lembur_jam || 0
                                } jam
                            </td>

                        </tr>
                    `;
                }
            ).join('');
    })
    .catch(error => {

        console.error(
            'ERROR LOAD ABSENSI:',
            error
        );

        const tbody =
            document.getElementById(
                'absensi-tbody'
            );

        if (tbody) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="text-center">
                        Gagal memuat data absensi
                    </td>
                </tr>
            `;
        }
    });
}


/* ============================================================
   LOAD MODEL FACE RECOGNITION
============================================================ */

async function loadModels() {

    if (!scanStatus) {
        return;
    }

    scanStatus.innerHTML =
        '📦 Memuat model face recognition...';

    const modelUrls = [

        'https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/',

        'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights'
    ];

    for (
        const url of modelUrls
    ) {

        try {

            await Promise.all([

                faceapi.nets.tinyFaceDetector
                    .loadFromUri(url),

                faceapi.nets.faceLandmark68Net
                    .loadFromUri(url),

                faceapi.nets.faceRecognitionNet
                    .loadFromUri(url)
            ]);

            modelsLoaded = true;

            scanStatus.innerHTML =
                '✅ Model siap - Klik Mulai Kamera';

            console.log(
                'Model berhasil dimuat dari:',
                url
            );

            return;

        } catch (error) {

            console.warn(
                'Gagal memuat model dari:',
                url,
                error
            );
        }
    }

    scanStatus.innerHTML =
        '❌ Gagal memuat model face recognition';
}


/* ============================================================
   START CAMERA
============================================================ */

const startCameraButton =
    document.getElementById(
        'start-camera'
    );

if (startCameraButton) {

    startCameraButton.addEventListener(
        'click',
        async function() {

            if (!modelsLoaded) {

                scanStatus.innerHTML =
                    '⏳ Model belum siap...';

                return;
            }

            try {

                if (stream) {

                    stream
                        .getTracks()
                        .forEach(
                            track =>
                                track.stop()
                        );
                }

                stream =
                    await navigator
                        .mediaDevices
                        .getUserMedia({

                            video: {
                                width: 640,
                                height: 480,
                                facingMode: 'user'
                            },

                            audio: false
                        });

                video.srcObject =
                    stream;

                await new Promise(
                    resolve => {

                        video.onloadedmetadata =
                            resolve;
                    }
                );

                await video.play();

                overlay.width =
                    video.videoWidth;

                overlay.height =
                    video.videoHeight;

                scanStatus.innerHTML =
                    '🟢 Kamera aktif - Scan wajah';

                startDetection();

            } catch (error) {

                console.error(
                    'ERROR KAMERA:',
                    error
                );

                let errorMsg =
                    error.message;

                if (
                    error.name ===
                    'NotAllowedError'
                ) {

                    errorMsg =
                        'Izin kamera ditolak. Izinkan akses kamera pada browser.';
                }

                scanStatus.innerHTML =
                    '❌ ' + errorMsg;

                alert(errorMsg);
            }
        }
    );
}


/* ============================================================
   STOP CAMERA
============================================================ */

const stopCameraButton =
    document.getElementById(
        'stop-camera'
    );

if (stopCameraButton) {

    stopCameraButton.addEventListener(
        'click',
        function() {

            if (detectionInterval) {

                clearInterval(
                    detectionInterval
                );

                detectionInterval = null;
            }

            if (stream) {

                stream
                    .getTracks()
                    .forEach(
                        track =>
                            track.stop()
                    );

                stream = null;
            }

            if (video) {
                video.srcObject = null;
            }

            if (scanStatus) {

                scanStatus.innerHTML =
                    '⏹️ Kamera dimatikan';
            }

            const detectedInfo =
                document.getElementById(
                    'detected-info'
                );

            const absenForm =
                document.getElementById(
                    'absen-form'
                );

            if (detectedInfo) {
                detectedInfo.style.display =
                    'none';
            }

            if (absenForm) {
                absenForm.style.display =
                    'none';
            }

            currentKaryawanId = null;
            currentKaryawan = null;
        }
    );
}


/* ============================================================
   FACE DETECTION
============================================================ */

function startDetection() {

    if (detectionInterval) {

        clearInterval(
            detectionInterval
        );
    }

    detectionInterval =
        setInterval(
            async function() {

                try {

                    if (
                        !modelsLoaded ||
                        !stream ||
                        !video ||
                        !video.videoWidth
                    ) {
                        return;
                    }

                    const detections =
                        await faceapi
                            .detectAllFaces(
                                video,
                                new faceapi
                                    .TinyFaceDetectorOptions()
                            )
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                    const ctx =
                        overlay.getContext(
                            '2d'
                        );

                    ctx.clearRect(
                        0,
                        0,
                        overlay.width,
                        overlay.height
                    );

                    if (
                        detections.length === 0
                    ) {

                        return;
                    }

                    const resized =
                        faceapi.resizeResults(
                            detections,
                            {
                                width:
                                    video.videoWidth,

                                height:
                                    video.videoHeight
                            }
                        );

                    faceapi.draw.drawDetections(
                        overlay,
                        resized
                    );

                    const descriptor =
                        Array.from(
                            detections[0].descriptor
                        );

                    await recognizeFace(
                        descriptor
                    );

                    takeSnapshot();

                } catch (error) {

                    console.error(
                        'ERROR FACE DETECTION:',
                        error
                    );
                }

            },
            1000
        );
}


/* ============================================================
   RECOGNIZE FACE
============================================================ */

async function recognizeFace(
    faceDescriptor
) {

    try {

        const response =
            await fetch(
                '<?= BASE_URL ?>/public/index.php?page=api&action=recognizeFace',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    body: JSON.stringify({
                        face_descriptors:
                            faceDescriptor
                    })
                }
            );

        if (!response.ok) {

            throw new Error(
                'HTTP Error: ' +
                response.status
            );
        }

        const result =
            await response.json();

        console.log(
            'HASIL FACE RECOGNITION:',
            result
        );

        if (
            !result.success ||
            !result.karyawan
        ) {

            scanStatus.innerHTML =
                '❌ Wajah tidak dikenali';

            return;
        }


        /* ----------------------------------------------------
           SIMPAN DATA KARYAWAN TERLEBIH DAHULU
        ---------------------------------------------------- */

        currentKaryawan =
            result.karyawan;

        currentKaryawanId =
            result.karyawan.id;


        /* ----------------------------------------------------
           TAMPILKAN DATA KARYAWAN
        ---------------------------------------------------- */

        const detectedName =
            document.getElementById(
                'detected-name'
            );

        const detectedPosition =
            document.getElementById(
                'detected-position'
            );

        const karyawanId =
            document.getElementById(
                'karyawan-id'
            );

        const detectedInfo =
            document.getElementById(
                'detected-info'
            );

        const absenForm =
            document.getElementById(
                'absen-form'
            );

        const submitAbsen =
            document.getElementById(
                'submit-absen'
            );


        if (detectedName) {

            detectedName.textContent =
                result.karyawan.nama;
        }

        if (detectedPosition) {

            detectedPosition.textContent =
                result.karyawan.jabatan ||
                '-';
        }

        if (karyawanId) {

            karyawanId.value =
                result.karyawan.id;
        }

        if (detectedInfo) {

            detectedInfo.style.display =
                'block';
        }

        if (absenForm) {

            absenForm.style.display =
                'block';
        }

        if (submitAbsen) {

            submitAbsen.disabled =
                false;
        }

        scanStatus.innerHTML =
            `✅ Dikenali: ${result.karyawan.nama}`;


        /* ----------------------------------------------------
           AMBIL FOTO
        ---------------------------------------------------- */

        takeSnapshot();


        /* ----------------------------------------------------
           ABSEN MASUK OTOMATIS
        ---------------------------------------------------- */

        await autoAbsenMasuk(
            result.karyawan.id
        );

    } catch (error) {

        console.error(
            'ERROR RECOGNIZE FACE:',
            error
        );
    }
}


/* ============================================================
   SNAPSHOT
============================================================ */

function takeSnapshot() {

    if (
        !video ||
        !video.videoWidth ||
        !video.videoHeight
    ) {
        return;
    }

    const canvas =
        document.createElement(
            'canvas'
        );

    canvas.width =
        video.videoWidth;

    canvas.height =
        video.videoHeight;

    const ctx =
        canvas.getContext('2d');

    ctx.drawImage(
        video,
        0,
        0,
        canvas.width,
        canvas.height
    );

    const snapshot =
        document.getElementById(
            'face-snapshot'
        );

    if (snapshot) {

        snapshot.value =
            canvas.toDataURL(
                'image/jpeg',
                0.8
            );
    }
}


/* ============================================================
   AUTO ABSEN MASUK
============================================================ */

async function autoAbsenMasuk(
    id_karyawan
) {

    const proyekInput =
        document.getElementById(
            'current-proyek-id'
        );

    const id_proyek =
        proyekInput
            ? proyekInput.value
            : currentProyekId;

    if (!id_proyek) {

        showMessage(
            'Pilih proyek terlebih dahulu',
            'info'
        );

        return;
    }

    const now =
        Date.now();

    if (
        now - lastAutoAbsenTime <
        AUTO_ABSEN_COOLDOWN
    ) {

        return;
    }

    const formData =
        new FormData();

    formData.append(
        'id_karyawan',
        id_karyawan
    );

    formData.append(
        'id_proyek',
        id_proyek
    );

    formData.append(
        'absensi_type',
        'masuk'
    );

    formData.append(
        'status',
        'hadir'
    );

    formData.append(
        'keterangan',
        'Absen masuk otomatis'
    );

    formData.append(
        'lembur_jam',
        0
    );

    const snapshot =
        document.getElementById(
            'face-snapshot'
        );

    formData.append(
        'face_snapshot',
        snapshot
            ? snapshot.value
            : ''
    );

    try {

        const response =
            await fetch(
                '<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi',
                {
                    method: 'POST',
                    body: formData
                }
            );

        if (!response.ok) {

            throw new Error(
                'HTTP Error: ' +
                response.status
            );
        }

        const result =
            await response.json();

        console.log(
            'HASIL ABSEN MASUK:',
            result
        );

        if (result.success) {

            lastAutoAbsenTime =
                now;

            showMessage(
                '✅ Absen Masuk Otomatis Berhasil!',
                'success'
            );

            loadStatistik(
                currentProyekId
            );

            loadAbsensi(
                currentProyekId
            );

        } else {

            if (
                !String(
                    result.message || ''
                ).toLowerCase()
                .includes(
                    'sudah absen masuk'
                )
            ) {

                console.log(
                    result.message
                );
            }
        }

    } catch (error) {

        console.error(
            'ERROR AUTO ABSEN MASUK:',
            error
        );
    }
}


/* ============================================================
   ABSEN KELUAR
============================================================ */

async function absenKeluar() {

    if (!currentKaryawanId) {

        showMessage(
            'Scan wajah terlebih dahulu',
            'error'
        );

        return;
    }

    if (!currentProyekId) {

        showMessage(
            'Proyek belum dipilih',
            'error'
        );

        return;
    }

    const formData =
        new FormData();

    formData.append(
        'id_karyawan',
        currentKaryawanId
    );

    formData.append(
        'id_proyek',
        currentProyekId
    );

    formData.append(
        'absensi_type',
        'keluar'
    );

    try {

        const response =
            await fetch(
                '<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi',
                {
                    method: 'POST',
                    body: formData
                }
            );

        if (!response.ok) {

            throw new Error(
                'HTTP Error: ' +
                response.status
            );
        }

        const result =
            await response.json();

        console.log(
            'HASIL ABSEN KELUAR:',
            result
        );

        if (result.success) {

            showMessage(
                '✅ Absen Keluar Berhasil!',
                'success'
            );

            loadAbsensi(
                currentProyekId
            );

            loadStatistik(
                currentProyekId
            );

        } else {

            showMessage(
                result.message ||
                'Absen keluar gagal',
                'error'
            );
        }

    } catch (error) {

        console.error(
            'ERROR ABSEN KELUAR:',
            error
        );

        showMessage(
            'Terjadi kesalahan saat absen keluar',
            'error'
        );
    }
}


/* ============================================================
   BUTTON ABSEN KELUAR
============================================================ */

const btnAbsenKeluar =
    document.getElementById(
        'btnAbsenKeluar'
    );

if (btnAbsenKeluar) {

    btnAbsenKeluar.addEventListener(
        'click',
        absenKeluar
    );
}


/* ============================================================
   MANUAL ABSEN MASUK
============================================================ */

const btnManualMasuk =
    document.getElementById(
        'btn-manual-masuk'
    );

if (btnManualMasuk) {

    btnManualMasuk.addEventListener(
        'click',
        async function() {

            const karyawanIdElement =
                document.getElementById(
                    'karyawan-id'
                );

            const proyekIdElement =
                document.getElementById(
                    'current-proyek-id'
                );

            const id_karyawan =
                karyawanIdElement
                    ? karyawanIdElement.value
                    : '';

            const id_proyek =
                proyekIdElement
                    ? proyekIdElement.value
                    : currentProyekId;

            if (!id_karyawan) {

                showMessage(
                    'Scan wajah terlebih dahulu',
                    'error'
                );

                return;
            }

            if (!id_proyek) {

                showMessage(
                    'Pilih proyek terlebih dahulu',
                    'error'
                );

                return;
            }

            const formData =
                new FormData();

            formData.append(
                'id_karyawan',
                id_karyawan
            );

            formData.append(
                'id_proyek',
                id_proyek
            );

            formData.append(
                'absensi_type',
                'masuk'
            );

            const status =
                document.getElementById(
                    'status'
                );

            const keterangan =
                document.getElementById(
                    'keterangan'
                );

            formData.append(
                'status',
                status
                    ? status.value
                    : 'hadir'
            );

            formData.append(
                'keterangan',
                keterangan &&
                keterangan.value
                    ? keterangan.value
                    : 'Absen masuk manual'
            );

            formData.append(
                'lembur_jam',
                0
            );

            const snapshot =
                document.getElementById(
                    'face-snapshot'
                );

            formData.append(
                'face_snapshot',
                snapshot
                    ? snapshot.value
                    : ''
            );

            const submitButton =
                document.getElementById(
                    'submit-absen'
                );

            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.textContent =
                    '⏳ Menyimpan...';
            }

            try {

                const response =
                    await fetch(
                        '<?= BASE_URL ?>/public/index.php?page=api&action=storeAbsensi',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );

                const result =
                    await response.json();

                showMessage(
                    result.message,
                    result.success
                        ? 'success'
                        : 'error'
                );

                if (result.success) {

                    loadStatistik(
                        currentProyekId
                    );

                    loadAbsensi(
                        currentProyekId
                    );
                }

            } catch (error) {

                console.error(
                    'ERROR MANUAL ABSEN:',
                    error
                );

                showMessage(
                    'Terjadi kesalahan: ' +
                    error.message,
                    'error'
                );

            } finally {

                if (submitButton) {

                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        '📥 Konfirmasi Absen Masuk';
                }
            }
        }
    );
}


/* ============================================================
   SHOW MESSAGE
============================================================ */

function showMessage(
    msg,
    type
) {

    const div =
        document.getElementById(
            'status-message'
        );

    if (!div) {

        alert(msg);

        return;
    }

    div.textContent =
        msg || '';

    div.style.display =
        'block';

    if (type === 'success') {

        div.className =
            'status-success';

    } else if (type === 'error') {

        div.className =
            'status-error';

    } else {

        div.className =
            'status-info';
    }

    setTimeout(
        function() {

            div.style.display =
                'none';

        },
        4000
    );
}


/* ============================================================
   REGISTER WAJAH
============================================================ */

function registerFace(
    id,
    nama
) {

    const namaElement =
        document.getElementById(
            'register-nama'
        );

    const idElement =
        document.getElementById(
            'register-karyawan-id'
        );

    const modal =
        document.getElementById(
            'modal-register-face'
        );

    if (namaElement) {

        namaElement.textContent =
            nama;
    }

    if (idElement) {

        idElement.value =
            id;
    }

    if (modal) {

        modal.style.display =
            'flex';
    }

    if (registerStream) {

        registerStream
            .getTracks()
            .forEach(
                track =>
                    track.stop()
            );
    }

    navigator.mediaDevices
        .getUserMedia({
            video: true,
            audio: false
        })
        .then(
            function(cameraStream) {

                registerStream =
                    cameraStream;

                registerVideo =
                    document.getElementById(
                        'register-video'
                    );

                if (!registerVideo) {
                    return;
                }

                registerVideo.srcObject =
                    cameraStream;

                return registerVideo.play();
            }
        )
        .catch(
            function(error) {

                console.error(
                    'ERROR REGISTER CAMERA:',
                    error
                );

                alert(
                    'Kamera tidak dapat digunakan.'
                );
            }
        );
}


/* ============================================================
   CLOSE REGISTER MODAL
============================================================ */

function closeModalRegister() {

    if (registerStream) {

        registerStream
            .getTracks()
            .forEach(
                track =>
                    track.stop()
            );

        registerStream = null;
    }

    if (registerVideo) {

        registerVideo.srcObject =
            null;
    }

    const modal =
        document.getElementById(
            'modal-register-face'
        );

    if (modal) {

        modal.style.display =
            'none';
    }
}


/* ============================================================
   CAPTURE & REGISTER FACE
============================================================ */

const captureFaceButton =
    document.getElementById(
        'capture-face'
    );

if (captureFaceButton) {

    captureFaceButton.addEventListener(
        'click',
        async function() {

            const idElement =
                document.getElementById(
                    'register-karyawan-id'
                );

            const videoReg =
                document.getElementById(
                    'register-video'
                );

            const karyawanId =
                idElement
                    ? idElement.value
                    : '';

            if (!karyawanId) {

                alert(
                    'ID karyawan tidak tersedia.'
                );

                return;
            }

            if (
                !videoReg ||
                !videoReg.videoWidth
            ) {

                alert(
                    'Kamera belum siap.'
                );

                return;
            }

            try {

                const detection =
                    await faceapi
                        .detectSingleFace(
                            videoReg,
                            new faceapi
                                .TinyFaceDetectorOptions()
                        )
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                if (!detection) {

                    alert(
                        'Wajah tidak terdeteksi!'
                    );

                    return;
                }

                const descriptor =
                    Array.from(
                        detection.descriptor
                    );

                const response =
                    await fetch(
                        '<?= BASE_URL ?>/public/index.php?page=api&action=registerFace',
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json'
                            },

                            body: JSON.stringify({

                                id_karyawan:
                                    karyawanId,

                                face_descriptor:
                                    descriptor
                            })
                        }
                    );

                const data =
                    await response.json();

                alert(
                    data.message
                );

                if (data.success) {

                    closeModalRegister();

                    loadKaryawan(
                        currentProyekId
                    );
                }

            } catch (error) {

                console.error(
                    'ERROR REGISTER FACE:',
                    error
                );

                alert(
                    'Terjadi kesalahan saat register wajah.'
                );
            }
        }
    );
}


/* ============================================================
   EDIT ABSEN KELUAR
============================================================ */

let currentEditId = null;


function openEditKeluar(
    id,
    nama
) {

    currentEditId =
        id;

    const idElement =
        document.getElementById(
            'edit_id_absensi'
        );

    if (idElement) {

        idElement.value =
            id;
    }

    const modal =
        document.getElementById(
            'modal-edit-keluar'
        );

    if (modal) {

        modal.style.display =
            'flex';
    }

    const header =
        document.querySelector(
            '#modal-edit-keluar .modal-header span:first-child'
        );

    if (header) {

        header.innerHTML =
            `✏️ Edit Absen Keluar - ${nama}`;
    }
}


function closeEditModal() {

    const modal =
        document.getElementById(
            'modal-edit-keluar'
        );

    if (modal) {

        modal.style.display =
            'none';
    }
}


const formEditKeluar =
    document.getElementById(
        'form-edit-keluar'
    );

if (formEditKeluar) {

    formEditKeluar.addEventListener(
        'submit',
        async function(e) {

            e.preventDefault();

            const formData =
                new FormData();

            formData.append(
                'id',
                document.getElementById(
                    'edit_id_absensi'
                ).value
            );

            formData.append(
                'jam_keluar',
                document.getElementById(
                    'edit_jam_keluar'
                ).value
            );

            formData.append(
                'lembur_jam',
                document.getElementById(
                    'edit_lembur'
                ).value
            );

            formData.append(
                'status',
                document.getElementById(
                    'edit_status'
                ).value
            );

            formData.append(
                'keterangan',
                document.getElementById(
                    'edit_keterangan'
                ).value
            );

            const submitButton =
                document.querySelector(
                    '#form-edit-keluar button'
                );

            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.textContent =
                    '⏳ Menyimpan...';
            }

            try {

                const response =
                    await fetch(
                        '<?= BASE_URL ?>/public/index.php?page=api&action=updateAbsensi',
                        {
                            method: 'POST',
                            body: formData
                        }
                    );

                const result =
                    await response.json();

                alert(
                    result.message
                );

                if (result.success) {

                    closeEditModal();

                    loadAbsensi(
                        currentProyekId
                    );

                    loadStatistik(
                        currentProyekId
                    );
                }

            } catch (error) {

                console.error(
                    'ERROR UPDATE ABSENSI:',
                    error
                );

                alert(
                    'Terjadi kesalahan: ' +
                    error.message
                );

            } finally {

                if (submitButton) {

                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        '💾 Simpan Perubahan';
                }
            }
        }
    );
}


/* ============================================================
   FORMAT TANGGAL
============================================================ */

function formatDateInput(
    date
) {

    const y =
        date.getFullYear();

    const m =
        String(
            date.getMonth() + 1
        ).padStart(2, '0');

    const d =
        String(
            date.getDate()
        ).padStart(2, '0');

    return `${y}-${m}-${d}`;
}


function parseDateOnly(
    value
) {

    if (!value) {
        return null;
    }

    const datePart =
        String(value)
            .substring(0, 10);

    const parts =
        datePart.split('-');

    if (
        parts.length !== 3
    ) {

        return null;
    }

    return new Date(
        Number(parts[0]),
        Number(parts[1]) - 1,
        Number(parts[2])
    );
}


function formatTanggalIndonesia(
    value
) {

    const d =
        parseDateOnly(value);

    if (!d) {
        return '-';
    }

    return d.toLocaleDateString(
        'id-ID',
        {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        }
    );
}


/* ============================================================
   MONITORING GAJI
============================================================ */

function periodeGaji14Hari() {
    const end   = new Date();
    const start = new Date();
    start.setDate(end.getDate() - 13);

    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('gaji-start').value = fmt(start);
    document.getElementById('gaji-end').value   = fmt(end);
    tampilkanMonitoringGaji();
}

async function tampilkanMonitoringGaji() {
    const start = document.getElementById('gaji-start').value;
    const end   = document.getElementById('gaji-end').value;

    if (!start || !end) {
        alert('Pilih tanggal mulai dan tanggal selesai.');
        return;
    }
    if (!currentProyekId) {
        alert('Pilih proyek terlebih dahulu.');
        return;
    }

    const tbody   = document.getElementById('gaji-tbody');
    const period  = document.getElementById('gaji-period');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center">⏳ Memuat data...</td></tr>';

    try {
        const res  = await fetch(`<?= BASE_URL ?>/public/index.php?page=api&action=getRekapGaji&id_proyek=${currentProyekId}&start=${start}&end=${end}`);
        const json = await res.json();

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">${json.message}</td></tr>`;
            return;
        }

        // Periode label
        const fmt = s => {
            const [y,m,d] = s.split('-');
            const bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
            return `${d} ${bln[parseInt(m)-1]} ${y}`;
        };
        if (period) period.textContent = `Periode: ${fmt(start)} – ${fmt(end)}`;

        // Summary cards
        const s = json.summary;
        const el = id => document.getElementById(id);
        if (el('gaji-jumlah-karyawan')) el('gaji-jumlah-karyawan').textContent = s.total_karyawan;
        if (el('gaji-jumlah-hadir'))    el('gaji-jumlah-hadir').textContent    = s.total_hadir;
        if (el('gaji-jumlah-lembur'))   el('gaji-jumlah-lembur').textContent   = s.total_lembur + ' jam';
        if (el('gaji-total'))           el('gaji-total').textContent           = 'Rp ' + Math.round(s.total_gaji).toLocaleString('id-ID');

        // Tabel
        if (!json.data.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center">Tidak ada data karyawan untuk periode ini.</td></tr>';
            return;
        }

        tbody.innerHTML = json.data.map((k, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${k.nik || '-'}</td>
                <td>${k.nama}</td>
                <td>${k.jabatan || '-'}</td>
                <td style="text-align:center;">${k.total_hadir}</td>
                <td style="text-align:center;">${k.total_izin}</td>
                <td style="text-align:center;">${k.total_sakit}</td>
                <td style="text-align:center;">${k.total_lembur} jam</td>
                <td>Rp ${Number(k.gaji_pokok).toLocaleString('id-ID')}</td>
                <td style="font-weight:600;">Rp ${Number(k.total_gaji).toLocaleString('id-ID')}</td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('ERROR MONITORING GAJI:', err);
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Terjadi kesalahan. Coba lagi.</td></tr>';
    }
}


/* ============================================================
   EXPORT EXCEL
============================================================ */

function exportExcel() {

    if (!currentProyekId) {

        alert(
            'Pilih proyek terlebih dahulu.'
        );

        return;
    }

    window.location.href =
        `<?= BASE_URL ?>/public/index.php?page=api&action=exportExcel&proyek_id=${currentProyekId}`;
}


/* ============================================================
   TAB
============================================================ */

document
    .querySelectorAll(
        '.tab-absensi-btn'
    )
    .forEach(
        function(btn) {

            btn.addEventListener(
                'click',
                function() {

                    document
                        .querySelectorAll(
                            '.tab-absensi-btn'
                        )
                        .forEach(
                            b =>
                                b.classList
                                    .remove(
                                        'active'
                                    )
                        );

                    document
                        .querySelectorAll(
                            '.tab-pane'
                        )
                        .forEach(
                            pane =>
                                pane.classList
                                    .remove(
                                        'active'
                                    )
                        );

                    btn.classList.add(
                        'active'
                    );

                    const tabName =
                        btn.dataset.tab;

                    const target =
                        document.getElementById(
                            `tab-${tabName}`
                        );

                    if (target) {

                        target.classList.add(
                            'active'
                        );
                    }
                }
            );
        }
    );


/* ============================================================
   REALTIME CLOCK WIB
============================================================ */

function updateRealtimeClock() {

    const now = new Date();

    // Ambil waktu UTC
    const utcTime = now.getTime();

    // WIB = UTC + 7 jam
    const wibTime = new Date(
        utcTime + (7 * 60 * 60 * 1000)
    );

    const jam = String(wibTime.getUTCHours()).padStart(2, '0');
    const menit = String(wibTime.getUTCMinutes()).padStart(2, '0');
    const detik = String(wibTime.getUTCSeconds()).padStart(2, '0');

    const hari = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu'
    ];

    const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    const namaHari =
        hari[wibTime.getUTCDay()];

    const tanggal =
        wibTime.getUTCDate();

    const namaBulan =
        bulan[wibTime.getUTCMonth()];

    const tahun =
        wibTime.getUTCFullYear();

    const clock =
        document.getElementById('live-clock');

    const date =
        document.getElementById('live-date');

    if (clock) {
        clock.textContent =
            `${jam}:${menit}:${detik}`;
    }

    if (date) {
        date.textContent =
            `${namaHari}, ${tanggal} ${namaBulan} ${tahun}`;
    }
}

// Jalankan langsung
updateRealtimeClock();

// Update setiap 1 detik
setInterval(
    updateRealtimeClock,
    1000
);


/* ============================================================
   CLOSE MODAL WHEN CLICK OUTSIDE
============================================================ */

window.addEventListener(
    'click',
    function(event) {

        if (
            event.target.classList
                .contains('modal')
        ) {

            event.target.style.display =
                'none';
        }
    }
);

/* ============================================================
   INIT
============================================================ */

document.addEventListener(
    'DOMContentLoaded',
    function() {

        console.log(
            'Sistem absensi dimulai...'
        );

        <?php if ($globalProjectId): ?>

            pilihProyek(
                <?= (int)$globalProjectId ?>
            );

        <?php else: ?>

            loadProyek();

        <?php endif; ?>

        loadModels();
    }
);


/* ============================================================
   CLOSE MODAL
============================================================ */

window.onclick = function(event) {

    if (
        event.target.classList.contains('modal')
    ) {

        event.target.style.display = 'none';
    }
};

</script>

<?php endif; ?>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>