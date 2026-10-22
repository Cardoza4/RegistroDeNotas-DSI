<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$est = $estudianteData ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Modificar Estudiante</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
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
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
        }

        .brand-logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .brand-logo-circle img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 2px;
            border-radius: 50%;
        }

        .btn-back-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.45rem 1.1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: #ffffff;
            background: transparent;
            border: 1px solid #334155;
            border-radius: 6px;
            text-decoration: none;
        }

        .btn-back-nav:hover {
            background: #1e293b;
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
        }

        .card-register {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 680px;
            padding: 2.5rem;
        }

        .card-header {
            margin-bottom: 2rem;
        }

        .card-header h2 {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .section-subhead {
            grid-column: span 2;
            font-size: 0.9rem;
            font-weight: 700;
            color: #d97706;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 0.4rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-main);
            background-color: #f8fafc;
            transition: all 0.2s;
        }

        select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748b%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.4-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.65rem auto;
            padding-right: 2.5rem;
            cursor: pointer;
        }

        input:focus,
        select:focus {
            outline: none;
            background-color: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .actions-group {
            display: flex;
            gap: 12px;
            margin-top: 1.75rem;
        }

        .btn-submit {
            flex: 2;
            background: #eab308;
            color: #ffffff;
            border: none;
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #ca8a04;
        }

        .btn-cancel {
            flex: 1;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border);
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <div class="brand-logo-circle">
                <img src="img/logo_inca.png" alt="Logo INCA">
            </div>
            <span>INCA NOTES</span>
        </a>
        <a href="index.php?action=gestion_notas" class="btn-back-nav">
            <i class="fa-solid fa-arrow-left"></i> Volver al Control de Notas
        </a>
    </nav>

    <main class="main-container">
        <div class="card-register">
            <div class="card-header">
                <h2>Editar Expediente de Estudiante</h2>
                <p>Modifica los datos institucionales, de contacto y del encargado.</p>
            </div>

            <form action="index.php?action=actualizar_estudiante" method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($est['id'] ?? '') ?>">

                <div class="form-grid">
                    
                    <div class="section-subhead">
                        <i class="fa-solid fa-id-card"></i> 1. Identificación Institucional
                    </div>

                    <div class="form-group full-width">
                        <label for="nie">NIE (Carné Único)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-badge"></i>
                            <input type="text" id="nie" name="nie" value="<?= htmlspecialchars($est['nie'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombres">Nombres</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="nombres" name="nombres" value="<?= htmlspecialchars($est['nombre'] ?? $est['nombres'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="apellidos" name="apellidos" value="<?= htmlspecialchars($est['apellido'] ?? $est['apellidos'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="grado">Grado Escolar</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <select id="grado" name="grado" required>
                                <?php 
                                $actualGrado = trim($est['grado'] ?? '9° Grado');
                                $gradosDisponibles = [
                                    '1° Grado', '2° Grado', '3° Grado', '4° Grado', '5° Grado',
                                    '6° Grado', '7° Grado', '8° Grado', '9° Grado',
                                    '1° Año Bachillerato', '2° Año Bachillerato'
                                ];
                                foreach ($gradosDisponibles as $g): 
                                ?>
                                    <option value="<?= $g ?>" <?= ($actualGrado === $g) ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="seccion">Sección Asignada</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-users-rectangle"></i>
                            <select id="seccion" name="seccion" required>
                                <?php 
                                $actualSec = trim($est['seccion'] ?? 'Sección A');
                                foreach (['Sección A', 'Sección B'] as $s): 
                                ?>
                                    <option value="<?= $s ?>" <?= ($actualSec === $s) ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="correo">Correo Institucional</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($est['correo'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="section-subhead">
                        <i class="fa-solid fa-user-group"></i> 2. Datos Personales y Tutor
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($est['fecha_nacimiento'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="genero">Género</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-venus-mars"></i>
                            <select id="genero" name="genero">
                                <option value="Masculino" <?= (($est['genero'] ?? '') === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                                <option value="Femenino" <?= (($est['genero'] ?? '') === 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="encargado">Nombre del Encargado / Tutor</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user-shield"></i>
                            <input type="text" id="encargado" name="encargado" value="<?= htmlspecialchars($est['encargado'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono de Contacto</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($est['telefono'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="direccion">Dirección Residencial</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-location-dot"></i>
                            <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($est['direccion'] ?? '') ?>">
                        </div>
                    </div>

                </div>

                <div class="actions-group">
                    <a href="index.php?action=gestion_notas" class="btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>