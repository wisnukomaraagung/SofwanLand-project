<?php

class PenggunaController {
    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function index(): void {
        requireManagerPermission('pengguna');
        $users = $this->db->query('SELECT id, nama, email, role, status, created_at FROM users ORDER BY created_at DESC, id DESC')->fetchAll();
        $pageTitle = 'Pengguna';
        $pageSubtitle = 'Tambah dan lihat akun pengguna';
        require BASE_PATH . '/app/views/pengguna/index.php';
    }

    public function store(): void {
        requireManagerPermission('pengguna');
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $redirect = BASE_URL . '/public/index.php?page=pengguna';

        if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || !in_array($role, ['admin', 'manager', 'owner', 'user'], true)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Nama, email, role valid, dan password minimal 6 karakter wajib diisi.'];
            header('Location: ' . $redirect); exit;
        }

        try {
            $stmt = $this->db->prepare('INSERT INTO users (nama, email, password, role, status) VALUES (?, ?, ?, ?, \'aktif\')');
            $stmt->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengguna berhasil ditambahkan.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getCode() === '23000' ? 'Email sudah digunakan.' : 'Pengguna gagal ditambahkan.'];
        }
        header('Location: ' . $redirect); exit;
    }

    public function edit(int $id): void {
        requireManagerPermission('pengguna');
        $stmt = $this->db->prepare('SELECT id, nama, email, role, status FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Pengguna tidak ditemukan.'];
            header('Location: ' . BASE_URL . '/public/index.php?page=pengguna'); exit;
        }
        $pageTitle = 'Edit Pengguna';
        $pageSubtitle = 'Perbarui data akun pengguna';
        require BASE_PATH . '/app/views/pengguna/edit.php';
    }

    public function update(int $id): void {
        requireManagerPermission('pengguna');
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'aktif';
        $redirect = BASE_URL . '/public/index.php?page=pengguna';

        if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['admin', 'manager', 'owner', 'user'], true) || !in_array($status, ['aktif', 'nonaktif'], true) || ($password !== '' && strlen($password) < 6)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Data pengguna tidak valid. Password baru minimal 6 karakter.'];
            header('Location: ' . $redirect . '&action=edit&id=' . $id); exit;
        }
        if ($id === (int)($_SESSION['user_id'] ?? 0) && $status !== 'aktif') {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.'];
            header('Location: ' . $redirect . '&action=edit&id=' . $id); exit;
        }

        try {
            if ($password !== '') {
                $stmt = $this->db->prepare('UPDATE users SET nama = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?');
                $stmt->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT), $role, $status, $id]);
            } else {
                $stmt = $this->db->prepare('UPDATE users SET nama = ?, email = ?, role = ?, status = ? WHERE id = ?');
                $stmt->execute([$nama, $email, $role, $status, $id]);
            }
            if ($id === (int)($_SESSION['user_id'] ?? 0)) {
                $_SESSION['nama'] = $nama;
                $_SESSION['email'] = $email;
                $_SESSION['user_role'] = $role;
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Pengguna berhasil diperbarui.'];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $e->getCode() === '23000' ? 'Email sudah digunakan.' : 'Pengguna gagal diperbarui.'];
        }
        header('Location: ' . $redirect); exit;
    }

    public function delete(int $id): void {
        requireManagerPermission('pengguna');
        $redirect = BASE_URL . '/public/index.php?page=pengguna';
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Akun yang sedang digunakan tidak dapat dihapus.'];
            header('Location: ' . $redirect); exit;
        }
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => $stmt->rowCount() ? 'Pengguna berhasil dihapus.' : 'Pengguna tidak ditemukan.'];
        header('Location: ' . $redirect); exit;
    }
}
