<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $datosUsuario ?? [];

$nombreCompleto = trim(($user['nombre'] ?? $_SESSION['nombre'] ?? '') . ' ' . ($user['apellido'] ?? $_SESSION['apellido'] ?? ''));
if (empty(trim($nombreCompleto))) {
    $nombreCompleto = $_SESSION['usuario_nombre'] ?? 'Personal INCA';
}

$rol = strtoupper($user['rol'] ?? $_SESSION['rol'] ?? 'DOCENTE');
$esEstudiante = ($rol === 'ESTUDIANTE');
$esDocente = ($rol === 'DOCENTE');
$esAdmin = ($rol === 'ADMIN' || $rol === 'ADMINISTRADOR');

// Identificación
$nie = $user['nie'] ?? $user['username'] ?? $_SESSION['usuario'] ?? 'DOC1001';
$correo = !empty($user['correo']) ? $user['correo'] : (!empty($user['email']) ? $user['email'] : ($nie . '@inca.edu.sv'));
$dui = !empty($user['dui']) ? $user['dui'] : 'No registrado';
$escalafon = !empty($user['escalafon']) ? $user['escalafon'] : 'No registrado';

// Datos de estudiante
$grado = $user['grado'] ?? '9° Grado';
$seccion = $user['seccion'] ?? 'Sección A';
$encargado = $user['encargado'] ?? 'No asignado';
$fechaNacimiento = !empty($user['fecha_nacimiento']) ? $user['fecha_nacimiento'] : 'No registrada';
$genero = $user['genero'] ?? 'Masculino';
$telefono = $user['telefono'] ?? '';
$direccion = $user['direccion'] ?? '';

// Foto de perfil
$fotoPerfil = !empty($user['foto']) ? 'uploads/' . $user['foto'] : 'img/logo_inca.png';

