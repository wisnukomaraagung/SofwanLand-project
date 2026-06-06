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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        $hasProject  = !empty($_SESSION['selected_project_id']);
        $role        = $_SESSION['user_role'] ?? 'user';

        // Jika ada proyek aktif, tampilkan menu khusus proyek.
        if ($hasProject) {
                if ($role === 'user') {
                $menuItems = [
                    'absensi' => ['icon' => '◩', 'label' => 'Absensi'],
                ];
            } else {
                $menuItems = [
                    'proyek'   => ['icon' => '◫', 'label' => 'Proyek'],
                    'keuangan' => ['icon' => '◪', 'label' => 'Keuangan'],
                    'absensi'  => ['icon' => '◩', 'label' => 'Absensi'],
                    'barang'   => ['icon' => '◧', 'label' => 'Barang'],
                ];
            }
        } else {
            $menuItems = getMenuForRole();
            // Jika sedang di halaman Dashboard utama tanpa proyek aktif, sembunyikan menu yang terkait proyek
            $projectRequired = ['absensi', 'barang', 'keuangan', 'proyek'];
            if ($currentPage === 'dashboard' && !$hasProject) {
                foreach ($projectRequired as $k) {
                    if (isset($menuItems[$k])) unset($menuItems[$k]);
                }
            }
        }

        // Pages that require a project to be selected first (for non-project view)
        $projectRequired = ['absensi', 'barang', 'keuangan', 'proyek'];

        foreach ($menuItems as $key => $item):
            // Jika tidak ada proyek, batasi halaman yang memerlukan proyek hanya untuk role `user`.
            if (in_array($key, $projectRequired) && !$hasProject && $role === 'user') continue;
            $isActive = ($currentPage === $key) ? 'active' : '';
            $href = BASE_URL . '/public/index.php?page=' . $key;
            // Jika menu proyek diklik dan proyek aktif, langsung ke detail proyek
            if ($key === 'proyek' && $hasProject) {
                $pid = (int)($_SESSION['selected_project_id'] ?? 0);
                if ($pid) $href .= '&action=detail&id=' . $pid;
            }
        ?>
        <a href="<?= $href ?>" class="nav-link <?= $isActive ?>">
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

        <a id="logoutBtn" href="<?= BASE_URL ?>/public/index.php?page=login&action=logout" class="nav-link" style="color: #c0392b; font-weight: 600;">
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
