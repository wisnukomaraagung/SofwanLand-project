<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sofwan Land</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #f0f0f0 0%, #e8e8e8 50%, #f5f5f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #333 0%, #666 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: white;
        }

        .company-name {
            font-size: 28px;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .tagline {
            font-size: 12px;
            color: #999;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-group.last-input {
            margin-bottom: 12px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #999;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            transition: all 0.3s ease;
            background-color: #fafafa;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #333;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.05);
        }

        input::placeholder {
            color: #999;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            cursor: pointer;
            color: #999;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #333;
        }

        .form-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #333;
            border-radius: 4px;
        }

        .checkbox-label {
            color: #333;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .forgot-password {
            color: #666;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #333;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: #000;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            background: #222;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #666;
        }

        .login-footer a {
            color: #333;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .login-footer a:hover {
            color: #000;
            text-decoration: underline;
        }

        .page-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }

        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background-color: #efe;
            color: #3c3;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #3c3;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 40px 24px;
            }

            .company-name {
                font-size: 24px;
            }

            input[type="email"],
            input[type="password"] {
                padding: 12px 14px 12px 44px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="logo">
                    <img src="<?= BASE_URL ?>/public/assets/logo_pt.png" alt="Sofwan Land Logo">
                </div>
                <div class="company-name">SOFWAN</div>
                <div class="tagline">L A N D</div>
                <div style="margin-top: 8px; font-size: 11px; color: #999; letter-spacing: 0.5px;">'a new way of life'</div>
            </div>

            <!-- Error/Success Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success-message">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="<?= BASE_URL ?>/?page=login&action=authenticate">
                <!-- Email -->
                <div class="form-group last-input">
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="email" name="email" placeholder="Email" required
                               value="<?= isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : '' ?>">
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group last-input">
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="passwordInput" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                            <span id="toggleIcon">👁️</span>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-bottom">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="rememberCheckbox">
                        <span class="checkbox-label">Ingat Saya</span>
                    </label>
                    <a href="<?= BASE_URL ?>/?page=forgot-password" class="forgot-password">Lupa password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                Tidak punya akun? <a href="javascript:void(0);">Hubungi Admin</a>
            </div>
        </div>

        <div class="page-footer">
            © 2025 Sofwan Land. Semua hak cipta dilindungi.
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('toggleIcon');

            if (input.type === 'password') {
                input.type = 'text';
                toggleIcon.textContent = '🙈';
            } else {
                input.type = 'password';
                toggleIcon.textContent = '👁️';
            }
        }

        // Remember checkbox state
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            const rememberCheckbox = document.querySelector('input[name="remember"]');

            if (emailInput.value) {
                rememberCheckbox.checked = true;
            }
        });
    </script>
</body>
</html>