$alertaExito = (isset($_GET['msg']) && $_GET['msg'] === 'exito');
$errorPass = (isset($_GET['error']) && $_GET['error'] === 'password_mismatch');
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
            --text-dark: #0f172a;
            --text-gray: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
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
            letter-spacing: 0.5px;
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            border: 2px solid rgba(255, 255, 255, 0.25);
            flex-shrink: 0;
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
            transition: all 0.2s ease;
        }

        .btn-back-nav:hover { background: #1e293b; }

        .container {
            flex: 1;
            max-width: 960px;
            width: 100%;
            margin: 2.5rem auto;
            padding: 0 1.5rem;
        }

        .card-profile {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
        }

        .avatar-wrapper {
            position: relative;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            flex-shrink: 0;
            background: #ffffff;
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: transform 0.2s, background 0.2s;
        }

        .avatar-upload-btn:hover {
            background: var(--primary-hover);
            transform: scale(1.08);
        }

        .profile-title-meta h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.3rem;
        }

        .profile-title-meta p {
            font-size: 0.95rem;
            color: var(--text-gray);
        }

        .role-tag {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 0.5rem;
            letter-spacing: 0.5px;
        }

        .section-separator {
            grid-column: span 2;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed var(--border);
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .form-group.full { grid-column: span 2; }

        label {
            font-size: 0.88rem;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .readonly-indicator {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-box i {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.6rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-dark);
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            padding-left: 1rem;
        }

        input:focus, textarea:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        input[readonly] {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            color: #64748b;
            cursor: not-allowed;
            box-shadow: none;
        }

        .actions-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn-cancel {
            padding: 0.75rem 1.4rem;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-cancel:hover { background: #e2e8f0; }

        .btn-save {
            padding: 0.75rem 1.6rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-save:hover { background: var(--primary-hover); }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }

        .modal-alert {
            background: #ffffff;
            border-radius: 14px;
            padding: 2rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
        }

        .modal-icon {
            width: 60px;
            height: 60px;
            background: #dcfce7;
            color: #16a34a;
            font-size: 1.8rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .modal-alert h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .modal-alert p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.4;
        }

        .btn-modal-close {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .navbar { padding: 0.85rem 1.25rem; }
            .profile-header { flex-direction: column; text-align: center; }
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full, .section-separator { grid-column: span 1; }
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
        <a href="index.php?action=dashboard" class="btn-back-nav">
            <i class="fa-solid fa-arrow-left"></i> Volver al Inicio
        </a>
    </nav>

    <main class="container">
        <div class="card-profile">
            
            <form action="index.php?action=actualizar_perfil" method="POST" enctype="multipart/form-data" id="perfilForm">
                
                <div class="profile-header">
                    <div class="avatar-wrapper">
                        <img src="<?= htmlspecialchars($fotoPerfil) ?>" id="avatarPreview" class="avatar-img" alt="Foto de Perfil">
                        <label for="fotoInput" class="avatar-upload-btn" title="Cargar nueva fotografía (.jpg, .png max 2MB)">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                        <input type="file" id="fotoInput" name="foto" accept=".jpg, .jpeg, .png" style="display: none;">
                    </div>

                    <div class="profile-title-meta">
                        <h1><?= htmlspecialchars($nombreCompleto) ?></h1>
                        <p>Expediente institucional y credenciales de acceso</p>
                        <span class="role-tag"><i class="fa-solid fa-shield"></i> ROL: <?= htmlspecialchars($rol) ?></span>
                    </div>
                </div>

                <div class="form-grid">
                    
                    <?php if ($esDocente): ?>
                        <!-- PERFIL DOCENTE: DUI, ESCALAFON Y CREDENCIALES -->
                        <div class="form-group">
                            <label>Número de DUI <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-address-card"></i>
                                <input type="text" value="<?= htmlspecialchars($dui) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Número de Escalafón <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-certificate"></i>
                                <input type="text" value="<?= htmlspecialchars($escalafon) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Correo Electrónico <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="text" value="<?= htmlspecialchars($correo) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Cargo / Rol Asignado <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-chalkboard-user"></i>
                                <input type="text" value="Personal Docente" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="section-separator">
                            <i class="fa-solid fa-key"></i> Seguridad: Modificar Contraseña de Acceso Docente
                        </div>

                        <div class="form-group">
                            <label for="nueva_password">Nueva Contraseña</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="nueva_password" name="nueva_password" placeholder="Dejar en blanco para conservar la actual">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_password">Confirmar Contraseña</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock-open"></i>
                                <input type="password" id="confirmar_password" name="confirmar_password" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                    <?php elseif ($esEstudiante): ?>
                        <!-- PERFIL ESTUDIANTE: EXPEDIENTE EN SOLO LECTURA + CONTRASEÑA -->
                        <div class="form-group">
                            <label>NIE (Carné Único) <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-id-badge"></i>
                                <input type="text" value="<?= htmlspecialchars($nie) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Correo Institucional <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="text" value="<?= htmlspecialchars($correo) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Grado Escolar <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <input type="text" value="<?= htmlspecialchars($grado) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Sección Asignada <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-users-rectangle"></i>
                                <input type="text" value="<?= htmlspecialchars($seccion) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Fecha de Nacimiento <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-regular fa-calendar"></i>
                                <input type="text" value="<?= htmlspecialchars($fechaNacimiento) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Género <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-venus-mars"></i>
                                <input type="text" value="<?= htmlspecialchars($genero) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nombre del Encargado / Tutor <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-user-shield"></i>
                                <input type="text" value="<?= htmlspecialchars($encargado) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Teléfono Registrado <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-phone"></i>
                                <input type="text" value="<?= htmlspecialchars($telefono) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group full">
                            <label>Dirección Residencial <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-location-dot"></i>
                                <input type="text" value="<?= htmlspecialchars($direccion) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="section-separator">
                            <i class="fa-solid fa-key"></i> Seguridad: Modificar Contraseña de Acceso
                        </div>

                        <div class="form-group">
                            <label for="nueva_password">Nueva Contraseña</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="nueva_password" name="nueva_password" placeholder="Dejar en blanco para conservar la actual">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_password">Confirmar Contraseña</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock-open"></i>
                                <input type="password" id="confirmar_password" name="confirmar_password" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- PERFIL ADMINISTRADOR: DUI Y DATOS INSTITUCIONALES -->
                        <div class="form-group">
                            <label>Número de DUI <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-address-card"></i>
                                <input type="text" value="<?= htmlspecialchars($dui) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Correo Electrónico <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="text" value="<?= htmlspecialchars($correo) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Privilegio / Rol <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-shield-halved"></i>
                                <input type="text" value="<?= htmlspecialchars($rol) ?>" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Estado de Cuenta <span class="readonly-indicator">(Solo Lectura)</span></label>
                            <div class="input-box">
                                <i class="fa-solid fa-circle-check" style="color: #10b981;"></i>
                                <input type="text" value="Activo" readonly tabindex="-1">
                            </div>
                        </div>

                        <div class="form-group full">
                            <label for="telefono"><i class="fa-solid fa-phone" style="color: var(--primary);"></i> Teléfono de Contacto</label>
                            <div class="input-box">
                                <i class="fa-solid fa-mobile-screen"></i>
                                <input type="tel" id="telefono" name="telefono" placeholder="Ej: 7123-4567" value="<?= htmlspecialchars($telefono) ?>">
                            </div>
                        </div>

                        <div class="form-group full">
                            <label for="direccion"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> Dirección Residencial</label>
                            <textarea id="direccion" name="direccion" placeholder="Dirección de residencia..."><?= htmlspecialchars($direccion) ?></textarea>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="actions-bar">
                    <a href="index.php?action=dashboard" class="btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>

            </form>

        </div>
    </main>

    <div class="modal-overlay" id="modalExito" style="<?= $alertaExito ? 'display: flex;' : '' ?>">
        <div class="modal-alert">
            <div class="modal-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3>¡Cambios Guardados!</h3>
            <p>La información de tu perfil, fotografía y credenciales se han actualizado con éxito.</p>
            <a href="index.php?action=perfil" class="btn-modal-close" onclick="cerrarModal()">Aceptar</a>
        </div>
    </div>

    <script>
        const fotoInput = document.getElementById('fotoInput');
        const avatarPreview = document.getElementById('avatarPreview');

        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2097152) {
                    alert('Error: La fotografía excede el límite máximo de 2MB.');
                    this.value = '';
                    return;
                }
                const extValidas = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!extValidas.includes(file.type)) {
                    alert('Error: Solo se permiten formatos .jpg o .png.');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(event) {
                    avatarPreview.src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('perfilForm').addEventListener('submit', function(e) {
            const p1 = document.getElementById('nueva_password');
            const p2 = document.getElementById('confirmar_password');
            if (p1 && p2 && p1.value.trim() !== '') {
                if (p1.value !== p2.value) {
                    e.preventDefault();
                    alert('Error: Las contraseñas ingresadas no coinciden.');
                    p2.focus();
                }
            }
        });

        function cerrarModal() {
            document.getElementById('modalExito').style.display = 'none';
        }
    </script>
</body>
</html>