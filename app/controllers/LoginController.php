<?php

class LoginController {
    public function index() {
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/?page=dashboard');
            exit;
        }

        $error = null;
        $success = null;

        include BASE_PATH . '/app/views/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            $_SESSION['error'] = 'Email dan password harus diisi';
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }

        // Query dari database
        require_once BASE_PATH . '/config/database.php';
        $db = getDB();
        
        try {
            $query = "SELECT id, nama, email, password, role FROM users WHERE email = ? AND status = 'aktif'";
            $stmt = $db->prepare($query);
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                if (isset($_POST['remember'])) {
                    setcookie('email', $email, time() + (86400 * 30), '/');
                }

                header('Location: ' . BASE_URL . '/?page=dashboard');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        
        $_SESSION['error'] = 'Email atau password salah';
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
}
