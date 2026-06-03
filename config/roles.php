<?php
// config/roles.php — hak akses halaman per role

function getUserRole(): string
{
    return $_SESSION['user_role'] ?? 'staff';
}

function getRolePermissions(): array
{
    return [
        'admin' => [
            'pages' => ['dashboard', 'absensi', 'barang', 'proyek', 'keuangan'],
            'menu' => [
                'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
                'proyek'    => ['icon' => '◫', 'label' => 'Proyek'],
                'keuangan'  => ['icon' => '◪', 'label' => 'Keuangan'],
                'absensi'   => ['icon' => '◩', 'label' => 'Absensi'],
                'barang'    => ['icon' => '◧', 'label' => 'Barang'],
            ],
            'manage' => ['absensi', 'barang'],
            'dashboard_subtitle' => 'Kelola absensi, persediaan barang, dan proyek',
        ],
        'manager' => [
            'pages' => ['dashboard', 'proyek', 'keuangan', 'barang', 'absensi'],
            'menu' => [
                'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
                'proyek'    => ['icon' => '◫', 'label' => 'Proyek'],
                'keuangan'  => ['icon' => '◪', 'label' => 'Keuangan'],
                'absensi'   => ['icon' => '◩', 'label' => 'Absensi'],
                'barang'    => ['icon' => '◧', 'label' => 'Barang'],
            ],
            'manage' => ['proyek', 'keuangan'],
            'dashboard_subtitle' => 'Pantau proyek dan laporan keuangan',
        ],
        'staff' => [
            'pages' => [],
            'menu' => [],
            'manage' => [],
            'dashboard_subtitle' => '',
        ],
    ];
}

function roleCanAccessPage(string $page): bool
{
    $role = getUserRole();
    $perms = getRolePermissions();

    if (!isset($perms[$role])) {
        return false;
    }

    return in_array($page, $perms[$role]['pages'], true);
}

function getManagedModules(): array
{
    $role = getUserRole();
    $perms = getRolePermissions();

    return $perms[$role]['manage'] ?? [];
}

function roleCanManage(string $module): bool
{
    return in_array($module, getManagedModules(), true);
}

function requireManagerPermission(string $module): void
{
    if (!roleCanManage($module)) {
        $_SESSION['error'] = 'Anda tidak berwenang melakukan aksi ini.';
        header('Location: ' . BASE_URL . '/public/index.php?page=dashboard');
        exit;
    }
}

function getMenuForRole(?string $role = null): array
{
    $role = $role ?? getUserRole();
    $perms = getRolePermissions();

    return $perms[$role]['menu'] ?? [];
}

function getRoleLabel(?string $role = null): string
{
    $labels = [
        'admin'   => 'Administrator',
        'manager' => 'Manager',
        'staff'   => 'Staff',
    ];

    return $labels[$role ?? getUserRole()] ?? 'Pengguna';
}
