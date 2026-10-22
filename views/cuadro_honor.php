<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. RESTRICCIÓN DE SEGURIDAD: Solo Administrador y Docente
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$rolUsuario = strtoupper($_SESSION['rol'] ?? 'DOCENTE');
if ($rolUsuario === 'ESTUDIANTE') {
    header('Location: index.php?action=dashboard');
    exit;
}

$nombreUsuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario';
$fotoPerfil = trim($_SESSION['foto'] ?? '');
$esAdmin = in_array($rolUsuario, ['ADMIN', 'ADMINISTRADOR']);

// Variables pasadas por el controlador (con valores seguros por defecto)
$grados = $grados ?? ['1° Año Bachillerato', '2° Año Bachillerato', '7° Grado', '8° Grado', '9° Grado'];
$secciones = $secciones ?? ['A', 'B', 'C'];
$gradoSeleccionado = $gradoSeleccionado ?? ($grados[0] ?? '1° Año Bachillerato');
$seccionSeleccionada = $seccionSeleccionada ?? ($secciones[0] ?? 'A');
$periodoSeleccionado = $periodoSeleccionado ?? 'anual'; // 'anual', '1', '2', '3', '4'
$esDocenteRestringido = !$esAdmin;
$rankingData = $rankingData ?? []; // Array con los datos de alumnos calculados por el controlador
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logo_inca.png">
    <title>INCA NOTES - Cuadro de Honor</title>
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
            --radius: 12px;
            --gold: #f59e0b;
            --silver: #94a3b8;
            --bronze: #d97706;
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
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
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

        /* CONTAINER */
        .container {
            flex: 1;
            max-width: 1250px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* CABECERA Y BOTÓN REGRESAR */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: var(--text-main);
            border: 1px solid var(--border);
            padding: 0.55rem 1.1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: white;
            border: none;
            padding: 0.55rem 1.2rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-print:hover {
            background: #1e293b;
        }

        /* HERO DEL CUADRO DE HONOR */
        .honor-hero {
            background: linear-gradient(135deg, #090e17 0%, #1e293b 100%);
            color: white;
            border-radius: var(--radius);
            padding: 2.25rem 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .honor-hero::after {
            content: "\f091";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 20px;
            bottom: -25px;
            font-size: 11rem;
            color: rgba(245, 158, 11, 0.08);
            pointer-events: none;
        }

        .honor-hero-text h1 {
            font-size: 1.85rem;
            font-weight: 900;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .honor-hero-text h1 i {
            color: #fbbf24;
        }

        .honor-hero-text p {
            color: #94a3b8;
            font-size: 0.95rem;
            max-width: 700px;
            line-height: 1.5;
        }

        .honor-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.4);
            color: #fde68a;
            padding: 0.45rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* BARRA DE FILTROS */
        .filter-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .filter-form {
            display: flex;
            align-items: flex-end;
            gap: 1.25rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group select {
            width: 100%;
            padding: 0.6rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.9rem;
            color: var(--text-main);
            background-color: #fff;
            outline: none;
            transition: border-color 0.2s;
        }

        .filter-group select:focus {
            border-color: var(--primary);
        }

        .filter-group select:disabled {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        .filter-badge-docente {
            font-size: 0.75rem;
            color: #2563eb;
            font-weight: 600;
            margin-top: 2px;
        }

        .btn-filter-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            height: 40px;
        }

        .btn-filter-submit:hover {
            background: var(--primary-dark);
        }

        /* PODIO OLÍMPICO (TOP 3) */
        .podium-section-title {
            font-size: 0.85rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .podium-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            align-items: end;
        }

        @media (max-width: 900px) {
            .podium-container {
                grid-template-columns: 1fr;
            }
        }

        .podium-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem 1.5rem;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .podium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
        }

        /* Estilos por lugar */
        .podium-card.first {
            border-color: #fcd34d;
            background: linear-gradient(180deg, #ffffff 0%, #fffdf5 100%);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.12);
            order: 2; /* Centro */
            padding-top: 2.25rem;
            padding-bottom: 2.25rem;
        }

        .podium-card.second {
            border-color: #cbd5e1;
            order: 1; /* Izquierda */
        }

        .podium-card.third {
            border-color: #fdba74;
            order: 3; /* Derecha */
        }

        @media (max-width: 900px) {
            .podium-card.first { order: 1; }
            .podium-card.second { order: 2; }
            .podium-card.third { order: 3; }
        }

        .medal-badge {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .medal-gold {
            background: linear-gradient(135deg, #fef08a 0%, #f59e0b 100%);
            color: #78350f;
            border: 2px solid #fde047;
            width: 66px;
            height: 66px;
            font-size: 1.9rem;
        }

        .medal-silver {
            background: linear-gradient(135deg, #f1f5f9 0%, #94a3b8 100%);
            color: #1e293b;
            border: 2px solid #cbd5e1;
        }

        .medal-bronze {
            background: linear-gradient(135deg, #ffedd5 0%, #ea580c 100%);
            color: #7c2d12;
            border: 2px solid #fdba74;
        }

        .podium-position {
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 0.85rem;
        }

        .pos-gold { background: #fef3c7; color: #b45309; }
        .pos-silver { background: #f1f5f9; color: #475569; }
        .pos-bronze { background: #ffedd5; color: #c2410c; }

        .podium-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            overflow: hidden;
            border: 2px solid var(--border);
        }

        .podium-card.first .podium-avatar {
            width: 76px;
            height: 76px;
            border-color: #fde047;
        }

        .podium-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .podium-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.25rem;
        }

        .podium-nie {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .podium-score-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.6rem 1.25rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .podium-card.first .podium-score-box {
            background: #fffbeb;
            border-color: #fef08a;
        }

        .score-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .score-value {
            font-size: 1.45rem;
            font-weight: 900;
            color: var(--primary);
        }

        .podium-card.first .score-value {
            color: #b45309;
            font-size: 1.7rem;
        }

        .score-materias {
            font-size: 0.72rem;
            color: var(--text-muted);
        }

        /* TABLA TOP 5 RESTANTE */
        .ranking-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 2rem;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .ranking-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .ranking-table td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-main);
        }

        .ranking-table tr:last-child td {
            border-bottom: none;
        }

        .rank-pos {
            font-weight: 800;
            font-size: 1rem;
            color: var(--text-muted);
            width: 50px;
        }

        .rank-student-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rank-mini-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #64748b;
            font-weight: bold;
            overflow: hidden;
        }

        .rank-mini-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rank-score-pill {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 800;
            padding: 0.3rem 0.75rem;
            border-radius: 6px;
            font-size: 0.92rem;
        }

        /* ALERTA: SIN ESTUDIANTES CON NOTA >= 8.0 */
        .empty-ranking {
            background: white;
            border: 1px dashed #cbd5e1;
            border-radius: var(--radius);
            padding: 3rem 2rem;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-ranking i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-ranking h3 {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .empty-ranking p {
            font-size: 0.9rem;
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* PRINT STYLES */
        @media print {
            .navbar, .header-actions, .filter-card, .footer {
                display: none !important;
            }
            body {
                background: white;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            .honor-hero {
                background: #090e17 !important;
                color: white !important;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .podium-card {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <!-- NAVEGACIÓN SUPERIOR -->
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <div style="width:34px; height:34px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <img src="img/logo_inca.png" alt="Logo" style="width:28px; height:28px; object-fit:contain;" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            </div>
            <span>INCA NOTES</span>
        </a>
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="color:#94a3b8; font-size:0.88rem;">
                <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($nombreUsuario) ?> (<?= htmlspecialchars($rolUsuario) ?>)
            </span>
            <a href="index.php?action=logout" class="btn-logout">Cerrar Sesión</a>
        </div>
    </nav>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="container">
        <!-- ACCIONES SUPERIORES -->
        <div class="header-actions">
            <a href="index.php?action=dashboard" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al Dashboard
            </a>
            <button onclick="window.print()" class="btn-print">
                <i class="fa-solid fa-print"></i> Imprimir Cuadro de Honor
            </button>
        </div>

        <!-- BANNER DE HONOR -->
        <div class="honor-hero">
            <div class="honor-hero-text">
                <div class="honor-hero-badge">
                    <i class="fa-solid fa-award"></i> Excelencia Académica Institucional
                </div>
                <h1 style="margin-top: 0.6rem;">
                    <i class="fa-solid fa-trophy"></i> Cuadro de Honor y Ranking de Rendimiento
                </h1>
                <p>
                    Reconocimiento oficial a los mejores promedios globales de <strong><?= htmlspecialchars($gradoSeleccionado) ?> - Sección "<?= htmlspecialchars($seccionSeleccionada) ?>"</strong>.
                    Evaluación integral que promedia todas las asignaturas oficiales (Umbral de mérito: Promedio ≥ 8.0).
                </p>
            </div>
        </div>

        <!-- BARRA DE FILTROS Y CONTROLES -->
        <div class="filter-card">
            <form action="index.php" method="GET" class="filter-form">
                <input type="hidden" name="action" value="cuadro_honor">

                <!-- 1. GRADO -->
                <div class="filter-group">
                    <label for="filtro_grado"><i class="fa-solid fa-graduation-cap"></i> Grado</label>
                    <?php if ($esDocenteRestringido): ?>
                        <select id="filtro_grado" disabled>
                            <option value="<?= htmlspecialchars($gradoSeleccionado) ?>"><?= htmlspecialchars($gradoSeleccionado) ?></option>
                        </select>
                        <input type="hidden" name="grado" value="<?= htmlspecialchars($gradoSeleccionado) ?>">
                        <span class="filter-badge-docente"><i class="fa-solid fa-lock"></i> Grado asignado</span>
                    <?php else: ?>
                        <select name="grado" id="filtro_grado" onchange="this.form.submit()">
                            <?php foreach ($grados as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>" <?= ($gradoSeleccionado === $g) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- 2. SECCIÓN -->
                <div class="filter-group">
                    <label for="filtro_seccion"><i class="fa-solid fa-users"></i> Sección</label>
                    <?php if ($esDocenteRestringido): ?>
                        <select id="filtro_seccion" disabled>
                            <option value="<?= htmlspecialchars($seccionSeleccionada) ?>">Sección <?= htmlspecialchars($seccionSeleccionada) ?></option>
                        </select>
                        <input type="hidden" name="seccion" value="<?= htmlspecialchars($seccionSeleccionada) ?>">
                        <span class="filter-badge-docente"><i class="fa-solid fa-lock"></i> Sección asignada</span>
                    <?php else: ?>
                        <select name="seccion" id="filtro_seccion" onchange="this.form.submit()">
                            <?php foreach ($secciones as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= ($seccionSeleccionada === $s) ? 'selected' : '' ?>>
                                    Sección <?= htmlspecialchars($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- 3. PERIODO / ACUMULADO (CRITERIO 3) -->
                <div class="filter-group">
                    <label for="filtro_periodo"><i class="fa-solid fa-calendar-check"></i> Modalidad de Cálculo</label>
                    <select name="periodo" id="filtro_periodo" onchange="this.form.submit()">
                        <option value="anual" <?= ($periodoSeleccionado === 'anual') ? 'selected' : '' ?>>Promedio Acumulado Anual (Global)</option>
                        <option value="1" <?= ($periodoSeleccionado === '1') ? 'selected' : '' ?>>Periodo 1</option>
                        <option value="2" <?= ($periodoSeleccionado === '2') ? 'selected' : '' ?>>Periodo 2</option>
                        <option value="3" <?= ($periodoSeleccionado === '3') ? 'selected' : '' ?>>Periodo 3</option>
                        <option value="4" <?= ($periodoSeleccionado === '4') ? 'selected' : '' ?>>Periodo 4</option>
                    </select>
                </div>

                <?php if ($esAdmin): ?>
                    <button type="submit" class="btn-filter-submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Consultar
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <?php
        // Filtrar y validar estrictamente promedios >= 8.0 ordenados descendentemente
        $alumnosHonor = array_filter($rankingData, function($alumno) {
            return isset($alumno['promedio']) && floatval($alumno['promedio']) >= 8.0;
        });

        // Reindexar array
        $alumnosHonor = array_values($alumnosHonor);
        $totalHonor = count($alumnosHonor);
        ?>

        <?php if ($totalHonor === 0): ?>
            <!-- AVISO INSTITUCIONAL: NINGÚN ALUMNO ALCANZA EL UMBRAL DE 8.0 -->
            <div class="empty-ranking">
                <i class="fa-solid fa-graduation-cap"></i>
                <h3>No hay estudiantes en el Cuadro de Honor</h3>
                <p>
                    Para la sección seleccionada en esta modalidad de cálculo, actualmente ningún estudiante ha alcanzado el umbral de excelencia académica institucional (Promedio global superior o igual a 8.0).
                </p>
            </div>
        <?php else: ?>

            <!-- PODIO OLÍMPICO (TOP 3) -->
            <div class="podium-section-title">
                <i class="fa-solid fa-medal"></i> Podio de Honor - Top 3 de Excelencia Académica
            </div>

            <div class="podium-container">

                <!-- 2° LUGAR (PLATA) -->
                <?php if (isset($alumnosHonor[1])): 
                    $p2 = $alumnosHonor[1];
                ?>
                    <div class="podium-card second">
                        <div class="medal-badge medal-silver"><i class="fa-solid fa-medal"></i></div>
                        <span class="podium-position pos-silver">2° Lugar &bull; Medalla de Plata</span>
                        <div class="podium-avatar">
                            <?php if (!empty($p2['foto']) && file_exists(__DIR__ . '/../uploads/' . basename($p2['foto']))): ?>
                                <img src="uploads/<?= htmlspecialchars(basename($p2['foto'])) ?>" alt="Foto">
                            <?php else: ?>
                                <?= strtoupper(substr($p2['nombres'] ?? 'A', 0, 1) . substr($p2['apellidos'] ?? 'B', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h3 class="podium-name"><?= htmlspecialchars(($p2['nombres'] ?? '') . ' ' . ($p2['apellidos'] ?? '')) ?></h3>
                        <div class="podium-nie">NIE: <?= htmlspecialchars($p2['nie'] ?? 'N/D') ?></div>
                        <div class="podium-score-box">
                            <span class="score-label">Promedio Global</span>
                            <span class="score-value"><?= number_format(floatval($p2['promedio']), 2) ?></span>
                            <span class="score-materias"><?= intval($p2['total_materias'] ?? 0) ?> materias evaluadas</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="podium-card second" style="opacity: 0.5;">
                        <div class="medal-badge medal-silver"><i class="fa-solid fa-medal"></i></div>
                        <span class="podium-position pos-silver">2° Lugar Vacante</span>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">Sin calificado ≥ 8.0</p>
                    </div>
                <?php endif; ?>

                <!-- 1° LUGAR (ORO) -->
                <?php if (isset($alumnosHonor[0])): 
                    $p1 = $alumnosHonor[0];
                ?>
                    <div class="podium-card first">
                        <div class="medal-badge medal-gold"><i class="fa-solid fa-trophy"></i></div>
                        <span class="podium-position pos-gold"><i class="fa-solid fa-crown"></i> 1° Lugar &bull; Medalla de Oro</span>
                        <div class="podium-avatar">
                            <?php if (!empty($p1['foto']) && file_exists(__DIR__ . '/../uploads/' . basename($p1['foto']))): ?>
                                <img src="uploads/<?= htmlspecialchars(basename($p1['foto'])) ?>" alt="Foto">
                            <?php else: ?>
                                <?= strtoupper(substr($p1['nombres'] ?? 'A', 0, 1) . substr($p1['apellidos'] ?? 'B', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h3 class="podium-name" style="font-size: 1.2rem;"><?= htmlspecialchars(($p1['nombres'] ?? '') . ' ' . ($p1['apellidos'] ?? '')) ?></h3>
                        <div class="podium-nie">NIE: <?= htmlspecialchars($p1['nie'] ?? 'N/D') ?></div>
                        <div class="podium-score-box">
                            <span class="score-label">Promedio Global Más Alto</span>
                            <span class="score-value"><?= number_format(floatval($p1['promedio']), 2) ?></span>
                            <span class="score-materias"><?= intval($p1['total_materias'] ?? 0) ?> materias evaluadas</span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3° LUGAR (BRONCE) -->
                <?php if (isset($alumnosHonor[2])): 
                    $p3 = $alumnosHonor[2];
                ?>
                    <div class="podium-card third">
                        <div class="medal-badge medal-bronze"><i class="fa-solid fa-award"></i></div>
                        <span class="podium-position pos-bronze">3° Lugar &bull; Medalla de Bronce</span>
                        <div class="podium-avatar">
                            <?php if (!empty($p3['foto']) && file_exists(__DIR__ . '/../uploads/' . basename($p3['foto']))): ?>
                                <img src="uploads/<?= htmlspecialchars(basename($p3['foto'])) ?>" alt="Foto">
                            <?php else: ?>
                                <?= strtoupper(substr($p3['nombres'] ?? 'A', 0, 1) . substr($p3['apellidos'] ?? 'B', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <h3 class="podium-name"><?= htmlspecialchars(($p3['nombres'] ?? '') . ' ' . ($p3['apellidos'] ?? '')) ?></h3>
                        <div class="podium-nie">NIE: <?= htmlspecialchars($p3['nie'] ?? 'N/D') ?></div>
                        <div class="podium-score-box">
                            <span class="score-label">Promedio Global</span>
                            <span class="score-value"><?= number_format(floatval($p3['promedio']), 2) ?></span>
                            <span class="score-materias"><?= intval($p3['total_materias'] ?? 0) ?> materias evaluadas</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="podium-card third" style="opacity: 0.5;">
                        <div class="medal-badge medal-bronze"><i class="fa-solid fa-award"></i></div>
                        <span class="podium-position pos-bronze">3° Lugar Vacante</span>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 1rem;">Sin calificado ≥ 8.0</p>
                    </div>
                <?php endif; ?>

            </div>

            <!-- RANKING COMPLEMENTARIO (POSICIONES 4 Y 5 - TOP 5) -->
            <?php if ($totalHonor > 3): ?>
                <div class="podium-section-title">
                    <i class="fa-solid fa-list-ol"></i> Menciones Honoríficas (Top 5)
                </div>

                <div class="ranking-card">
                    <table class="ranking-table">
                        <thead>
                            <tr>
                                <th>Posición</th>
                                <th>Estudiante</th>
                                <th>NIE</th>
                                <th>Materias Evaluadas</th>
                                <th>Promedio Global</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 3; $i < min(5, $totalHonor); $i++): 
                                $alumno = $alumnosHonor[$i];
                            ?>
                                <tr>
                                    <td class="rank-pos">#<?= $i + 1 ?></td>
                                    <td>
                                        <div class="rank-student-cell">
                                            <div class="rank-mini-avatar">
                                                <?php if (!empty($alumno['foto']) && file_exists(__DIR__ . '/../uploads/' . basename($alumno['foto']))): ?>
                                                    <img src="uploads/<?= htmlspecialchars(basename($alumno['foto'])) ?>" alt="Foto">
                                                <?php else: ?>
                                                    <?= strtoupper(substr($alumno['nombres'] ?? 'A', 0, 1)) ?>
                                                <?php endif; ?>
                                            </div>
                                            <strong><?= htmlspecialchars(($alumno['nombres'] ?? '') . ' ' . ($alumno['apellidos'] ?? '')) ?></strong>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-muted);"><?= htmlspecialchars($alumno['nie'] ?? 'N/D') ?></td>
                                    <td><?= intval($alumno['total_materias'] ?? 0) ?> asignaturas</td>
                                    <td>
                                        <span class="rank-score-pill"><?= number_format(floatval($alumno['promedio']), 2) ?></span>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- PIE DE PÁGINA INSTITUCIONAL -->
    <?php 
    if (file_exists(__DIR__ . '/footer.php')) {
        include __DIR__ . '/footer.php';
    }
    ?>
</body>
</html>