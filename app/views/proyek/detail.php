<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

.detail-wrapper{
    background:#fff;
    border-radius:24px;
    padding:30px;
    box-shadow:0 10px 40px rgba(0,0,0,.08);
}

.project-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:25px;
}

.project-info{
    display:flex;
    gap:20px;
}

.project-image{
    width:110px;
    height:110px;
    border-radius:16px;
    overflow:hidden;
    background:#f1f5f9;
}

.project-image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.project-title h1{
    font-size:28px;
    margin-bottom:8px;
}

.project-sub{
    color:#64748b;
    margin-bottom:15px;
}

.project-stats{
    display:flex;
    gap:40px;
    margin-top:20px;
}

.stat-item small{
    color:#94a3b8;
    display:block;
    margin-bottom:5px;
}

.stat-item strong{
    font-size:18px;
}

.progress-area{
    min-width:250px;
}

.progress-bar{
    width:100%;
    height:12px;
    background:#e2e8f0;
    border-radius:10px;
    overflow:hidden;
    margin-top:10px;
}

.progress-fill{
    height:100%;
    background:#22c55e;
    border-radius:10px;
}

.tabs{
    display:flex;
    gap:30px;
    border-bottom:1px solid #e2e8f0;
    margin:30px 0;
}

.tab{
    padding-bottom:14px;
    cursor:pointer;
    font-weight:600;
    color:#64748b;
}

.tab.active{
    color:#2563eb;
    border-bottom:3px solid #2563eb;
}

.detail-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

.chart-card{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:20px;
}

.chart-title{
    font-size:18px;
    font-weight:700;
    margin-bottom:20px;
}

.work-list{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.work-item{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.work-left{
    display:flex;
    align-items:center;
    gap:10px;
}

.work-progress{
    width:120px;
    height:8px;
    background:#e2e8f0;
    border-radius:10px;
    overflow:hidden;
    margin-top:5px;
}

.work-progress span{
    display:block;
    height:100%;
    background:#22c55e;
}

.badge-status{
    padding:8px 14px;
    border-radius:10px;
    background:#dbeafe;
    color:#2563eb;
    font-weight:600;
    font-size:13px;
}

.tab-content{
    display:none;
}

.tab-content.active{
    display:block;
}

.custom-table{
    width:100%;
    border-collapse:collapse;
}

.custom-table th,
.custom-table td{
    padding:14px;
    border-bottom:1px solid #e2e8f0;
    text-align:left;
}

.job-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}

.job-card{
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:20px;
    background:#fff;
}

.job-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.job-status{
    padding:6px 12px;
    border-radius:10px;
    font-size:12px;
    font-weight:600;
}

.selesai{
    background:#dcfce7;
    color:#16a34a;
}

.proses{
    background:#fef9c3;
    color:#ca8a04;
}

.job-progress{
    width:100%;
    height:10px;
    background:#e2e8f0;
    border-radius:10px;
    overflow:hidden;
    margin:15px 0;
}

.job-progress span{
    display:block;
    height:100%;
    background:#2563eb;
}

.gallery-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}

.gallery-card{
    border-radius:20px;
    overflow:hidden;
    border:1px solid #e2e8f0;
    background:#fff;
}

.gallery-card img{
    width:100%;
    height:220px;
    object-fit:cover;
}

.gallery-body{
    padding:18px;
}

.tab{
    padding-bottom:14px;
    cursor:pointer;
    font-weight:600;
    color:#64748b;
    transition:.2s;
}

.tab:hover{
    color:#2563eb;
}

@media(max-width:768px){

    .project-top{
        flex-direction:column;
        gap:20px;
    }

    .detail-grid{
        grid-template-columns:1fr;
    }

    .tabs{
        overflow-x:auto;
        white-space:nowrap;
    }

}

</style>

