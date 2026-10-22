<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$rolUsuario = strtoupper(trim($_SESSION['rol'] ?? 'DOCENTE'));
$esAdmin = ($rolUsuario === 'ADMIN' || $rolUsuario === 'ADMINISTRADOR');
$esDocente = ($rolUsuario === 'DOCENTE');
$esEstudiante = ($rolUsuario === 'ESTUDIANTE');

$nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario';
$fotoPerfil = trim($_SESSION['foto'] ?? '');

// Obtener el ID real del estudiante vinculado al usuario actual para sus calificaciones
$estudianteIdReal = $_SESSION['usuario_id'];
try {
    global $pdo;
    $dbConn = $dbConn ?? $pdo ?? null;
    if (!$dbConn && class_exists('Conexion')) {
        $dbConn = (new Conexion())->conectar();
    }
    if ($dbConn) {
        $stmtB = $dbConn->prepare("SELECT id FROM estudiantes WHERE correo = :c OR nie = :c OR id = :id LIMIT 1");
        $stmtB->execute([':c' => $_SESSION['usuario'] ?? '', ':id' => $_SESSION['usuario_id']]);
        $resB = $stmtB->fetch(PDO::FETCH_ASSOC);
        if ($resB) {
            $estudianteIdReal = $resB['id'];
        }
    }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logo_inca.png">
    <title>INCA NOTES - Panel Principal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 14px;
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

        /* NAVBAR */
        .navbar {
            background: #090e17;
            color: white;
            padding: 0.85rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
        }

        .brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 50%;
            background: white;
            padding: 2px;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar-mini {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .user-avatar-mini img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
            text-align: right;
        }

        .user-info-text .user-name {
            font-size: 0.88rem;
            font-weight: 700;
            color: #ffffff;
        }

        .user-info-text .user-role {
            font-size: 0.74rem;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-logout {
            color: #ffffff;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 0.45rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background: #ef4444;
            border-color: #ef4444;
        }

        /* CONTAINER */
        .container {
            flex: 1;
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        /* BANNER DE BIENVENIDA */
        .welcome-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: var(--radius);
            padding: 2.25rem 2.5rem;
            color: white;
            margin-bottom: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-hero::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(37, 99, 235, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }

        .welcome-text h1 {
            font-size: 1.85rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
            letter-spacing: -0.5px;
        }

        .welcome-text p {
            color: #94a3b8;
            font-size: 0.95rem;
            max-width: 650px;
            line-height: 1.5;
        }

        .hero-badge-rol {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #93c5fd;
            backdrop-filter: blur(4px);
        }

        /* GRID DE MÓDULOS */
        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .module-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .module-card-top {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .module-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            flex-shrink: 0;
        }

        .icon-blue   { background: #eff6ff; color: #2563eb; }
        .icon-green  { background: #f0fdf4; color: #16a34a; }
        .icon-purple { background: #f5f3ff; color: #7c3aed; }
        .icon-amber  { background: #fefce8; color: #d97706; }
        .icon-cyan   { background: #ecfeff; color: #0891b2; }
        .icon-indigo { background: #eef2ff; color: #4f46e5; }
        .icon-gold   { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #b45309; border: 1px solid #fcd34d; }

        .module-header-text h3 {
            font-size: 1.12rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .module-header-text p {
            font-size: 0.86rem;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .module-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid var(--border);
            padding-top: 1rem;
            margin-top: 0.5rem;
        }

        .module-tag {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .tag-blue   { background: #eff6ff; color: #1d4ed8; }
        .tag-green  { background: #f0fdf4; color: #15803d; }
        .tag-purple { background: #f5f3ff; color: #6d28d9; }
        .tag-amber  { background: #fefce8; color: #b45309; }
        .tag-cyan   { background: #ecfeff; color: #0e7490; }
        .tag-indigo { background: #eef2ff; color: #4338ca; }
        .tag-gold   { background: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }

        .module-link-arrow {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: transform 0.2s;
        }

        .module-card:hover .module-link-arrow {
            transform: translateX(4px);
        }

        /* TARJETA DESTACADA CUADRO DE HONOR */
        .card-cuadro-honor {
            border: 2px solid #fef08a;
            background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.08);
        }

        .card-cuadro-honor:hover {
            border-color: #f59e0b;
            box-shadow: 0 12px 30px rgba(217, 119, 6, 0.18);
        }

        .card-cuadro-honor .module-link-arrow {
            color: #b45309;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 2rem 1.5rem;
            color: var(--text-muted);
            font-size: 0.82rem;
            border-top: 1px solid var(--border);
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- NAVEGACIÓN SUPERIOR -->
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <img src="img/logo_inca.png" alt="Logo INCA" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            <span>INCA NOTES</span>
        </a>
        <div class="user-nav">
            <div class="user-badge">
                <div class="user-avatar-mini">
                    <?php if (!empty($fotoPerfil) && file_exists(__DIR__ . '/../uploads/' . basename($fotoPerfil))): ?>
                        <img src="uploads/<?= htmlspecialchars(basename($fotoPerfil)) ?>" alt="Avatar">
                    <?php else: ?>
                        <i class="fa-solid fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="user-info-text">
                    <span class="user-name"><?= htmlspecialchars($nombreUsuario) ?></span>
                    <span class="user-role"><?= htmlspecialchars($rolUsuario) ?></span>
                </div>
            </div>
            <a href="index.php?action=logout" class="btn-logout" title="Cerrar sesión segura">
                <i class="fa-solid fa-power-off"></i> Salir
            </a>
        </div>
    </nav>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="container">
        <!-- BANNER DE BIENVENIDA -->
        <div class="welcome-hero">
            <div class="welcome-text">
                <h1>¡Bienvenido, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
                <p>Sistema Institucional de Gestión y Control de Evaluaciones Académicas del Instituto Nacional Noé Canjura (INCA).</p>
            </div>
            <div>
                <span class="hero-badge-rol">
                    <i class="fa-solid fa-shield-halved"></i> Rol Activo: <?= htmlspecialchars($rolUsuario) ?>
                </span>
            </div>
        </div>

        <h2 class="section-title">
            <i class="fa-solid fa-layer-group"></i> Módulos y Servicios del Sistema
        </h2>

        <!-- GRID DE TARJETAS / MÓDULOS SEPARADO POR ROL -->
        <div class="modules-grid">

            <?php if ($esAdmin): ?>
                <!-- ================= TARJETAS EXCLUSIVAS DE ADMINISTRADOR ================= -->
                <a href="index.php?action=gestion_notas" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-blue">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Control de Alumnos y Notas</h3>
                                <p>Registro de evaluaciones, matriz por sección, sustitución por recuperación y reportes oficiales.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-blue">Académico</span>
                        <span class="module-link-arrow">Acceder <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=cuadro_honor" class="module-card card-cuadro-honor">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-gold">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Cuadro de Honor y Ranking</h3>
                                <p>Reconocimiento a la excelencia académica con Podio Olímpico (Top 3) y ranking de mejores promedios globales (≥ 8.0).</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-gold"><i class="fa-solid fa-medal"></i> Excelencia</span>
                        <span class="module-link-arrow">Ver Podio <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=gestion_docentes" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-purple">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Gestión de Personal Docente</h3>
                                <p>Asignación de asignaturas, grados académicos impartidos, escalafón y sección de orientación.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-purple">Administración</span>
                        <span class="module-link-arrow">Gestionar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=registro_usuarios" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-indigo">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Registro de Nuevas Cuentas</h3>
                                <p>Crear accesos con credenciales y roles para estudiantes, docentes y administradores.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-indigo">Cuentas</span>
                        <span class="module-link-arrow">Registrar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=usuarios_lista" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-cyan">
                                <i class="fa-solid fa-address-book"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Directorio de Usuarios</h3>
                                <p>Padrón institucional de alumnos, docentes y administradores con consulta rápida y fichas de contacto.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-cyan">Directorio</span>
                        <span class="module-link-arrow">Consultar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=datos_personales" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-amber">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Mi Perfil y Seguridad</h3>
                                <p>Actualización de datos personales, teléfono, cambio de contraseña y actualización de foto de perfil.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-amber">Configuración</span>
                        <span class="module-link-arrow">Editar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

            <?php elseif ($esEstudiante): ?>
                <!-- ================= TARJETAS EXCLUSIVAS DE ESTUDIANTE ================= -->
                <a href="index.php?action=boleta_notas&estudiante_id=<?= $estudianteIdReal ?>" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-blue">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Mis Calificaciones</h3>
                                <p>Consultar calificaciones trimestrales, promedios por materia y estado académico oficial.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-blue">Mis Calificaciones</span>
                        <span class="module-link-arrow">Ver Boleta <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=datos_personales" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-amber">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Mi Perfil y Seguridad</h3>
                                <p>Visualizar tu expediente académico y actualizar tu contraseña de acceso personal.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-amber">Configuración</span>
                        <span class="module-link-arrow">Editar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

            <?php else: ?>
                <!-- ================= TARJETAS EXCLUSIVAS DE DOCENTE (SOLO 3 TARJETAS) ================= -->
                <a href="index.php?action=gestion_notas" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-blue">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Control de Alumnos y Notas</h3>
                                <p>Registro de evaluaciones, matriz por sección, sustitución por recuperación y reportes oficiales.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-blue">Académico</span>
                        <span class="module-link-arrow">Acceder <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=cuadro_honor" class="module-card card-cuadro-honor">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-gold">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Cuadro de Honor y Ranking</h3>
                                <p>Reconocimiento a la excelencia académica con Podio Olímpico (Top 3) y ranking de mejores promedios globales (≥ 8.0).</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-gold"><i class="fa-solid fa-medal"></i> Excelencia</span>
                        <span class="module-link-arrow">Ver Podio <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>

                <a href="index.php?action=datos_personales" class="module-card">
                    <div>
                        <div class="module-card-top">
                            <div class="module-icon icon-amber">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            <div class="module-header-text">
                                <h3>Mi Perfil y Seguridad</h3>
                                <p>Actualización de datos personales, teléfono, cambio de contraseña y actualización de foto de perfil.</p>
                            </div>
                        </div>
                    </div>
                    <div class="module-card-bottom">
                        <span class="module-tag tag-amber">Configuración</span>
                        <span class="module-link-arrow">Editar <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- Pie de página integrado institucionalmente -->
    <?php 
    if (file_exists(__DIR__ . '/footer.php')) {
        include __DIR__ . '/footer.php';
    } else {
        echo '<footer class="footer"><p>&copy; ' . date('Y') . ' Instituto Nacional Noé Canjura (INCA). Sistema de Control Académico y Gestión de Notas.</p></footer>';
    }
    ?>
</body>
</html>