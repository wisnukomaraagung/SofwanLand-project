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
        <div class="tab active">Grafik Progress</div>
        <div class="tab">Pengeluaran</div>
        <div class="tab">Rincian Pekerjaan</div>
        <div class="tab">Dokumentasi</div>
    </div>

    <!-- CONTENT -->
    <div class="detail-grid">

        <!-- GRAFIK -->
        <div class="chart-card">

            <div class="chart-title">
                Grafik Progress Proyek
            </div>

            <div style="height:320px">
                <canvas id="progressChart"></canvas>
            </div>

        </div>

        <!-- RINCIAN -->
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

                    <div>
                        <div class="work-progress">
                            <span style="width:100%"></span>
                        </div>
                    </div>
                </div>

                <div class="work-item">
                    <div class="work-left">
                        <i class="ri-building-line"></i>
                        Struktur
                    </div>

                    <div>
                        <div class="work-progress">
                            <span style="width:80%"></span>
                        </div>
                    </div>
                </div>

                <div class="work-item">
                    <div class="work-left">
                        <i class="ri-home-gear-line"></i>
                        Finishing
                    </div>

                    <div>
                        <div class="work-progress">
                            <span style="width:40%"></span>
                        </div>
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

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>