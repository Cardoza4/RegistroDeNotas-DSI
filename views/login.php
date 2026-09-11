<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Si ya existe sesión, redirigir al panel
if (isset($_SESSION['rol'])) {
    header("Location: index.php?action=dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Sistema de Control Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Barra Superior Estilo Institucional */
        .top-nav {
            background-color: #1e293b;
            color: #ffffff;
            padding: 10px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        /* Insignia Circular para el Logo Institucional */
        .brand-logo-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            flex-shrink: 0;
        }

        .brand-logo-circle img {
            width: 82%;
            height: 82%;
            object-fit: contain;
            display: block;
        }

        .institution-text h6 {
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        .institution-text small {
            font-size: 0.72rem;
            color: #94a3b8;
            letter-spacing: 1px;
        }

        /* Contenedor Principal Centrado */
        .main-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            border: 1px solid #e2e8f0;
            padding: 38px 32px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-login {
            background-color: #2563eb;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background-color: #1d4ed8;
        }

        .security-notice {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.8rem;
            color: #64748b;
        }

        footer {
            font-size: 0.8rem;
            color: #94a3b8;
            padding-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

   <!-- Cabecera Institucional con Enlaces a Redes Sociales -->
<header class="top-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div class="brand-logo-circle">
                <img src="img/logo_inca.png" alt="Logo INCA">
            </div>
            <div class="institution-text d-none d-sm-block">
                <h6 class="fw-bold mb-0 text-white text-uppercase">Instituto Nacional Noé Canjura</h6>
                <small class="fw-semibold text-uppercase">INCA NOTES — Plataforma de Gestión Académica</small>
            </div>
        </div>

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
        </div>
    </div>
</header>

    <!-- Área de Acceso Centrada -->
    <main class="main-wrapper">
        <div class="login-card">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-3 border shadow-sm" style="width: 58px; height: 58px;">
                    <i class="fa-solid fa-graduation-cap fa-xl"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Iniciar Sesión</h4>
                <p class="text-muted small mb-0">Ingresa tus credenciales para acceder al sistema institucional</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center small py-2 border-0 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Credenciales de acceso incorrectas.
            </div>
            <?php endif; ?>

            <form action="index.php?action=login" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Usuario / NIE / Carné:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" class="form-control border-start-0" placeholder="Ej: 250525 o carné docente" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Contraseña:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 text-white shadow-sm mb-3">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Ingresar al Sistema
                </button>
            </form>

            <!-- Aviso de Seguridad Institucional -->
            <div class="security-notice text-center mt-3">
                <i class="fa-solid fa-shield-halved text-secondary me-1"></i>
                <span>Acceso restringido. Las cuentas de estudiantes y docentes son gestionadas exclusivamente por la administración.</span>
            </div>

        </div>
    </main>

    <footer>
        <div class="container">
            &copy; 2026 INCA Notes — Sistema de Registro de Notas. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>