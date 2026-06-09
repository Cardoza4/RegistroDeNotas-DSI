<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'administrador')) {
    header("Location: index.php?action=login");
    exit();
}
$esAdmin = ($_SESSION['rol'] === 'administrador');
$nombreUsuario = isset($_SESSION['nombre']) ? $_SESSION['nombre'] . ' ' . $_SESSION['apellido'] : 'Usuario Institucional';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA Notes - Menú Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-dark { background-color: #0f172a; }
        .welcome-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border: none; border-radius: 16px; }
        .menu-card { border: none; border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; background-color: white; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05); }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
        .icon-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php?action=dashboard">
                <img src="/registro_academico/img/logo_inca.png" alt="Logo" height="30" class="me-2"> INCA NOTES
            </a>
            <div class="ms-auto">
                <a href="index.php?action=logout" class="btn btn-sm btn-outline-light fw-bold">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card welcome-card p-4 p-md-5 mb-5 shadow-sm">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary px-3 py-2 mb-2 text-uppercase fw-bold"><?php echo $_SESSION['rol']; ?></span>
                    <h1 class="fw-bold mb-2">¡Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h1>
                    <p class="text-slate-300 mb-0 opacity-75">Panel de control unificado del Instituto Noé Canjura. Selecciona una opción para gestionar el ciclo académico actual.</p>
                </div>
                <div class="col-md-4 text-end d-none d-md-block">
                    <i class="fa-solid fa-graduation-cap fa-5x opacity-25"></i>
                </div>
            </div>
        </div>

        <h5 class="text-secondary fw-bold text-uppercase mb-4 small tracking-wider">Módulos Disponibles</h5>
        <div class="row g-4">

            <div class="col-md-6 col-lg-4">
                <div class="card menu-card p-4 h-100" onclick="window.location.href='index.php?action=notas';">
                    <div class="icon-circle bg-success text-white">
                        <i class="fa-solid fa-calculator fa-xl"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Control de Notas</h5>
                    <p class="text-muted small mb-0">Registrar, promediar (35%, 35%, 30%) y evaluar el rendimiento académico por materias.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card menu-card p-4 h-100" onclick="window.location.href='index.php?action=notas';">
                    <div class="icon-circle bg-primary text-white">
                        <i class="fa-solid fa-user-plus fa-xl"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Matrícula y Alumnos</h5>
                    <p class="text-muted small mb-0">Inscribir nuevos estudiantes con NIE institucional o modificar expedientes personales.</p>
                </div>
            </div>

            <?php if ($esAdmin): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card menu-card p-4 h-100 border border-primary border-opacity-25" onclick="window.location.href='index.php?action=notas&tab=docentes';">
                    <div class="icon-circle bg-dark text-white">
                        <i class="fa-solid fa-user-tie fa-xl"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Gestión de Docentes</h5>
                    <p class="text-muted small mb-0">Módulo técnico de administración. Dar de alta o baja cuentas del cuerpo de profesores.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-md-6 col-lg-4">
                <div class="card menu-card p-4 h-100 opacity-75" onclick="alert('Módulo de perfil en mantenimiento. Para cambios de contraseña o credenciales, contactar a Soporte Técnico.');">
                    <div class="icon-circle bg-warning text-white">
                        <i class="fa-solid fa-user-gear fa-xl"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Datos Personales</h5>
                    <p class="text-muted small mb-0">Actualizar información de contacto, fotografía de perfil y credenciales de acceso.</p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>