<div class="detail-wrapper">

    <!-- HEADER -->
    <div class="project-top">

        <div class="project-info">

           

            <div class="project-title">

                <h1><?= htmlspecialchars($proyek['nama_proyek']) ?></h1>

                <div class="project-sub">
                    <?= htmlspecialchars($proyek['lokasi']) ?>
                </div>

                <span class="badge-status">
                    <?= ucfirst($proyek['status']) ?>
                </span>

                <div class="project-stats">

                    <div class="stat-item">
                        <small>Total Budget</small>
                        <strong>
                            Rp <?= number_format($proyek['nilai_kontrak'],0,',','.') ?>
                        </strong>
                    </div>

                    <div class="stat-item">
                        <small>Pengeluaran</small>
                        <strong>
                            Rp <?= number_format($proyek['total_biaya'],0,',','.') ?>
                        </strong>
                    </div>

                </div>

            </div>

        </div>

        <div class="progress-area">

            <strong style="font-size:24px">
                <?= $proyek['progress_terbaru'] ?>%
            </strong>

            <div class="progress-bar">
                <div class="progress-fill"
                     style="width:<?= $proyek['progress_terbaru'] ?>%">
                </div>
            </div>

        </div>

    </div>

    <!-- TAB -->
    <div class="tabs">
        <div class="tab active" data-tab="grafik">
            Grafik Progress
        </div>

        <div class="tab" data-tab="pengeluaran">
            Pengeluaran
        </div>

        <div class="tab" data-tab="rincian">
            Rincian Pekerjaan
        </div>

        <div class="tab" data-tab="dokumentasi">
            Dokumentasi
        </div>
    </div>


    <!-- TAB GRAFIK -->
    <div class="tab-content active" id="grafik">

        <div class="detail-grid">

            <div class="chart-card">

                <div class="chart-title">
                    Grafik Progress Proyek
                </div>

                <div style="height:320px">
                    <canvas id="progressChart"></canvas>
                </div>

            </div>

            <div class="chart-card">

                <div class="chart-title">
                    Progress Pekerjaan
                </div>

                <div class="work-list">

                    <div class="work-item">
                        <div class="work-left">
                            <i class="ri-hammer-line"></i>
                            Pondasi
                        </div>

                        <div class="work-progress">
                            <span style="width:100%"></span>
                        </div>
                    </div>

                    <div class="work-item">
                        <div class="work-left">
                            <i class="ri-building-line"></i>
                            Struktur
                        </div>

                        <div class="work-progress">
                            <span style="width:80%"></span>
                        </div>
                    </div>

                    <div class="work-item">
                        <div class="work-left">
                            <i class="ri-home-gear-line"></i>
                            Finishing
                        </div>

                        <div class="work-progress">
                            <span style="width:40%"></span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- TAB PENGELUARAN -->
    <div class="tab-content" id="pengeluaran">

        <div class="chart-card">

            <div class="chart-title">
                Data Pengeluaran
            </div>

            <table class="custom-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>12 Mei 2026</td>
                        <td>Material</td>
                        <td>Pembelian Semen</td>
                        <td>Rp 12.000.000</td>
                    </tr>

                    <tr>
                        <td>15 Mei 2026</td>
                        <td>Upah</td>
                        <td>Pekerja Pondasi</td>
                        <td>Rp 8.000.000</td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

    <!-- TAB RINCIAN -->
    <div class="tab-content" id="rincian">

        <div class="job-grid">

            <div class="job-card">

                <div class="job-head">
                    <h3>Pondasi</h3>
                    <span class="job-status selesai">Selesai</span>
                </div>

                <p>
                    Pengerjaan pondasi utama bangunan proyek.
                </p>

                <div class="job-progress">
                    <span style="width:100%"></span>
                </div>

                <small>Estimasi : 7 Hari</small>

            </div>

            <div class="job-card">

                <div class="job-head">
                    <h3>Struktur Beton</h3>
                    <span class="job-status proses">Berjalan</span>
                </div>

                <p>
                    Pengerjaan struktur dan pengecoran beton.
                </p>

                <div class="job-progress">
                    <span style="width:65%"></span>
                </div>

                <small>Estimasi : 14 Hari</small>

            </div>

        </div>

    </div>

    <!-- TAB DOKUMENTASI -->
    <div class="tab-content" id="dokumentasi">

        <div class="gallery-grid">

            <div class="gallery-card">

                <img src="<img src="/uploads/proyek/pondasi.jpg">

                <div class="gallery-body">

                    <h4>Pondasi Proyek</h4>

                    <small>12 Mei 2026</small>

                    <div class="job-progress">
                        <span style="width:35%"></span>
                    </div>

                </div>

            </div>

            <div class="gallery-card">

                <img src="https://images.unsplash.com/photo-1541888946425-d81bb19240f5?q=80&w=1200&auto=format&fit=crop">

                <div class="gallery-body">

                    <h4>Struktur Bangunan</h4>

                    <small>20 Mei 2026</small>

                    <div class="job-progress">
                        <span style="width:70%"></span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

const ctx = document.getElementById('progressChart');

new Chart(ctx, {
    type: 'line',

    data: {
        labels: [
            <?php foreach(array_reverse($progressHistory) as $pr): ?>
                '<?= date('d M', strtotime($pr['tanggal'])) ?>',
            <?php endforeach; ?>
        ],

        datasets: [{
            label:'Progress',

            data: [
                <?php foreach(array_reverse($progressHistory) as $pr): ?>
                    <?= $pr['persentase'] ?>,
                <?php endforeach; ?>
            ],

            borderColor:'#2563eb',
            backgroundColor:'rgba(37,99,235,.1)',
            fill:true,
            tension:.4,
            borderWidth:3,
            pointRadius:5
        }]
    },

    options:{
        responsive:true,
        maintainAspectRatio:false,

        plugins:{
            legend:{
                display:false
            }
        },

        scales:{
            y:{
                beginAtZero:true,
                max:100
            }
        }
    }
});

</script>

<script>

const tabs = document.querySelectorAll('.tab');
const contents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {

    tab.addEventListener('click', () => {

        tabs.forEach(item => item.classList.remove('active'));
        contents.forEach(item => item.classList.remove('active'));

        tab.classList.add('active');

        const target = tab.dataset.tab;

        document
            .getElementById(target)
            .classList.add('active');

    });

});

</script>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>