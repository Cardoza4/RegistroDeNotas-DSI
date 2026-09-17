<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=dashboard');
    exit;
}
$errorMsg = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Instituto Nacional Noé Canjura</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-body: #f1f5f9;
            --nav-bg: #0b1a30;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #cbd5e1;
            --radius: 12px;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Barra Superior Institucional */
        .institucional-nav {
            background-color: var(--nav-bg);
            color: white;
            padding: 0.8rem 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .nav-brand-container {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .nav-logo-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .nav-logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 2px;
            border-radius: 50%;
        }

        .nav-titles h2 {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .nav-titles p {
            font-size: 0.75rem;
            color: #94a3b8;
            letter-spacing: 0.3px;
        }

        .social-icons {
            display: flex;
            gap: 12px;
        }

        .social-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.85rem;
            transition: background 0.2s;
        }

        .social-icon-btn:hover {
            background: var(--primary);
        }

        /* Contenedor Principal del Login */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 480px;
            padding: 2.5rem 3rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-icon-badge {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.4rem;
            border: 1px solid #bfdbfe;
        }

        .login-header h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.4;
        }

        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i.field-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 2.75rem 0.75rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 0.95rem;
            padding: 4px;
        }

        .toggle-password:hover {
            color: var(--text-main);
        }

        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 0.85rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
            margin-top: 1.5rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .login-footer-text {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

    <!-- Barra Institucional Superior -->
    <header class="institucional-nav">
        <div class="nav-brand-container">
            <div class="nav-logo-circle">
                <img src="img/logo_inca.png" alt="Logo INCA" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            </div>
            <div class="nav-titles">
                <h2>INSTITUTO NACIONAL NOÉ CANJURA</h2>
                <p>INCA NOTES — PLATAFORMA DE GESTIÓN ACADÉMICA</p>
            </div>
        </div>
        <div class="social-icons">
            <a href="#" class="social-icon-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-icon-btn" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" class="social-icon-btn" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </header>

    <!-- Contenido del Formulario de Inicio de Sesión -->
    <main class="main-content">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon-badge">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h1>Iniciar Sesión</h1>
                <p>Ingresa tus credenciales para acceder al sistema institucional</p>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
            <?php endif; ?>

            <form action="index.php?action=login" method="POST">
                <div class="form-group">
                    <label for="usuario">Usuario / NIE / Carné:</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user field-icon"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Ej: Administrador1" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-password" id="btnTogglePass" onclick="togglePasswordVisibility()" title="Mostrar/Ocultar Contraseña">
                            <i class="fa-solid fa-eye" id="iconEye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Ingresar al Sistema
                </button>
            </form>

            <div class="login-footer-text">
                Acceso restringido. Las cuentas de estudiantes y docentes son administradas institucionalmente.
            </div>
        </div>
    </main>

    <script>
        function togglePasswordVisibility() {
            const passInput = document.getElementById('password');
            const iconEye = document.getElementById('iconEye');

            if (passInput.type === 'password') {
                passInput.type = 'text';
                iconEye.classList.remove('fa-eye');
                iconEye.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                iconEye.classList.remove('fa-eye-slash');
                iconEye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>