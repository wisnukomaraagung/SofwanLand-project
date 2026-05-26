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
            'pages' => ['dashboard', 'absensi', 'barang'],
            'menu' => [
                'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
                'absensi'   => ['icon' => '◩', 'label' => 'Absensi'],
                'barang'    => ['icon' => '◧', 'label' => 'Barang'],
            ],
            'dashboard_subtitle' => 'Kelola absensi dan persediaan barang',
        ],
        'manager' => [
            'pages' => ['dashboard', 'proyek', 'keuangan'],
            'menu' => [
                'dashboard' => ['icon' => '◈', 'label' => 'Dashboard'],
                'proyek'    => ['icon' => '◫', 'label' => 'Proyek'],
                'keuangan'  => ['icon' => '◪', 'label' => 'Keuangan'],
            ],
            'dashboard_subtitle' => 'Pantau proyek dan laporan keuangan',
        ],
        'staff' => [
            'pages' => [],
            'menu' => [],
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
