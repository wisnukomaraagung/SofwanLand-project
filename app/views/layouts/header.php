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
        $menuItems   = getMenuForRole();
        $hasProject  = !empty($_SESSION['selected_project_id']);

        // Pages that require a project to be selected first
        $projectRequired = ['absensi', 'barang', 'keuangan', 'proyek'];

        foreach ($menuItems as $key => $item):
            // Skip project-gated pages if no project is selected
            if (in_array($key, $projectRequired) && !$hasProject) continue;
            $isActive = ($currentPage === $key) ? 'active' : '';
        ?>
        <a href="<?= BASE_URL ?>/public/index.php?page=<?= $key ?>" class="nav-link <?= $isActive ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>


        <div style="flex-grow: 1;"></div>

        <?php if (isset($_SESSION['selected_project_name'])): ?>
        <span class="nav-user-badge" style="background: #27ae60; color: white; display: inline-flex; align-items: center; gap: 6px;" title="Proyek Aktif">
            📁 <?= htmlspecialchars($_SESSION['selected_project_name']) ?>
            <a href="<?= BASE_URL ?>/public/index.php?page=dashboard&action=clearProject" style="color: white; text-decoration: none; font-weight: bold; padding: 0 4px; border-radius: 4px; background: rgba(0,0,0,0.15);" title="Nonaktifkan Filter">✕</a>
        </span>
        <?php endif; ?>

        <?php if (!empty($_SESSION['nama'])): ?>
        <span class="nav-user-badge" title="<?= htmlspecialchars(getRoleLabel()) ?>">
            <?= htmlspecialchars($_SESSION['nama']) ?>
            <small>(<?= htmlspecialchars(getRoleLabel()) ?>)</small>
        </span>
        <?php endif; ?>

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
