<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$rol = strtoupper($_SESSION['rol'] ?? '');
$esAdmin = in_array($rol, ['ADMIN', 'ADMINISTRADOR']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Historial de Alumnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --bg-body: #f8fafc; --card-bg: #ffffff; --text-main: #0f172a; --text-muted: #64748b; --border: #e2e8f0; --radius: 12px; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #090e17; color: white; padding: 0.85rem 3rem; display: flex; align-items: center; justify-content: space-between; }
        .brand { display: flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 800; }
        .container { flex: 1; max-width: 1200px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; }
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 1.25rem; font-size: 0.9rem; }
        .filters-bar { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1rem; display: flex; gap: 0.85rem; align-items: center; margin-bottom: 1.25rem; }
        .filters-bar select, .filters-bar input { padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; outline: none; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 0.88rem; vertical-align: middle; }
        th { background: #ffffff; font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.74rem; }
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold; border: 1px solid #bfdbfe; overflow: hidden; }
        .user-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
        .btn-kardex { background: #0ea5e9; color: white; border: none; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
        .btn-kardex:hover { background: #0284c7; }
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
        <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem;">Cerrar Sesión</a>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
        
        <div class="card">
            <div class="header-top">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);"><i class="fa-solid fa-folder-tree" style="color:var(--primary)"></i> <?= $esAdmin ? 'Historial de Estudiantes' : 'Mis Alumnos (Kárdex)' ?></h2>
                    <p style="font-size: 0.88rem; color: var(--text-muted); margin-top:5px;">Consulta el récord multianual consolidado de los estudiantes.</p>
                </div>
                <?php if(!$esAdmin && !empty($gradoFijo)): ?>
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:0.5rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:700;">
                        <i class="fa-solid fa-users-rectangle"></i> Sección Orientada: <?= htmlspecialchars($gradoFijo . ' ' . $seccionFija) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="filters-bar">
                <input type="text" id="input_buscar" placeholder="🔍 Buscar por NIE o Nombre..." style="min-width: 250px;">
                <?php if($esAdmin): ?>
                    <select id="selectGrado">
                        <option value="">-- Filtrar por Grado --</option>
                        <option value="1° Grado">1° Grado</option><option value="2° Grado">2° Grado</option><option value="3° Grado">3° Grado</option>
                        <option value="7° Grado">7° Grado</option><option value="8° Grado">8° Grado</option><option value="9° Grado">9° Grado</option>
                        <option value="1° Año Bachillerato">1° Año Bachillerato</option><option value="2° Año Bachillerato">2° Año Bachillerato</option>
                    </select>
                    <select id="selectSeccion">
                        <option value="">-- Sección --</option>
                        <option value="Sección A">Sección A</option><option value="Sección B">Sección B</option>
                    </select>
                <?php endif; ?>
            </div>

            <div style="overflow-x: auto;">
                <table id="tablaAlumnos">
                    <thead>
                        <tr>
                            <th>NIE</th>
                            <th>Estudiante</th>
                            <th>Grado / Sección Actual</th>
                            <th>Estado Actual</th>
                            <th style="text-align: right;">Consulta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($estudiantesLista)): ?>
                            <?php foreach ($estudiantesLista as $est): 
                                $nombreFormateado = trim(($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? ''));
                            ?>
                                <tr class="fila-est">
                                    <td><strong><?= htmlspecialchars($est['nie'] ?? 'N/D') ?></strong></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-circle">
                                                <?php $fNom = basename($est['foto'] ?? '');
                                                if (!empty($fNom) && file_exists(__DIR__ . '/../uploads/' . $fNom)): ?>
                                                    <img src="uploads/<?= htmlspecialchars($fNom) ?>">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="span-nom"><?= htmlspecialchars($nombreFormateado) ?></span>
                                        </div>
                                    </td>
                                    <td class="span-grado"><?= htmlspecialchars(($est['grado'] ?? '') . ' - ' . ($est['seccion'] ?? '')) ?></td>
                                    <td>
                                        <?php if(($est['estado_academico'] ?? '') === 'EGRESADO/GRADUADO'): ?>
                                            <span style="background:#fefce8; color:#b45309; border:1px solid #fde68a; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:800;">GRADUADO</span>
                                        <?php else: ?>
                                            <span style="background:#f1f5f9; color:#475569; padding:4px 8px; border-radius:6px; font-size:0.75rem; font-weight:800;">ACTIVO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="index.php?action=ver_kardex&id=<?= $est['id'] ?>" class="btn-kardex">
                                            <i class="fa-solid fa-book-bookmark"></i> Ver Kárdex
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay estudiantes asignados/registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function normalizarTexto(txt) {
            return (txt || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        }
        
        function filtrarTabla() {
            const txt = normalizarTexto(document.getElementById('input_buscar').value);
            const selG = document.getElementById('selectGrado') ? normalizarTexto(document.getElementById('selectGrado').value) : '';
            const selS = document.getElementById('selectSeccion') ? normalizarTexto(document.getElementById('selectSeccion').value) : '';

            document.querySelectorAll('.fila-est').forEach(fila => {
                const nombre = normalizarTexto(fila.querySelector('.span-nom').textContent);
                const nie = normalizarTexto(fila.cells[0].textContent);
                const gradoSec = normalizarTexto(fila.querySelector('.span-grado').textContent);

                let visible = true;
                if (txt !== '' && !nombre.includes(txt) && !nie.includes(txt)) visible = false;
                if (selG !== '' && !gradoSec.includes(selG)) visible = false;
                if (selS !== '' && !gradoSec.includes(selS.replace('seccion ', ''))) visible = false;

                fila.style.display = visible ? '' : 'none';
            });
        }
        document.getElementById('input_buscar').addEventListener('input', filtrarTabla);
        if(document.getElementById('selectGrado')) document.getElementById('selectGrado').addEventListener('change', filtrarTabla);
        if(document.getElementById('selectSeccion')) document.getElementById('selectSeccion').addEventListener('change', filtrarTabla);
    </script>
</body>
</html>