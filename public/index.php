<?php
// public/index.php

session_start();

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']));

require_once BASE_PATH . '/config/database.php';

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

$routes = [
    'login'     => 'LoginController',
    'dashboard' => 'DashboardController',
    'proyek'    => 'ProyekController',
    'barang'    => 'BarangController',
    'absensi'   => 'AbsensiController',
    'keuangan'  => 'KeuanganController',
];

// Access control middleware
$publicPages = ['login']; // Pages yang bisa diakses tanpa login
$publicActions = ['authenticate', 'logout']; // Actions yang bisa diakses tanpa login
if (!in_array($page, $publicPages) && !in_array($action, $publicActions) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/?page=login');
    exit;
}

if (isset($routes[$page])) {
    $controllerClass = $routes[$page];
    $controller = new $controllerClass();

    if ($action === 'index')            $controller->index();
    elseif ($action === 'authenticate') $controller->authenticate();
    elseif ($action === 'logout')       $controller->logout();
    elseif ($action === 'detail')       $controller->detail((int)$id);
    elseif ($action === 'create')       $controller->create();
    elseif ($action === 'store')        $controller->store();
    elseif ($action === 'storeMasuk')   $controller->storeMasuk();
    elseif ($action === 'storeKeluar')  $controller->storeKeluar();
    elseif ($action === 'edit')         $controller->edit((int)$id);
    elseif ($action === 'update')       $controller->update((int)$id);
    elseif ($action === 'delete')       $controller->delete((int)$id);
    elseif ($action === 'exportMasukExcel') $controller->exportMasukExcel();
    elseif ($action === 'exportKeluarExcel') $controller->exportKeluarExcel();
    elseif ($action === 'exportExcel')  $controller->exportExcel();
    elseif ($action === 'editMasuk')    $controller->editMasuk((int)$id);
    elseif ($action === 'updateMasuk')  $controller->updateMasuk((int)$id);
    elseif ($action === 'deleteMasuk')  $controller->deleteMasuk((int)$id);
    else                                $controller->index();
} else {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
}
