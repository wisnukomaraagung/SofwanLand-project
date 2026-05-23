<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Kontraktor Management System' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar d-flex justify-content-between align-items-center">

  <div class="d-flex align-items-center">
        <img src="<?= BASE_URL ?>/public/assets/logoputih.png" width="80" class="me-2">
    </div>

    <button class="hamburger" id="hamburgerBtn" onclick="toggleMenu()">
        <span></span><span></span><span></span>
    </button>

    <div class="navbar-menu" id="navMenu">
        <?php
        $currentPage = $_GET['page'] ?? 'dashboard';
        $menuItems = [
            'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
            'proyek'    => ['icon' => '◫', 'label' => 'Proyek'],
            'barang'    => ['icon' => '◧', 'label' => 'Barang'],
            'absensi'   => ['icon' => '◩', 'label' => 'Absensi'],
            'keuangan'  => ['icon' => '◪', 'label' => 'Keuangan'],
        ];
        foreach ($menuItems as $key => $item):
            $isActive = ($currentPage === $key) ? 'active' : '';
        ?>
        <a href="<?= BASE_URL ?>/public/index.php?page=<?= $key ?>" class="nav-link <?= $isActive ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>

        <div style="flex-grow: 1;"></div>
        
        <a href="<?= BASE_URL ?>/public/index.php?page=login&action=logout" class="nav-link" style="color: #c0392b; font-weight: 600;" onclick="return confirm('Apakah Anda yakin ingin logout?');">
            <span class="nav-icon">🚪</span>
            Logout
        </a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
            <?php if (!empty($pageSubtitle)): ?>
            <p class="page-subtitle"><?= $pageSubtitle ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($pageAction)): ?>
        <div class="page-actions">
            <?= $pageAction ?>
        </div>
        <?php endif; ?>
    </div>
