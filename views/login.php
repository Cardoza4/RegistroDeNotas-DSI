<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=dashboard');
    exit;
}
$errorMsg = $error ?? '';
$timeoutActivo = isset($_GET['timeout']) && $_GET['timeout'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Instituto Noé Canjura</title>
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1d4ed8;
            --primary-hover: #1e3a8a;
            --bg-body: #f1f5f9;
            --nav-bg: #0b1a30;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* BARRA SUPERIOR INSTITUCIONAL (Intacta) */
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
            margin-bottom: 2px;
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
            background: #2563eb;
        }

        /* CONTENEDOR PRINCIPAL */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
        }

        .ues-card {
            display: flex;
            width: 100%;
            max-width: 1150px;
            min-height: 600px;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        /* MITAD IZQUIERDA (Imagen del Águila como portada) */
        .ues-left {
            width: 50%;
            background: #0f172a;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #f1f5f9;
        }

        .ues-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* MITAD DERECHA (Formulario de Login) */
        .ues-right {
            width: 50%;
            padding: 3.5rem 5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Logo de INCA NOTES arriba del formulario */
        .app-logo {
            width: 240px;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        /* Alertas */
        .alert-box {
            padding: 0.85rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-timeout { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }

        /* Estilo de Inputs */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            transition: border-color 0.2s;
        }
        
        .input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }

        .input-wrapper i.left-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 1rem;
        }

        .input-wrapper input {
            width: 100%;
            padding: 1rem 2.5rem 1rem 2.75rem;
            border: none;
            background: transparent;
            font-size: 0.95rem;
            color: var(--text-main);
            outline: none;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1rem;
            padding: 4px;
        }
        .toggle-password:hover { color: var(--text-main); }

        /* Texto informativo institucional */
        .info-credenciales {
            text-align: center;
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.45;
            padding: 0 10px;
        }

        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 1rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 1.5rem;
        }
        .btn-submit:hover { background: var(--primary-hover); }

        .login-footer-text {
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* Responsive para móviles */
        @media (max-width: 900px) {
            .ues-card { flex-direction: column; max-width: 500px; min-height: auto; }
            .ues-left { width: 100%; height: 250px; }
            .ues-right { width: 100%; padding: 2.5rem; }
            .institucional-nav { padding: 0.8rem 1.5rem; flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- BARRA INSTITUCIONAL INCA (Intacta) -->
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
        <div class="social-icons">
            <a href="https://www.facebook.com/institutonoecanjura.priv" class="social-icon-btn" title="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://www.tiktok.com/@noe.canjura?_r=1&_t=ZS-99Y7F1ZrQfA" class="social-icon-btn" title="TikTok" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-tiktok"></i></a>
            <a href="https://www.instagram.com/noe.canjura/" class="social-icon-btn" title="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL - DISEÑO DIVIDIDO -->
    <main class="main-content">
        <div class="ues-card">
            
            <!-- MITAD IZQUIERDA: Imagen de Designer.png (Águila) -->
            <div class="ues-left">
                <img src="img/Designer.png" alt="Instituto Noé Canjura" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=0f172a&color=fff&size=500'">
            </div>

            <!-- MITAD DERECHA: Formulario de Login con logo_inca.png arriba -->
            <div class="ues-right">
                <div class="form-header">
                    <img src="img/logo_inca.png" alt="INCA NOTES Sistema de Registro" class="app-logo" onerror="this.src='https://ui-avatars.com/api/?name=INCA+NOTES&background=fff&color=1d4ed8'">
                </div>

                <?php if ($timeoutActivo): ?>
                    <div class="alert-box alert-timeout">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Tu sesión ha finalizado por inactividad.
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert-box alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?= htmlspecialchars($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=login" method="POST">
                    
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user left-icon"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="Usuario / NIE" required autocomplete="username">
                    </div>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-key left-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()" title="Mostrar/Ocultar Contraseña">
                            <i class="fa-solid fa-eye" id="iconEye"></i>
                        </button>
                    </div>

                    <!-- Texto institucional -->
                    <p class="info-credenciales">Plataforma exclusiva para alumnos y docentes del INCA, si no conoce sus credenciales acercarse a Administración Académica</p>

                    <button type="submit" class="btn-submit">
                        Iniciar sesión (ingresar)
                    </button>
                    
                </form>

                <div class="login-footer-text">
                    Ingrese con su usuario institucional. No necesita<br>escribir <strong>@inca.edu.sv</strong>
                </div>
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