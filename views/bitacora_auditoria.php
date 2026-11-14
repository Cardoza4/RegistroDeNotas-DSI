<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Control de acceso: solo Administrador
if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
    header('Location: index.php?action=dashboard');
    exit;
}

// Conexión a base de datos
if (!isset($pdo) && !isset($conn) && !isset($db)) {
    $rutasConexion = [
        __DIR__ . '/../config/Conexion.php',
        __DIR__ . '/../config/conexion.php',
        __DIR__ . '/../config/database.php',
        __DIR__ . '/../config/db.php'
    ];
    foreach ($rutasConexion as $ruta) {
        if (file_exists($ruta)) {
            @require_once $ruta;
            break;
        }
    }
}

if (!isset($conexionDB)) {
    if (class_exists('Conexion')) {
        $conObj = new Conexion();
        $conexionDB = $conObj->conectar();
    } else {
        $conexionDB = $pdo ?? $conn ?? $db ?? null;
    }
}

// Asegurar que la tabla bitacora_auditoria exista automáticamente si no fue creada
if ($conexionDB instanceof PDO) {
    try {
        $conexionDB->exec("CREATE TABLE IF NOT EXISTS bitacora_auditoria (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(100) DEFAULT 'Sistema',
            fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            tabla_afectada VARCHAR(50),
            accion VARCHAR(20),
            valor_anterior TEXT,
            valor_nuevo TEXT
        )");
    } catch (Exception $e) {}
}

// Filtros de búsqueda amigables
$filtroUsuario = trim($_GET['usuario'] ?? '');
$filtroFechaInicio = trim($_GET['fecha_inicio'] ?? '');
$filtroFechaFin = trim($_GET['fecha_fin'] ?? '');
$filtroActividad = trim($_GET['actividad'] ?? '');

// Paginación optimizada
$registrosPorPagina = 15;
$paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
$offset = ($paginaActual - 1) * $registrosPorPagina;

$registrosBitacora = [];
$totalRegistros = 0;
$totalPaginas = 1;

if ($conexionDB instanceof PDO) {
    try {
        $whereClauses = ["1=1"];
        $params = [];

        if ($filtroUsuario !== '') {
            $whereClauses[] = "(usuario LIKE ? OR modificado_por LIKE ?)";
            $params[] = "%$filtroUsuario%";
            $params[] = "%$filtroUsuario%";
        }

        if ($filtroFechaInicio !== '') {
            $whereClauses[] = "fecha_modificacion >= ?";
            $params[] = $filtroFechaInicio . " 00:00:00";
        }

        if ($filtroFechaFin !== '') {
            $whereClauses[] = "fecha_modificacion <= ?";
            $params[] = $filtroFechaFin . " 23:59:59";
        }

        // Mapeo dinámico del filtro amigable hacia las tablas y acciones de la BD
        if ($filtroActividad !== '') {
            if ($filtroActividad === 'modificacion_notas') {
                $whereClauses[] = "tabla_afectada = ?";
                $params[] = 'notas_periodos';
            } elseif ($filtroActividad === 'estudiante_agregado') {
                $whereClauses[] = "tabla_afectada = ? AND (accion = ? OR accion = ?)";
                $params[] = 'estudiantes';
                $params[] = 'INSERT';
                $params[] = 'CREACION_USUARIO';
            } elseif ($filtroActividad === 'usuario_agregado') {
                $whereClauses[] = "tabla_afectada = ? AND accion = ?";
                $params[] = 'usuarios';
                $params[] = 'CREACION_USUARIO';
            } elseif ($filtroActividad === 'eliminacion') {
                $whereClauses[] = "accion = ?";
                $params[] = 'ELIMINACION';
            }
        }

        $sqlWhere = implode(" AND ", $whereClauses);

        // Contar total para paginación
        $stmtCount = $conexionDB->prepare("SELECT COUNT(*) FROM bitacora_auditoria WHERE $sqlWhere");
        $stmtCount->execute($params);
        $totalRegistros = (int)$stmtCount->fetchColumn();
        $totalPaginas = max(1, ceil($totalRegistros / $registrosPorPagina));

        // Obtener registros paginados
        $sqlData = "SELECT id, usuario, fecha_modificacion, tabla_afectada, valor_anterior, valor_nuevo, accion 
                    FROM bitacora_auditoria 
                    WHERE $sqlWhere 
                    ORDER BY fecha_modificacion DESC 
                    LIMIT $registrosPorPagina OFFSET $offset";
        
        $stmtData = $conexionDB->prepare($sqlData);
        $stmtData->execute($params);
        $registrosBitacora = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $registrosBitacora = [];
    }
}

