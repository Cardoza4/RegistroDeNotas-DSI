<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=dashboard');
    exit;
}
<<<<<<< HEAD
$errorMsg = $error ?? '';
=======
<<<<<<< Updated upstream
=======
$errorMsg = $error ?? '';
$timeoutActivo = isset($_GET['timeout']) && $_GET['timeout'] == '1';
>>>>>>> Stashed changes
>>>>>>> dev/oswaldo-cardoza
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Instituto Noé Canjura</title>
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
<<<<<<< HEAD
=======
<<<<<<< Updated upstream
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
=======
>>>>>>> dev/oswaldo-cardoza
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
<<<<<<< HEAD
=======
        }

        /* PBI-54: Alerta institucional por inactividad */
        .alert-timeout {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 0.85rem 1rem;
            border-radius: 8px;
            font-size: 0.86rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.45;
        }

        .alert-timeout i {
            color: #d97706;
            font-size: 1.05rem;
            margin-top: 2px;
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
>>>>>>> Stashed changes
            background-color: #f8fafc;
>>>>>>> dev/oswaldo-cardoza
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
                <h2>INSTITUTO NOÉ CANJURA</h2>
                <p>INCA NOTES — PLATAFORMA DE GESTIÓN ACADÉMICA</p>
            </div>
        </div>
<<<<<<< HEAD
        <div class="social-icons">
            <a href="#" class="social-icon-btn" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-icon-btn" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            <a href="#" class="social-icon-btn" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
=======
<<<<<<< Updated upstream
=======
        <div class="social-icons">
            <a href="https://www.facebook.com/institutonoecanjura.priv" class="social-icon-btn" title="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://www.tiktok.com/@noe.canjura?_r=1&_t=ZS-99Y7F1ZrQfA" class="social-icon-btn" title="TikTok" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-tiktok"></i></a>
            <a href="https://www.instagram.com/noe.canjura/" class="social-icon-btn" title="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </header>
>>>>>>> Stashed changes

        <!-- Redes Sociales Oficiales -->
        <div class="d-flex align-items-center gap-2">
            <!-- Facebook Oficial INCA -->
            <a href="https://www.facebook.com/share/1FERnJQYkz/" 
               target="_blank" 
               rel="noopener noreferrer" 
               title="Facebook Institucional"
               style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); color: #ffffff; text-decoration: none; transition: all 0.2s ease;"
               onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='scale(1.08)';"
               onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='scale(1)';">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>

            <!-- TikTok Oficial INCA -->
            <a href="https://www.tiktok.com/@noe.canjura?_r=1&_t=ZS-99Y7F1ZrQfA" 
               target="_blank" 
               rel="noopener noreferrer" 
               title="TikTok Institucional"
               style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); color: #ffffff; text-decoration: none; transition: all 0.2s ease;"
               onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='scale(1.08)';"
               onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='scale(1)';">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                </svg>
            </a>

            <!-- Instagram Oficial INCA -->
            <a href="https://www.instagram.com/noe.canjura?stkn=bDNscXE0YjBlZXlv" 
               target="_blank" 
               rel="noopener noreferrer" 
               title="Instagram Institucional"
               style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: rgba(255, 255, 255, 0.15); color: #ffffff; text-decoration: none; transition: all 0.2s ease;"
               onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='scale(1.08)';"
               onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'; this.style.transform='scale(1)';">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </a>
>>>>>>> dev/oswaldo-cardoza
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

<<<<<<< HEAD
=======
<<<<<<< Updated upstream
            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center small py-2 border-0 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Credenciales de acceso incorrectas.
            </div>
=======
            <!-- PBI-54 Criterio 3: Alerta institucional visible tras expulsión por inactividad -->
            <?php if ($timeoutActivo): ?>
                <div class="alert-timeout">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Tu sesión ha finalizado por motivos de seguridad tras 5 minutos de inactividad. Por favor, inicia sesión nuevamente.</span>
                </div>
            <?php endif; ?>

>>>>>>> dev/oswaldo-cardoza
            <?php if (!empty($errorMsg)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($errorMsg) ?></span>
                </div>
<<<<<<< HEAD
=======
>>>>>>> Stashed changes
>>>>>>> dev/oswaldo-cardoza
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