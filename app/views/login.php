<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sofwan Land</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: #dde0e5;
            --bg-light: #e8eaee;
            --bg-lighter: #f0f2f5;
            --card-bg: rgba(255, 255, 255, 0.92);
            --text-dark: #111111;
            --text-mid: #555;
            --text-light: #888;
            --border: #e0e0e0;
            --input-bg: #f7f7f8;
            --input-focus: #ffffff;
            --btn-bg: #111111;
            --btn-hover: #222;
            --shadow-card: 0 20px 60px rgba(0,0,0,0.10), 0 4px 16px rgba(0,0,0,0.06);
            --shadow-btn: 0 6px 20px rgba(0,0,0,0.25);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }

        /* â”€â”€ Geometric Background â”€â”€ */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        /* Large diagonal panels */
        .bg-panel {
            position: absolute;
            background: linear-gradient(135deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.08) 100%);
            border: 1px solid rgba(255,255,255,0.7);
            border-radius: 32px;
            box-shadow: 0 8px 40px rgba(255,255,255,0.3) inset, 0 2px 8px rgba(0,0,0,0.04);
            backdrop-filter: blur(0px);
        }

        /* Top-left large diamond panel */
        .panel-1 {
            width: 560px;
            height: 560px;
            top: -160px;
            left: -160px;
            transform: rotate(45deg);
            border-radius: 60px;
            background: linear-gradient(135deg, rgba(255,255,255,0.70) 0%, rgba(245,246,248,0.15) 100%);
        }

        /* Bottom-right large diamond panel */
        .panel-2 {
            width: 520px;
            height: 520px;
            bottom: -160px;
            right: -140px;
            transform: rotate(45deg);
            border-radius: 60px;
            background: linear-gradient(135deg, rgba(255,255,255,0.65) 0%, rgba(240,242,245,0.1) 100%);
        }

        /* Narrow tall panel top-right */
        .panel-3 {
            width: 180px;
            height: 420px;
            top: -80px;
            right: 18%;
            transform: rotate(45deg);
            border-radius: 40px;
            background: linear-gradient(180deg, rgba(255,255,255,0.50) 0%, rgba(255,255,255,0.05) 100%);
        }

        /* Narrow tall panel bottom-left */
        .panel-4 {
            width: 160px;
            height: 380px;
            bottom: -60px;
            left: 20%;
            transform: rotate(45deg);
            border-radius: 40px;
            background: linear-gradient(180deg, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0.04) 100%);
        }

        /* Small accent panel top-right corner */
        .panel-5 {
            width: 100px;
            height: 280px;
            top: 5%;
            right: 6%;
            transform: rotate(45deg);
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.35) 0%, rgba(255,255,255,0.02) 100%);
        }

        /* Small accent panel bottom-left corner */
        .panel-6 {
            width: 90px;
            height: 240px;
            bottom: 8%;
            left: 8%;
            transform: rotate(45deg);
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.30) 0%, rgba(255,255,255,0.02) 100%);
        }

        /* â”€â”€ Main layout â”€â”€ */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* â”€â”€ Logo / Header â”€â”€ */
        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            filter: invert(1);
        }

        .logo-wrap {
            width: 240px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-name {
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 4px;
            color: var(--text-dark);
            line-height: 1;
            text-transform: uppercase;
        }

        .brand-sub-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            margin: 6px 0 8px;
        }

        .brand-sub-row::before,
        .brand-sub-row::after {
            content: '';
            display: block;
            height: 1.5px;
            width: 44px;
            background-color: var(--text-dark);
        }

        .brand-sub {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 6px;
            color: var(--text-dark);
            text-transform: uppercase;
        }

        .tagline {
            font-size: 13px;
            font-weight: 400;
            color: var(--text-mid);
            font-style: italic;
            letter-spacing: 0.3px;
        }

        /* â”€â”€ Card â”€â”€ */
        .login-card {
            width: 100%;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 36px 32px 30px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255,255,255,0.95);
        }

        /* â”€â”€ Messages â”€â”€ */
        .error-message {
            background-color: #fff0f0;
            color: #c33;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 12.5px;
            border-left: 3px solid #e55;
        }

        .success-message {
            background-color: #f0fff4;
            color: #2a7a4b;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 12.5px;
            border-left: 3px solid #3cb96e;
        }

        /* â”€â”€ Form â”€â”€ */
        .form-group {
            margin-bottom: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #555;
            display: flex;
            align-items: center;
            pointer-events: none;
            z-index: 1;
        }

        .icon-svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 14px 48px 14px 48px;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 400;
            font-family: 'Outfit', sans-serif;
            color: var(--text-dark);
            background: var(--input-bg);
            transition: all 0.25s ease;
            outline: none;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="text"]:focus {
            border-color: #bbb;
            background: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.04);
        }

        input::placeholder {
            color: #aaa;
            font-weight: 400;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            cursor: pointer;
            color: #aaa;
            background: none;
            border: none;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
            border-radius: 6px;
        }

        .password-toggle:hover {
            color: #555;
        }

        /* â”€â”€ Remember / Forgot row â”€â”€ */
        .form-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 10px 0 22px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
        }

        input[type="checkbox"] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: var(--text-dark);
            border-radius: 4px;
        }

        .checkbox-label {
            font-size: 12.5px;
            color: var(--text-mid);
            font-weight: 400;
        }

        .forgot-password {
            font-size: 12.5px;
            color: var(--text-mid);
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 400;
        }

        .forgot-password:hover {
            color: var(--text-dark);
        }

        /* â”€â”€ Login button â”€â”€ */
        .login-btn {
            width: 100%;
            padding: 15px;
            background: var(--btn-bg);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-btn);
            text-transform: uppercase;
        }

        .login-btn:hover {
            background: var(--btn-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.3);
        }

        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* â”€â”€ Footer inside card â”€â”€ */
        .login-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12.5px;
            color: var(--text-mid);
        }

        .login-footer a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 700;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* â”€â”€ Page footer â”€â”€ */
        .page-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 11px;
            color: #999;
            letter-spacing: 0.2px;
        }

        /* —— Contact Modal Styles —— */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            padding: 20px;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }

        .modal-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            border: 1px solid rgba(255,255,255,0.95);
            box-shadow: var(--shadow-card);
            padding: 30px;
            position: relative;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.active .modal-card {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-light);
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
        }

        .modal-close:hover {
            color: var(--text-dark);
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .modal-icon {
            width: 28px;
            height: 28px;
            fill: var(--text-dark);
        }

        .modal-header h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .modal-body p {
            font-size: 13.5px;
            color: var(--text-mid);
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .contact-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .contact-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-icon {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        .whatsapp-btn {
            background: #25d366;
            color: #fff;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }

        .whatsapp-btn:hover {
            background: #20ba59;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
        }

        .email-btn {
            background: var(--btn-bg);
            color: #fff;
            box-shadow: var(--shadow-btn);
        }

        .email-btn:hover {
            background: var(--btn-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }

        .modal-footer {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
        }

        .close-btn {
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text-mid);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .close-btn:hover {
            background: #f5f5f5;
            color: var(--text-dark);
            border-color: #ccc;
        }
    </style>
</head>
<body>

    <!-- Geometric Background -->
    <div class="bg-canvas">
        <div class="bg-panel panel-1"></div>
        <div class="bg-panel panel-2"></div>
        <div class="bg-panel panel-3"></div>
        <div class="bg-panel panel-4"></div>
        <div class="bg-panel panel-5"></div>
        <div class="bg-panel panel-6"></div>
    </div>

    <div class="login-container">

        <!-- Header -->
        <div class="login-header">
            <div class="logo-wrap">
                <img src="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/public/assets/LogoSofwan-Center-putih-nobg-e1743959592975.webp"
                     alt="Sofwan Land Logo"
                     class="logo-img">
            </div>
            <div class="tagline">'a new way of life'</div>
        </div>

        <!-- Login Card -->
        <div class="login-card">

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

            <?php
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $emailCookie = isset($_COOKIE['email']) ? htmlspecialchars($_COOKIE['email']) : '';
            ?>
            <form method="POST" action="<?= $baseUrl ?>/?page=login&action=authenticate">

                <!-- Email -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg class="icon-svg" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </span>
                        <input type="email" name="email" placeholder="Email" required
                               value="<?= $emailCookie ?>">
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg class="icon-svg" viewBox="0 0 24 24">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </span>
                        <input type="password" id="passwordInput" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Tampilkan password">
                            <svg id="eyeIcon" class="icon-svg" viewBox="0 0 24 24">
                                <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="form-bottom">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" id="rememberCheckbox"
                               <?= $emailCookie ? 'checked' : '' ?>>
                        <span class="checkbox-label">Ingat Saya</span>
                    </label>
                    <a href="<?= $baseUrl ?>/?page=forgot-password" class="forgot-password">Lupa password?</a>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>
            </form>

            <div class="login-footer">
                Tidak punya akun? <a href="javascript:void(0);" onclick="openContactModal()">Hubungi Admin</a>
            </div>
        </div>

        <div class="page-footer">
            &copy; 2025 Sofwan Land. Semua hak cipta dilindungi.
        </div>
    </div>

    <!-- Contact Admin Modal -->
    <div id="contactModal" class="modal-overlay" onclick="event.target === this && closeContactModal()">
        <div class="modal-card">
            <button class="modal-close" onclick="closeContactModal()">&times;</button>
            <div class="modal-header">
                <svg class="modal-icon" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
                <h3>Hubungi Admin</h3>
            </div>
            <div class="modal-body">
                <p>Pendaftaran akun baru dilakukan oleh Administrator. Silakan hubungi admin Sofwan Land melalui saluran komunikasi di bawah ini:</p>
                <div class="contact-methods">
                    <!-- WhatsApp -->
                    <a href="https://wa.me/6283829205873?text=Halo%20Admin%20Sofwan%20Land,%20saya%20ingin%20meminta%20pembuatan%20akun%20baru." 
                       target="_blank" class="contact-btn whatsapp-btn">
                        <svg class="btn-icon" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.864-9.864.002-2.637-1.03-5.115-2.905-6.99-1.876-1.875-4.355-2.907-6.993-2.909-5.439 0-9.865 4.424-9.869 9.87-.001 1.795.486 3.548 1.412 5.11l-.98 3.578 3.662-.96zm12.635-7.79c-.29-.145-1.716-.847-1.982-.944-.266-.097-.46-.145-.653.145-.193.29-.747.944-.916 1.137-.168.193-.337.217-.627.072-2.975-1.488-4.086-2.578-4.877-3.93-.2-.343-.02-.529.15-.7l.478-.557c.168-.193.224-.326.337-.543.113-.217.056-.41-.028-.555-.084-.145-.653-1.57-.894-2.152-.236-.569-.47-.49-.653-.5-.17-.008-.361-.01-.554-.01-.193 0-.507.072-.772.361-.266.29-1.013.99-1.013 2.415 0 1.425 1.037 2.802 1.182 2.996.145.193 2.04 3.115 4.94 4.373.69.299 1.229.478 1.65.612.693.22 1.324.19 1.822.115.556-.084 1.716-.7 1.958-1.376.242-.677.242-1.256.17-1.376-.073-.12-.266-.193-.556-.339z"/>
                        </svg>
                        Hubungi via WhatsApp
                    </a>

                    <!-- Email -->
                    <a href="mailto:nazwaazahrarahman@gmail.com?subject=Pengajuan%20Akun%20Sofwan%20Land&body=Halo%20Admin,%20saya%20ingin%20meminta%20pembuatan%20akun%20baru%20untuk%20akses%20ke%20Sofwan%20Land.%20%0A%0ANama%20Lengkap%3A%20%0AJabatan%3A%20%0AEmail%20yang%20Diajukan%3A%20" 
                       class="contact-btn email-btn">
                        <svg class="btn-icon" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        Hubungi via Email
                    </a>
                </div>

                <!-- Fallback Email Copy Widget -->
                <div class="email-copy-section" style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed rgba(0,0,0,0.1); text-align: center;">
                    <span style="font-size: 12px; color: var(--text-light); display: block; margin-bottom: 8px;">Atau salin email admin di bawah ini:</span>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(0,0,0,0.03); padding: 8px 12px; border-radius: 10px; border: 1px solid var(--border); max-width: 100%; overflow: hidden;">
                        <span id="adminEmailText" style="font-family: 'Outfit', sans-serif; font-size: 13px; color: var(--text-dark); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">nazwaazahrarahman@gmail.com</span>
                        <button onclick="copyAdminEmail()" style="background: none; border: none; cursor: pointer; color: var(--text-mid); padding: 4px; display: flex; align-items: center; justify-content: center; transition: color 0.2s;" title="Salin Email">
                            <svg id="copyIcon" style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24">
                                <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
                            </svg>
                            <span id="copySuccessMsg" style="font-size: 11px; font-weight: 600; color: #2a7a4b; margin-left: 4px; display: none;">Tersalin!</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="close-btn" onclick="closeContactModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon  = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';

            icon.innerHTML = isHidden
                ? '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>'
                : '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
        }

        function openContactModal() {
            document.getElementById('contactModal').classList.add('active');
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.remove('active');
        }

        function copyAdminEmail() {
            const emailText = document.getElementById('adminEmailText').innerText;
            navigator.clipboard.writeText(emailText).then(() => {
                const icon = document.getElementById('copyIcon');
                const msg = document.getElementById('copySuccessMsg');
                
                icon.style.display = 'none';
                msg.style.display = 'inline';
                
                setTimeout(() => {
                    icon.style.display = 'block';
                    msg.style.display = 'none';
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin teks: ', err);
            });
        }
    </script>
</body>
</html>
