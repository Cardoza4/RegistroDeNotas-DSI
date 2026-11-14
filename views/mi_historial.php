<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$historial = $historialKardex ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/logo_inca.png">
    <title>INCA NOTES - Mi Historial Académico</title>
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

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        
        .navbar { background: #090e17; color: white; padding: 0.85rem 3rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .brand { display: flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 1.15rem; }
        .brand img { width: 32px; height: 32px; object-fit: contain; border-radius: 50%; background: white; padding: 2px; }
        .btn-back-nav { display: inline-flex; align-items: center; gap: 8px; padding: 0.45rem 1.1rem; font-size: 0.85rem; font-weight: 600; color: #ffffff; background: transparent; border: 1px solid #334155; border-radius: 6px; text-decoration: none; transition: background 0.2s; }
        .btn-back-nav:hover { background: #1e293b; }

        .container { flex: 1; max-width: 1100px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; }
        
        .card-history { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 2.5rem; }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid var(--border); padding-bottom: 1.5rem; }
        .title-area h2 { font-size: 1.6rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; margin-bottom: 0.4rem; }
        .title-area h2 i { color: var(--primary); }
        .title-area p { font-size: 0.95rem; color: var(--text-muted); }

        .table-responsive { overflow-x: auto; border-radius: 8px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; text-align: left; background: #fff; }
        th { background: #f8fafc; border-bottom: 2px solid var(--border); padding: 14px; font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 14px; border-bottom: 1px solid var(--border); font-size: 0.95rem; color: var(--text-main); }
        tr:last-child td { border-bottom: none; }
        
        .empty-state { background: #f8fafc; border: 2px dashed #cbd5e1; padding: 3rem 2rem; border-radius: 12px; text-align: center; }
        .empty-state i { font-size: 3rem; color: #94a3b8; margin-bottom: 1rem; }
        .empty-state h3 { font-size: 1.25rem; color: #334155; margin-bottom: 0.5rem; }
        .empty-state p { color: #64748b; font-size: 0.95rem; max-width: 500px; margin: 0 auto; line-height: 1.5; }

        .badge-condicion { padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .badge-aprobado { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-reprobado { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .footer { text-align: center; padding: 2rem 1.5rem; color: var(--text-muted); font-size: 0.82rem; border-top: 1px solid var(--border); margin-top: auto; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <img src="img/logo_inca.png" alt="Logo INCA" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            <span>INCA NOTES</span>
        </a>
        <a href="index.php?action=dashboard" class="btn-back-nav">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </nav>

    <div class="container">
        <div class="card-history">
            <div class="header-top">
                <div class="title-area">
                    <h2><i class="fa-solid fa-book-bookmark"></i> Mi Historial Académico (Kárdex)</h2>
                    <p>Aquí puedes consultar tus promedios finales consolidados de años lectivos anteriores. Este es tu récord inmutable.</p>
                </div>
            </div>

            <?php if(!empty($historial)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Año Lectivo</th>
                                <th>Grado</th>
                                <th>Asignatura</th>
                                <th style="text-align: center;">Promedio Final</th>
                                <th style="text-align: center;">Condición</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $currentYear = null;
                            foreach($historial as $k): 
                                // Opcional: Para separar visualmente por año si tienes más de uno
                                if ($currentYear !== null && $currentYear !== $k['anio_lectivo']) {
                                    echo '<tr style="background:#f1f5f9;"><td colspan="5" style="padding:4px;"></td></tr>';
                                }
                                $currentYear = $k['anio_lectivo'];
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($k['anio_lectivo']) ?></strong></td>
                                <td><?= htmlspecialchars($k['grado']) ?> - <?= htmlspecialchars($k['seccion']) ?></td>
                                <td><?= htmlspecialchars($k['materia']) ?></td>
                                <td style="text-align: center; font-weight: 800; color: var(--primary);"><?= number_format($k['promedio_final'], 2) ?></td>
                                <td style="text-align: center;">
                                    <?php if($k['condicion'] === 'APROBADO'): ?>
                                        <span class="badge-condicion badge-aprobado">APROBADO</span>
                                    <?php else: ?>
                                        <span class="badge-condicion badge-reprobado">REPROBADO</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-folder-open"></i>
                    <h3>Aún no tienes historial consolidado</h3>
                    <p>Tus calificaciones actuales se transferirán automáticamente a este archivo histórico cuando la administración finalice oficialmente el año lectivo en curso.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php 
    if (file_exists(__DIR__ . '/footer.php')) {
        include __DIR__ . '/footer.php';
    } else {
        echo '<footer class="footer"><p>&copy; ' . date('Y') . ' Instituto Nacional Noé Canjura (INCA). Sistema de Control Académico y Gestión de Notas.</p></footer>';
    }
    ?>
</body>
</html>