// Función auxiliar para traducir los nombres técnicos a lenguaje amigable para el usuario final
function traducirActividad($tabla, $accion) {
    $acc = strtoupper(trim($accion ?? ''));
    if ($acc === 'ELIMINACION') {
        return 'Usuario/Estudiante eliminado';
    }

    switch (strtolower(trim($tabla))) {
        case 'notas_periodos':
            return 'Modificación de notas';
        case 'estudiantes':
            return 'Estudiante agregado';
        case 'usuarios':
            return 'Usuario agregado';
        default:
            return ucfirst($tabla);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Historial de Actividad Institucional</title>
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
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #090e17; color: white; padding: 0.85rem 3rem; display: flex; align-items: center; justify-content: space-between; }
        .brand { display: flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 800; }
        .container { flex: 1; max-width: 1400px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; }
        
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .title-area h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .title-area p { font-size: 0.88rem; color: var(--text-muted); }
        
        .filters-bar { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1rem; display: flex; gap: 0.85rem; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; }
        .filters-bar input, .filters-bar select { padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: white; font-size: 0.88rem; color: var(--text-main); outline: none; }
        .btn-filter { background: var(--primary); color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-filter:hover { background: #1d4ed8; }
        .btn-clear { background: #e2e8f0; color: #334155; border: none; padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 0.85rem; vertical-align: middle; }
        th { background: #f8fafc; font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.5px; }
        
        .badge-tabla { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-accion { padding: 3px 7px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .accion-update { background: #fef3c7; color: #92400e; }
        .accion-delete { background: #fee2e2; color: #991b1b; }
        .accion-insert { background: #dcfce7; color: #166534; }

        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 1rem; }
        .pagination-links { display: flex; gap: 5px; }
        .page-btn { padding: 0.4rem 0.8rem; border: 1px solid var(--border); background: white; border-radius: 6px; text-decoration: none; color: var(--text-main); font-size: 0.85rem; font-weight: 600; }
        .page-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .page-btn:hover:not(.active) { background: #f1f5f9; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 1.25rem; font-size: 0.9rem; }
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
            <span style="color:#94a3b8; font-size:0.88rem;"><i class="fa-solid fa-user-shield"></i> Panel de ADMINISTRADOR</span>
            <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Panel Principal</a>

        <div class="card">
            <div class="header-top">
                <div class="title-area">
                    <h2><i class="fa-solid fa-fingerprint" style="color: var(--primary);"></i> Historial de Actividad Institucional</h2>
                    <p>Registro detallado de las acciones y modificaciones realizadas en el sistema para control y seguridad.</p>
                </div>
            </div>

            <!-- Filtros de búsqueda amigables -->
            <form method="GET" action="index.php" class="filters-bar">
                <input type="hidden" name="action" value="bitacora_auditoria">
                
                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:3px;">Usuario Responsable:</label>
                    <input type="text" name="usuario" value="<?= htmlspecialchars($filtroUsuario) ?>" placeholder="Buscar usuario...">
                </div>

                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:3px;">Actividad Realizada:</label>
                    <select name="actividad">
                        <option value="">-- Todas las Actividades --</option>
                        <option value="modificacion_notas" <?= $filtroActividad === 'modificacion_notas' ? 'selected' : '' ?>>Modificación de notas</option>
                        <option value="estudiante_agregado" <?= $filtroActividad === 'estudiante_agregado' ? 'selected' : '' ?>>Estudiante agregado</option>
                        <option value="usuario_agregado" <?= $filtroActividad === 'usuario_agregado' ? 'selected' : '' ?>>Usuario agregado</option>
                        <option value="eliminacion" <?= $filtroActividad === 'eliminacion' ? 'selected' : '' ?>>Usuario/Estudiante eliminado</option>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:3px;">Desde la Fecha:</label>
                    <input type="date" name="fecha_inicio" value="<?= htmlspecialchars($filtroFechaInicio) ?>">
                </div>

                <div>
                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#475569; margin-bottom:3px;">Hasta la Fecha:</label>
                    <input type="date" name="fecha_fin" value="<?= htmlspecialchars($filtroFechaFin) ?>">
                </div>

                <div style="margin-top: 15px; display:flex; gap:8px;">
                    <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <a href="index.php?action=bitacora_auditoria" class="btn-clear"><i class="fa-solid fa-rotate-right"></i> Limpiar</a>
                </div>
            </form>

            <!-- Tabla de Registros orientada al usuario final -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha / Hora</th>
                            <th>Actividad Realizada</th>
                            <th>Acción</th>
                            <th>Valor Anterior</th>
                            <th>Valor Nuevo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($registrosBitacora)): ?>
                            <?php foreach ($registrosBitacora as $reg): 
                                $acc = strtoupper($reg['accion'] ?? 'UPDATE');
                                $claseAccion = 'accion-update';
                                if (strpos($acc, 'DEL') !== false || $acc === 'ELIMINACION') $claseAccion = 'accion-delete';
                                if (strpos($acc, 'INS') !== false || $acc === 'CREACION_USUARIO') $claseAccion = 'accion-insert';
                                
                                $nombreActividad = traducirActividad($reg['tabla_afectada'], $reg['accion']);
                            ?>
                                <tr>
                                    <td><strong>#<?= $reg['id'] ?></strong></td>
                                    <td><i class="fa-solid fa-user-shield" style="color: var(--text-muted); margin-right:5px;"></i> <?= htmlspecialchars($reg['usuario'] ?? 'Sistema') ?></td>
                                    <td style="white-space: nowrap;"><?= htmlspecialchars($reg['fecha_modificacion']) ?></td>
                                    <td><span class="badge-tabla"><?= htmlspecialchars($nombreActividad) ?></span></td>
                                    <td><span class="badge-accion <?= $claseAccion ?>"><?= $acc ?></span></td>
                                    <td style="font-family: monospace; font-size: 0.8rem; color: #991b1b; background: #fff5f5; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($reg['valor_anterior'] ?? '') ?>">
                                        <?= htmlspecialchars($reg['valor_anterior'] ?? 'N/D') ?>
                                    </td>
                                    <td style="font-family: monospace; font-size: 0.8rem; color: #166534; background: #f0fdf4; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($reg['valor_nuevo'] ?? '') ?>">
                                        <?= htmlspecialchars($reg['valor_nuevo'] ?? 'N/D') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                    <div style="font-size: 28px; margin-bottom: 8px;"><i class="fa-solid fa-folder-open"></i></div>
                                    <strong>No se encontraron registros de actividad</strong><br>
                                    <span style="font-size: 0.85rem;">No hay eventos registrados que coincidan con los filtros de búsqueda aplicados.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación optimizada con persistencia de filtros -->
            <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        Mostrando registros del <strong><?= $offset + 1 ?></strong> al <strong><?= min($offset + $registrosPorPagina, $totalRegistros) ?></strong> de un total de <strong><?= $totalRegistros ?></strong> eventos.
                    </div>
                    <div class="pagination-links">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a href="index.php?action=bitacora_auditoria&pagina=<?= $i ?>&usuario=<?= urlencode($filtroUsuario) ?>&actividad=<?= urlencode($filtroActividad) ?>&fecha_inicio=<?= urlencode($filtroFechaInicio) ?>&fecha_fin=<?= urlencode($filtroFechaFin) ?>" 
                               class="page-btn <?= $i === $paginaActual ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>