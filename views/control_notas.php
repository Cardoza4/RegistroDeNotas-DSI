<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
    header('Location: index.php?action=dashboard');
    exit;
}

$est = $estudianteActual ?? [];
$nombreCompleto = trim(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? '')) ?: 'Estudiante sin registrar';
$nieEst = $est['nie'] ?? '---';
$gradoEst = $est['grado'] ?? '9° Grado';
$seccionEst = $est['seccion'] ?? 'Sección A';
$idEst = $est['id'] ?? 0;
$totalP = $totalPeriodos ?? 3;

$mapNotas = [];
if (!empty($calificacionesAlumno)) {
    foreach ($calificacionesAlumno as $c) {
        $mapNotas[$c['materia']][$c['periodo']] = $c;
    }
}

$materias = [
    'Matemática',
    'Lenguaje y Literatura',
    'Estudios Sociales y Cívica',
    'Ciencia y Tecnología',
    'Idioma Extranjero (Inglés)',
    'Educación Física'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Calificaciones - <?= htmlspecialchars($nombreCompleto) ?></title>
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
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); padding: 2rem 1rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .student-info h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem; }
        .student-info p { color: var(--text-muted); font-size: 0.95rem; font-weight: 600; }
        .badge-nivel { background: #e0f2fe; color: #0369a1; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; display: inline-block; margin-top: 6px; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--border); color: #334155; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; text-decoration: none; }
        .btn-back:hover { background: #f1f5f9; }
        .materia-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.5rem; box-shadow: var(--shadow); margin-bottom: 1.5rem; }
        .materia-title { font-size: 1.1rem; font-weight: 800; color: #1e293b; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; }
        .periodos-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        .periodo-box { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; }
        .periodo-box h5 { font-size: 0.9rem; font-weight: 700; color: var(--primary); margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 4px; }
        .form-group { margin-bottom: 0.6rem; }
        .form-group label { display: block; font-size: 0.78rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.2rem; }
        .form-group input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem; font-weight: 600; text-align: center; background: #fff; }
        .form-group input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .btn-guardar-nota { margin-top: 0.75rem; width: 100%; background: #059669; color: white; border: none; padding: 0.55rem; border-radius: 6px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-guardar-nota:hover { background: #047857; }
        .nota-calculada { margin-top: 0.5rem; text-align: center; font-size: 0.82rem; font-weight: 700; color: #0284c7; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-card">
            <div class="student-info">
                <h2><?= htmlspecialchars($nombreCompleto) ?></h2>
                <p>NIE: <strong><?= htmlspecialchars($nieEst) ?></strong></p>
                <div class="badge-nivel">
                    <i class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($gradoEst) ?> — <?= htmlspecialchars($seccionEst) ?> (<?= $totalP ?> Periodos oficiales)
                </div>
            </div>
            <a href="index.php?action=gestion_notas" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
        </div>

        <?php foreach ($materias as $mat): ?>
            <div class="materia-card">
                <div class="materia-title">
                    <i class="fa-solid fa-book-open" style="color: var(--primary);"></i> <?= htmlspecialchars($mat) ?>
                </div>

                <div class="periodos-grid">
                    <?php for ($p = 1; $p <= $totalP; $p++): 
                        $regNota = $mapNotas[$mat][$p] ?? null;
                        $act1Val = $regNota['act1'] ?? '';
                        $act2Val = $regNota['act2'] ?? '';
                        $exVal   = $regNota['examen'] ?? '';
                        $promVal = $regNota['nota_periodo'] ?? null;
                    ?>
                        <div class="periodo-box">
                            <h5>Periodo <?= $p ?></h5>
                            <form action="index.php?action=guardar_nota" method="POST">
                                <input type="hidden" name="estudiante_id" value="<?= $idEst ?>">
                                <input type="hidden" name="materia" value="<?= htmlspecialchars($mat) ?>">
                                <input type="hidden" name="periodo" value="<?= $p ?>">

                                <div class="form-group">
                                    <label>Actividad 1 (35%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="act1" value="<?= htmlspecialchars($act1Val) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Actividad 2 (35%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="act2" value="<?= htmlspecialchars($act2Val) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Examen (30%)</label>
                                    <input type="number" step="0.1" min="0" max="10" name="examen" value="<?= htmlspecialchars($exVal) ?>" required>
                                </div>

                                <button type="submit" class="btn-guardar-nota">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar P<?= $p ?>
                                </button>

                                <?php if ($promVal !== null): ?>
                                    <div class="nota-calculada">
                                        Promedio P<?= $p ?>: <?= number_format($promVal, 1) ?>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>