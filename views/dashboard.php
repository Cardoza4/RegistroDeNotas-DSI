<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$rolUsuario = strtoupper($_SESSION['rol'] ?? 'DOCENTE');
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
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
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

        .navbar {
            background: #090e17;
            color: white;
            padding: 0.85rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
        }

        .container {
            flex: 1;
            max-width: 1250px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #090e17 0%, #1e293b 100%);
            color: white;
            border-radius: var(--radius);
            padding: 2.5rem 3rem;
            margin-bottom: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
        }

        .welcome-banner::after {
            content: "\f501";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: -20px;
            bottom: -40px;
            font-size: 13rem;
            color: rgba(255, 255, 255, 0.03);
            pointer-events: none;
        }

        .badge-rol {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.85rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .welcome-banner h1 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
        }

        .welcome-banner p {
            font-size: 0.95rem;
            color: #94a3b8;
            max-width: 650px;
            line-height: 1.5;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.25rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 1.5rem;
        }

        .module-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            text-decoration: none;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .module-card:hover {
            transform: translateY(-3px);
            border-color: #cbd5e1;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
        }

        .module-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: white;
        }

        .icon-green { background: #10b981; }
        .icon-blue { background: #2563eb; }
        .icon-amber { background: #f59e0b; }
        .icon-purple { background: #7c3aed; }
        .icon-indigo { background: #4f46e5; }

        .module-info h3 {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.3rem;
        }

        .module-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .btn-logout {
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 0.4rem 1rem;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <div style="width:34px; height:34px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <img src="img/logo_inca.png" alt="Logo" style="width:28px; height:28px; object-fit:contain;" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            </div>
            <span>INCA NOTES</span>
        </a>
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="color:#94a3b8; font-size:0.88rem;">
                <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($nombreUsuario) ?>
            </span>
            <a href="index.php?action=logout" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <?php if ($rolUsuario === 'ADMIN' || $rolUsuario === 'ADMINISTRADOR'): ?>
            <!-- VISTA DE ADMINISTRADOR -->
            <div class="welcome-banner">
                <span class="badge-rol">Administrador</span>
                <h1>¡Bienvenido, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
                <p>Panel de control directivo del Instituto Noé Canjura. Administra expedientes, matrícula escolar, roles institucionales y calificaciones.</p>
            </div>

            <div class="section-title">Módulos Disponibles</div>
            <div class="modules-grid">
                <a href="index.php?action=gestion_notas" class="module-card">
                    <div class="module-icon icon-green"><i class="fa-solid fa-list-check"></i></div>
                    <div class="module-info">
                        <h3>Control de Notas</h3>
                        <p>Registrar, promediar (35%, 35%, 30%), editar expedientes y evaluar el rendimiento académico.</p>
                    </div>
                </a>
                <a href="index.php?action=matricula" class="module-card">
                    <div class="module-icon icon-blue"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="module-info">
                        <h3>Matrícula Escolar</h3>
                        <p>Inscribir nuevos estudiantes con NIE institucional, grado, sección y datos de encargado.</p>
                    </div>
                </a>
                <a href="index.php?action=registro_usuarios" class="module-card">
                    <div class="module-icon icon-indigo"><i class="fa-solid fa-user-gear"></i></div>
                    <div class="module-info">
                        <h3>Registro de Nuevas Cuentas</h3>
                        <p>Crear accesos con credenciales y roles para estudiantes, docentes y administradores.</p>
                    </div>
                </a>
                <a href="index.php?action=usuarios_lista" class="module-card">
                    <div class="module-icon icon-purple"><i class="fa-solid fa-address-book"></i></div>
                    <div class="module-info">
                        <h3>Directorio de Perfiles</h3>
                        <p>Consultar y filtrar todas las cuentas registradas por categorías (Estudiantes, Docentes, Administradores).</p>
                    </div>
                </a>
                <a href="index.php?action=datos_personales" class="module-card">
                    <div class="module-icon icon-amber"><i class="fa-solid fa-id-card"></i></div>
                    <div class="module-info">
                        <h3>Datos Personales</h3>
                        <p>Actualizar información de contacto, fotografía de perfil y credenciales de acceso.</p>
                    </div>
                </a>
            </div>

        <?php elseif ($rolUsuario === 'ESTUDIANTE'): ?>
            <!-- VISTA DE ESTUDIANTE -->
            <div class="welcome-banner">
                <span class="badge-rol">Estudiante</span>
                <h1>¡Bienvenido, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
                <p>Portal estudiantil del Instituto Noé Canjura. Consulta tus calificaciones oficiales por periodo y gestiona tus credenciales de acceso.</p>
            </div>

            <div class="section-title">Módulos Disponibles</div>
            <div class="modules-grid">
                <a href="index.php?action=boleta_notas&estudiante_id=<?= $estudianteIdReal ?>" class="module-card">
                    <div class="module-icon icon-green"><i class="fa-solid fa-star"></i></div>
                    <div class="module-info">
                        <h3>Mis Calificaciones</h3>
                        <p>Consultar calificaciones trimestrales, promedios por materia y estado académico oficial.</p>
                    </div>
                </a>
                <a href="index.php?action=datos_personales" class="module-card">
                    <div class="module-icon icon-amber"><i class="fa-solid fa-id-card"></i></div>
                    <div class="module-info">
                        <h3>Datos Personales</h3>
                        <p>Visualizar tu expediente académico y actualizar tu contraseña de acceso personal.</p>
                    </div>
                </a>
            </div>

        <?php else: ?>
            <!-- VISTA DE DOCENTE -->
            <div class="welcome-banner">
                <span class="badge-rol">Docente</span>
                <h1>¡Bienvenido, <?= htmlspecialchars($nombreUsuario) ?>!</h1>
                <p>Módulo docente del Instituto Noé Canjura. Evalúa a tus estudiantes, registra calificaciones por periodo e imprime boletas oficiales.</p>
            </div>

            <div class="section-title">Módulos Disponibles</div>
            <div class="modules-grid">
                <a href="index.php?action=gestion_notas" class="module-card">
                    <div class="module-icon icon-green"><i class="fa-solid fa-list-check"></i></div>
                    <div class="module-info">
                        <h3>Control de Notas</h3>
                        <p>Registrar, promediar (35%, 35%, 30%) las asignaturas evaluadas e imprimir boletas de calificaciones.</p>
                    </div>
                </a>
                <a href="index.php?action=datos_personales" class="module-card">
                    <div class="module-icon icon-amber"><i class="fa-solid fa-id-card"></i></div>
                    <div class="module-info">
                        <h3>Datos Personales</h3>
                        <p>Visualizar credenciales asignadas y modificar contraseña de acceso al sistema.</p>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pie de página integrado institucionalmente -->
    <?php 
    if (file_exists(__DIR__ . '/footer.php')) {
        include __DIR__ . '/footer.php';
    }
    ?>
</body>
</html>