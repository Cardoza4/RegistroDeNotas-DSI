<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}

$esPrimerInicio = !empty($_SESSION['primer_login']) || (isset($_GET['primer_inicio']) && $_GET['primer_inicio'] == '1');

$rolUsuario = strtoupper($_SESSION['rol'] ?? 'DOCENTE');
$isAdmin = ($rolUsuario === 'ADMIN' || $rolUsuario === 'ADMINISTRADOR');
$isDocente = ($rolUsuario === 'DOCENTE');
$isEstudiante = ($rolUsuario === 'ESTUDIANTE');

$data = $usuarioData ?? [];
$nombreCompleto = trim(($data['nombre'] ?? '') . ' ' . ($data['apellido'] ?? '')) ?: ($_SESSION['nombre'] ?? 'Usuario');
$correoUser = $data['correo'] ?? $data['username'] ?? $_SESSION['usuario'] ?? '';
$telefonoUser = $data['telefono'] ?? '';
$direccionUser = $data['direccion'] ?? '';
$fotoUser = trim($data['foto'] ?? $_SESSION['foto'] ?? '');
$msg = $_GET['msg'] ?? '';

// Si guardó con éxito la contraseña y estaba en primer inicio, desactiva la bandera
if (!empty($msg) && $msg === 'exito') {
    unset($_SESSION['primer_login']);
    $esPrimerInicio = false;
}
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

        .alert-primer-inicio {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.45;
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

        .label-symmetric {
            min-height: 38px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            line-height: 1.25;
        }

        .sublabel-text {
            display: block;
            font-size: 0.72rem;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        .input-password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-password-wrapper input {
            padding-right: 2.75rem !important;
        }

        .btn-toggle-eye {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            outline: none;
            transition: color 0.2s ease;
        }

        .btn-toggle-eye:hover {
            color: var(--primary);
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

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand"><span>INCA NOTES</span></a>
        <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem;">Cerrar Sesión</a>
    </nav>
    <div class="container">
        <?php if (!$esPrimerInicio): ?>
            <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Panel Principal</a>
        <?php endif; ?>

        <div class="card">
            <?php if ($esPrimerInicio): ?>
                <!-- ALERTA DE OBLIGACIÓN EN PRIMER INICIO -->
                <div class="alert-primer-inicio">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.3rem; color: #d97706; margin-top: 2px;"></i>
                    <div>
                        <strong style="display:block; font-size: 0.98rem; margin-bottom: 2px;">Primer inicio de sesión detectado</strong>
                        Por directriz de seguridad institucional, debes cambiar tu contraseña provisional por una contraseña personal y segura para activar tu cuenta y poder ingresar al sistema.
                    </div>
                </div>
            <?php endif; ?>

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
                    <?php if (!$esPrimerInicio): ?>
                    <form action="index.php?action=actualizar_foto" method="POST" enctype="multipart/form-data" id="formFoto">
                        <label for="inputFoto" class="upload-btn-badge" title="Cambiar fotografía">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="inputFoto" name="foto" accept="image/png, image/jpeg" style="display:none;" onchange="document.getElementById('formFoto').submit();">
                    </form>
                    <?php endif; ?>
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
                    <span>¡Contraseña actualizada con éxito! Ya puedes ingresar normalmente al sistema.</span>
                </div>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <!-- FORMULARIO ADMINISTRADOR -->
                <form action="index.php?action=actualizar_datos_personales" method="POST" id="formAdminPassword">
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
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nueva_password_admin" class="label-symmetric">
                                <span>Nueva Contraseña</span>
                                <span class="sublabel-text"><?= $esPrimerInicio ? '(Obligatoria para continuar)' : '(Dejar en blanco para mantener la actual)' ?></span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" id="nueva_password_admin" name="nueva_password" placeholder="••••••••" <?= $esPrimerInicio ? 'required' : '' ?>>
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('nueva_password_admin', this)" title="Mostrar u ocultar contraseña">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_password_admin" class="label-symmetric">
                                <span>Confirmar Nueva Contraseña</span>
                                <span class="sublabel-text">(Repetir exactamente igual)</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" id="confirmar_password_admin" name="confirmar_password" placeholder="••••••••" <?= $esPrimerInicio ? 'required' : '' ?>>
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('confirmar_password_admin', this)" title="Mostrar u ocultar contraseña">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 10px;">
                        <?php if (!$esPrimerInicio): ?>
                            <a href="index.php?action=dashboard" style="background:#f1f5f9; color:#334155; padding: 0.8rem 1.5rem; border-radius: 8px; text-decoration:none; font-weight:700; font-size:0.92rem; display:inline-flex; align-items:center;">Cancelar</a>
                        <?php endif; ?>
                        <button type="submit" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> <?= $esPrimerInicio ? 'Establecer Contraseña y Continuar' : 'Guardar Cambios' ?></button>
                    </div>
                </form>
            <?php else: ?>
                <!-- FORMULARIO DOCENTE Y ESTUDIANTE -->
                <form action="index.php?action=actualizar_password" method="POST" id="formUserPassword">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Correo Electrónico (Solo Lectura)</label>
                            <input type="text" value="<?= htmlspecialchars($correoUser) ?>" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                        <div class="form-group">
                            <label>Rol Asignado</label>
                            <input type="text" value="<?= $isDocente ? 'Personal Docente' : 'Estudiante Institucional' ?>" readonly style="background:#e2e8f0; color:#64748b; cursor:not-allowed;">
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fa-solid fa-lock"></i> Seguridad: Modificar Contraseña de Acceso <?= $isDocente ? 'Docente' : 'Estudiantil' ?>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nueva_password_user" class="label-symmetric">
                                <span>Nueva Contraseña</span>
                                <span class="sublabel-text">(Ingresar nueva clave de acceso)</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" id="nueva_password_user" name="nueva_password" placeholder="••••••••" required>
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('nueva_password_user', this)" title="Mostrar u ocultar contraseña">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_password_user" class="label-symmetric">
                                <span>Confirmar Nueva Contraseña</span>
                                <span class="sublabel-text">(Repetir exactamente igual)</span>
                            </label>
                            <div class="input-password-wrapper">
                                <input type="password" id="confirmar_password_user" name="confirmar_password" placeholder="••••••••" required>
                                <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('confirmar_password_user', this)" title="Mostrar u ocultar contraseña">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end; gap: 10px;">
                        <?php if (!$esPrimerInicio): ?>
                            <a href="index.php?action=dashboard" style="background:#f1f5f9; color:#334155; padding: 0.8rem 1.5rem; border-radius: 8px; text-decoration:none; font-weight:700; font-size:0.92rem; display:inline-flex; align-items:center;">Cancelar</a>
                        <?php endif; ?>
                        <button type="submit" class="btn-submit"><i class="fa-solid fa-key"></i> <?= $esPrimerInicio ? 'Establecer Contraseña y Continuar' : 'Actualizar Contraseña' ?></button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- SCRIPT DE VALIDACIÓN CON GLOBOS FLOTANTES HTML5 -->
    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }

        const formAdmin = document.getElementById('formAdminPassword');
        if (formAdmin) {
            const passAdmin = document.getElementById('nueva_password_admin');
            const confAdmin = document.getElementById('confirmar_password_admin');

            passAdmin.addEventListener('input', function() {
                this.setCustomValidity('');
                if (confAdmin) confAdmin.setCustomValidity('');
            });

            confAdmin.addEventListener('input', function() {
                this.setCustomValidity('');
            });

            formAdmin.addEventListener('submit', function(e) {
                passAdmin.setCustomValidity('');
                confAdmin.setCustomValidity('');

                const passVal = passAdmin.value.trim();
                const confVal = confAdmin.value.trim();

                if (passVal !== '' && confVal === '') {
                    e.preventDefault();
                    confAdmin.setCustomValidity('Rellene este campo.');
                    confAdmin.reportValidity();
                    return;
                }

                if (passVal === '' && confVal !== '') {
                    e.preventDefault();
                    passAdmin.setCustomValidity('Rellene este campo.');
                    passAdmin.reportValidity();
                    return;
                }

                if (passVal !== '' && confVal !== '') {
                    if (passAdmin.value !== confAdmin.value) {
                        e.preventDefault();
                        confAdmin.setCustomValidity('Las contraseñas no coinciden.');
                        confAdmin.reportValidity();
                        return;
                    }
                }
            });
        }

        const formUser = document.getElementById('formUserPassword');
        if (formUser) {
            const passUser = document.getElementById('nueva_password_user');
            const confUser = document.getElementById('confirmar_password_user');

            passUser.addEventListener('input', function() {
                this.setCustomValidity('');
                if (confUser) confUser.setCustomValidity('');
            });

            confUser.addEventListener('input', function() {
                this.setCustomValidity('');
            });

            formUser.addEventListener('submit', function(e) {
                confUser.setCustomValidity('');

                if (passUser.value !== confUser.value) {
                    e.preventDefault();
                    confUser.setCustomValidity('Las contraseñas no coinciden.');
                    confUser.reportValidity();
                    return;
                }
            });
        }
    </script>
</body>
</html>