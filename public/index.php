<?php
// public/index.php

session_start();

define('BASE_PATH', dirname(__DIR__));
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseFolder = preg_replace('#/public$#', '', rtrim($scriptDir, '/'));
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . $baseFolder);

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/roles.php';
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Autoload models & controllers
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/app/controllers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Simple router
$page    = $_GET['page'] ?? 'dashboard';
$action  = $_GET['action'] ?? 'index';
$id      = $_GET['id'] ?? null;

// ============ API ROUTING ============
if ($page === 'api') {
    // Set JSON header untuk API
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    require_once BASE_PATH . '/app/controllers/ApiController.php';
    $api = new ApiController();
    
    if (method_exists($api, $action)) {
        $api->$action();
    } else {
        echo json_encode(['success' => false, 'message' => 'API endpoint not found: ' . $action]);
    }
    exit();
}

$routes = [
    'login'     => 'LoginController',
    'dashboard' => 'DashboardController',
    'proyek'    => 'ProyekController',
    'barang'    => 'BarangController',
    'absensi'   => 'AbsensiController',
    'keuangan'  => 'KeuanganController',
    'pekerjaan' => 'PekerjaanController',
    'kurva_s'   => 'KurvaSController', 
    'pengguna'  => 'PenggunaController',
];

// Access control middleware
$publicPages = ['login']; // Pages yang bisa diakses tanpa login
$publicActions = ['authenticate', 'logout']; // Actions yang bisa diakses tanpa login
if (!in_array($page, $publicPages) && !in_array($action, $publicActions) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/public/index.php?page=login');
    exit;
}

// Role-based access: admin (absensi, barang) | manager (proyek, keuangan)
if (isset($_SESSION['user_id']) && !in_array($page, $publicPages) && !roleCanAccessPage($page)) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman tersebut.';
    header('Location: ' . BASE_URL . '/public/index.php?page=dashboard');
    exit;
}

if (isset($routes[$page])) {
    $controllerClass = $routes[$page];
    $controller = new $controllerClass();

    if ($action === 'index')            $controller->index();
    elseif ($action === 'authenticate') $controller->authenticate();
    elseif ($action === 'logout')       $controller->logout();
    elseif ($action === 'detail')       $controller->detail((int)$id);
    elseif ($page === 'pengguna' && $action === 'edit') $controller->edit((int)$id);
    elseif ($page === 'pengguna' && $action === 'update') $controller->update((int)$id);
    elseif ($page === 'pengguna' && $action === 'delete') $controller->delete((int)$id);
    elseif ($action === 'storeDokumentasi') $controller->storeDokumentasi((int)$id);
    elseif ($action === 'deleteDokumentasi') $controller->deleteDokumentasi((int)$id);
    elseif ($action === 'exportDetailExcel') $controller->exportDetailExcel((int)$id);
    elseif ($action === 'importDetailExcel') $controller->importDetailExcel((int)$id);
    elseif ($action === 'create')       $controller->create();
    elseif ($action === 'store')        $controller->store();
    elseif ($action === 'storeMasuk')   $controller->storeMasuk();
    elseif ($action === 'storeKeluar')  $controller->storeKeluar();
    elseif ($action === 'edit')         $controller->edit((int)$id);
    elseif ($action === 'update')       $controller->update((int)$id);
    elseif ($action === 'delete')       $controller->delete((int)$id);
    elseif ($action === 'exportMasukExcel') $controller->exportMasukExcel();
    elseif ($action === 'exportKeluarExcel') $controller->exportKeluarExcel();
    elseif ($action === 'exportStokExcel') $controller->exportStokExcel();
    elseif ($action === 'exportLaporanExcel') $controller->exportLaporanExcel();
    elseif ($action === 'exportExcel')  $controller->exportExcel();
    elseif ($action === 'editMasuk')    $controller->editMasuk((int)$id);
    elseif ($action === 'updateMasuk')  $controller->updateMasuk((int)$id);
    elseif ($action === 'deleteMasuk')  $controller->deleteMasuk((int)$id);
    elseif ($action === 'editKeluar')   $controller->editKeluar((int)$id);
    elseif ($action === 'updateKeluar') $controller->updateKeluar((int)$id);
    elseif ($action === 'deleteKeluar') $controller->deleteKeluar((int)$id);
    elseif ($action === 'viewBukti')   $controller->viewBukti();
    elseif ($action === 'updateProgressAjax') $controller->updateProgressAjax();

    elseif ($action === 'selectProject') {
        $dash = new DashboardController();
        $dash->selectProject();
    }
    elseif ($action === 'clearProject') {
        $dash = new DashboardController();
        $dash->clearProject();
    }
    else                                $controller->index();
} else {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
}