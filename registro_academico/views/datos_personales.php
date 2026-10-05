<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}
$rolUsuario = strtoupper($_SESSION['rol'] ?? 'DOCENTE');
$isAdmin = ($rolUsuario === 'ADMIN' || $rolUsuario === 'ADMINISTRADOR');
$data = $usuarioData ?? [];
$nombreCompleto = trim(($data['nombre'] ?? '') . ' ' . ($data['apellido'] ?? '')) ?: ($_SESSION['nombre'] ?? 'Usuario');
$correoUser = $data['correo'] ?? $data['username'] ?? $_SESSION['usuario'] ?? '';
$telefonoUser = $data['telefono'] ?? '';
$direccionUser = $data['direccion'] ?? '';
$fotoUser = trim($data['foto'] ?? $_SESSION['foto'] ?? '');
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Datos Personales</title>
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
            --radius: 14px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
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
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
        }

        .avatar-container {
            position: relative;
            width: 80px;
            height: 80px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border: 2px solid #bfdbfe;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-btn-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary);
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .profile-info h2 {
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 0.2rem;
        }

        .profile-info p {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        .badge {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.7rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-top: 0.4rem;
        }

        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
        }

        .form-group.full {
            grid-column: span 2;
        }

        label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        input[type="text"], input[type="password"], textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.92rem;
            background: #f8fafc;
            color: var(--text-main);
        }

        input:focus, textarea:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 1.75rem 0 1rem 0;
            display: flex;
            align-items: center;
            gap: 8px;
            border-top: 1px solid var(--border);
            padding-top: 1.25rem;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
        }

        .btn-back {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand"><span>INCA NOTES</span></a>
        <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem;">Cerrar Sesión</a>
    </nav>
    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Panel Principal</a>
        <div class="card">
            <div class="profile-header">
                <div class="avatar-container">
                    <div class="avatar">
                        <?php 
                        $nombreFoto = basename($fotoUser);
                        $rutaFisica = __DIR__ . '/../uploads/' . $nombreFoto;
                        if (!empty($nombreFoto) && file_exists($rutaFisica)): 
                        ?>
                            <img src="uploads/<?= htmlspecialchars($nombreFoto) ?>" alt="Foto de Perfil">
                        <?php else: ?>
                            <i class="fa-solid fa-user-shield"></i>
                        <?php endif; ?>
                    </div>
                    <form action="index.php?action=actualizar_foto" method="POST" enctype="multipart/form-data" id="formFoto">
                        <label for="inputFoto" class="upload-btn-badge" title="Cambiar fotografía">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="inputFoto" name="foto" accept="image/png, image/jpeg" style="display:none;" onchange="document.getElementById('formFoto').submit();">
                    </form>
                </div>

                <div class="profile-info">
                    <h2><?= htmlspecialchars($nombreCompleto) ?></h2>
                    <p>Expediente institucional y credenciales de acceso</p>
                    <span class="badge">ROL: <?= htmlspecialchars($rolUsuario) ?></span>
                </div>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>¡Fotografía y cambios actualizados con éxito!</span>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <form action="index.php?action=actualizar_datos_personales" method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Correo Electrónico (Solo Lectura)</label>
                            <input type="text" value="<?= htmlspecialchars($correoUser) ?>" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Estado de Cuenta</label>
                            <input type="text" value="Activo" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono de Contacto</label>
                        <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($telefonoUser) ?>" placeholder="Ej: 7123-4567">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección Residencial</label>
                        <textarea id="direccion" name="direccion" placeholder="Dirección de residencia..."><?= htmlspecialchars($direccionUser) ?></textarea>
                    </div>

                    <div class="section-title"><i class="fa-solid fa-lock"></i> Seguridad: Modificar Contraseña de Administrador</div>
                    <div class="form-group full">
                        <label for="nueva_password">Nueva Contraseña (Dejar en blanco para mantener la actual)</label>
                        <input type="password" id="nueva_password" name="nueva_password" placeholder="••••••••">
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 10px;">
                        <a href="index.php?action=dashboard" style="background:#f1f5f9; color:#334155; padding: 0.8rem 1.5rem; border-radius: 8px; text-decoration:none; font-weight:700; font-size:0.92rem; display:inline-flex; align-items:center;">Cancelar</a>
                        <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                    </div>
                </form>
            <?php else: ?>
                <form action="index.php?action=actualizar_password" method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <input type="text" value="<?= htmlspecialchars($correoUser) ?>" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Cargo Asignado</label>
                            <input type="text" value="Personal Docente" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                    </div>

                    <div class="section-title"><i class="fa-solid fa-lock"></i> Seguridad: Modificar Contraseña de Acceso Docente</div>
                    <div class="form-group full">
                        <label for="nueva_password">Nueva Contraseña</label>
                        <input type="password" id="nueva_password" name="nueva_password" placeholder="••••••••" required>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-submit"><i class="fa-solid fa-key"></i> Actualizar Contraseña</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>