<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Registro de Nuevas Cuentas</title>
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
            border: 2px solid rgba(255, 255, 255, 0.2);
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
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.35rem;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .section-tag {
            grid-column: span 2;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 0.35rem;
            margin-top: 0.6rem;
            margin-bottom: 0.4rem;
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
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 0.65rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .label-desc {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: normal;
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
            pointer-events: none;
            z-index: 2;
        }

        input[type="text"],
        input[type="tel"],
        input[type="file"],
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

        input[readonly] {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            color: #475569;
            font-weight: 600;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* Contenedor de Badges para materias seleccionadas */
        .materias-badges-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            min-height: 28px;
        }

        .materia-badge {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            animation: popIn 0.2s ease-out;
        }

        .materia-badge i.remove-btn {
            cursor: pointer;
            color: #0284c7;
            transition: color 0.2s;
        }

        .materia-badge i.remove-btn:hover {
            color: #dc2626;
        }

        .preview-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 0.85rem 1rem;
            grid-column: span 2;
            margin-top: 0.25rem;
            font-size: 0.88rem;
            color: #1e40af;
            line-height: 1.4;
        }

        .actions-group {
            display: flex;
            gap: 12px;
            margin-top: 1.75rem;
        }

        .btn-submit {
            flex: 2;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
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

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-alert {
            background: #ffffff;
            border-radius: 14px;
            padding: 2.2rem 2rem;
            max-width: 440px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
            animation: popIn 0.25s ease-out;
        }

        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 1.8rem;
        }

        .icon-success { background: #dcfce7; color: #16a34a; }
        .icon-error { background: #fee2e2; color: #dc2626; }

        .modal-alert h3 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .modal-alert p {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            line-height: 1.45;
        }

        .btn-modal-action {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        @media (max-width: 640px) {
            .navbar { padding: 0.85rem 1.25rem; }
            .form-grid { grid-template-columns: 1fr; }
            .form-group.full-width, .section-tag, .preview-box { grid-column: span 1; }
            .card-register { padding: 1.75rem; }
            .actions-group { flex-direction: column-reverse; }
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

    <main class="main-container">
        <div class="card-register">
            <div class="card-header">
                <h2>Registro de Nuevas Cuentas</h2>
                <p>Generación automatizada de credenciales y asignación de perfil institucional.</p>
            </div>

            <form action="index.php?action=guardar_usuario" method="POST" enctype="multipart/form-data" id="registroForm">
                <div class="form-grid">
                    
                    <div class="section-tag">
                        <i class="fa-solid fa-user-gear"></i> 1. Tipo de Cuenta y Datos Personales
                    </div>

                    <div class="form-group full-width">
                        <label for="rol">Rol Institucional</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-shield-halved"></i>
                            <select id="rol" name="rol" onchange="actualizarGeneracionCredenciales()" required>
                                <option value="estudiante" selected>Estudiante</option>
                                <option value="docente">Docente</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>

                    <!-- Estudiante: NIE -->
                    <div class="form-group full-width" id="grupoNie">
                        <label for="nie">NIE (Carné Único del Alumno)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-badge"></i>
                            <input type="text" id="nie" name="nie" placeholder="Ej: 0225558" oninput="actualizarGeneracionCredenciales()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombres</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="nombre" name="nombre" placeholder="Ej: Carlos Mauricio" oninput="actualizarGeneracionCredenciales()" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellidos</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="apellido" name="apellido" placeholder="Ej: Martinez Lopez" oninput="actualizarGeneracionCredenciales()" required>
                        </div>
                    </div>

                    <!-- DUI con formato 00000000-0 -->
                    <div class="form-group" id="grupoDui" style="display: none;">
                        <label for="dui">Número de DUI <span class="label-desc">00000000-0</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-address-card"></i>
                            <input type="text" id="dui" name="dui" placeholder="00000000-0" maxlength="10" oninput="aplicarMascaraDUI(this)">
                        </div>
                    </div>

                    <!-- Escalafón con prefijo ESC- fijo y máx 5 dígitos -->
                    <div class="form-group" id="grupoEscalafon" style="display: none;">
                        <label for="escalafon">Número de Escalafón <span class="label-desc">ESC- + 5 dígitos</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-certificate"></i>
                            <input type="text" id="escalafon" name="escalafon" value="ESC-" maxlength="9" oninput="aplicarMascaraEscalafon(this)" onkeydown="prevenirBorrarPrefijo(event, this)">
                        </div>
                    </div>

                    <!-- Teléfono con formato 0000-0000 -->
                    <div class="form-group" id="grupoTelefono">
                        <label for="telefono">Teléfono de Contacto <span class="label-desc">0000-0000</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-phone"></i>
                            <input type="tel" id="telefono" name="telefono" placeholder="7123-4567" maxlength="9" oninput="aplicarMascaraTelefono(this)" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="genero">Género</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-venus-mars"></i>
                            <select id="genero" name="genero">
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <!-- ADMINISTRADOR: CARGO -->
                    <div class="form-group full-width" id="grupoAdminCargo" style="display: none;">
                        <label for="cargo">Cargo Institucional</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-briefcase"></i>
                            <select id="cargo" name="cargo">
                                <option value="Directora">Directora</option>
                                <option value="Registro academico">Registro académico</option>
                                <option value="Administrador" selected>Administrador</option>
                            </select>
                        </div>
                    </div>

                    <!-- DOCENTE: MATERIAS CON SELECTOR IDÉNTICO AL DE ORIENTACIÓN Y CHIPS ACUMULABLES -->
                    <div class="form-group full-width" id="grupoDocenteMaterias" style="display: none;">
                        <label for="selectorMateriaCombo">Materias que imparte <span class="label-desc">(Máximo 2 asignaturas)</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-book"></i>
                            <select id="selectorMateriaCombo" onchange="agregarMateriaDesdeSelect(this)">
                                <option value="" selected>-- Seleccionar materia a impartir --</option>
                                <option value="Matemática">Matemática</option>
                                <option value="Lenguaje y Literatura">Lenguaje y Literatura</option>
                                <option value="Estudios Sociales y Cívica">Estudios Sociales y Cívica</option>
                                <option value="Ciencia y Tecnología">Ciencia y Tecnología</option>
                                <option value="Idioma Extranjero (Inglés)">Idioma Extranjero (Inglés)</option>
                                <option value="Educación Física">Educación Física</option>
                            </select>
                        </div>

                        <!-- Aquí se muestran las materias elegidas -->
                        <div class="materias-badges-container" id="badgesContainer"></div>
                        <!-- Campos ocultos reales que enviará el formulario -->
                        <div id="hiddenInputsMaterias"></div>
                    </div>

                    <!-- DOCENTE: ORIENTACIÓN -->
                    <div class="form-group full-width" id="grupoDocenteOrientacion" style="display: none;">
                        <label for="grado_orientacion">Grado de Orientación Asignado</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-compass"></i>
                            <select id="grado_orientacion" name="grado_orientacion">
                                <option value="Ninguno" selected>Ninguno</option>
                                <option value="1° Grado">1° Grado</option>
                                <option value="2° Grado">2° Grado</option>
                                <option value="3° Grado">3° Grado</option>
                                <option value="4° Grado">4° Grado</option>
                                <option value="5° Grado">5° Grado</option>
                                <option value="6° Grado">6° Grado</option>
                                <option value="7° Grado">7° Grado</option>
                                <option value="8° Grado">8° Grado</option>
                                <option value="9° Grado">9° Grado</option>
                                <option value="1° Año Bachillerato">1° Año Bachillerato</option>
                                <option value="2° Año Bachillerato">2° Año Bachillerato</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="foto">Fotografía de Perfil <span class="label-desc">Opcional (.jpg, .png máx 2MB)</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" id="foto" name="foto" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>

                    <div class="section-tag">
                        <i class="fa-solid fa-key"></i> 2. Credenciales Autogeneradas
                    </div>

                    <div class="form-group full-width">
                        <label for="usuario">Usuario / Correo Institucional <span class="label-desc">(Autogenerado)</span></label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="text" id="usuario" name="usuario" readonly tabindex="-1">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="password_preview">Contraseña Inicial de Acceso <span class="label-desc">(Autogenerada)</span></label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="text" id="password_preview" name="password_preview" readonly tabindex="-1">
                            <input type="hidden" id="password" name="password">
                        </div>
                    </div>

                    <div class="preview-box" id="infoBox"></div>

                </div>

                <div class="actions-group">
                    <a href="index.php?action=dashboard" class="btn-cancel">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-user-plus"></i> Registrar Cuenta
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Modal Éxito -->
    <?php if ($msg === 'creado'): ?>
    <div class="modal-overlay" id="modalExito">
        <div class="modal-alert">
            <div class="modal-icon icon-success">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3>¡Cuenta Creada!</h3>
            <p>El perfil institucional ha sido registrado con sus credenciales y asignaciones activas.</p>
            <a href="index.php?action=registro" class="btn-modal-action">Aceptar</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Error: Duplicado -->
    <?php if ($error === 'duplicado'): ?>
    <div class="modal-overlay" id="modalDuplicado">
        <div class="modal-alert">
            <div class="modal-icon icon-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3>Usuario Ya Existe</h3>
            <p>El correo institucional autogenerado ya se encuentra asignado a otra cuenta.</p>
            <button class="btn-modal-action" style="background: #dc2626;" onclick="cerrarAviso('modalDuplicado')">Entendido</button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Error General / Formatos -->
    <?php if ($error === 'formato_invalido' || $error === 'campos_vacios' || $error === 'materias_excedidas' || $error === 'fallo_sql'): ?>
    <div class="modal-overlay" id="modalError">
        <div class="modal-alert">
            <div class="modal-icon icon-error">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h3>Error de Validación</h3>
            <p>
                <?php 
                if ($error === 'formato_invalido') {
                    echo 'Comprueba los formatos: DUI (00000000-0), Teléfono (0000-0000) o Escalafón (ESC- seguido de 5 números).';
                } elseif ($error === 'materias_excedidas') {
                    echo 'Solo puedes seleccionar un máximo de 2 materias para el docente.';
                } elseif ($error === 'campos_vacios') {
                    echo 'Por favor completa todos los campos requeridos.';
                } else {
                    echo 'Ocurrió un inconveniente al guardar en la base de datos.';
                }
                ?>
            </p>
            <button class="btn-modal-action" style="background: #dc2626;" onclick="cerrarAviso('modalError')">Cerrar</button>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // MÁSCARAS
        function aplicarMascaraDUI(input) {
            let v = input.value.replace(/\D/g, '').slice(0, 9);
            if (v.length > 8) {
                v = v.slice(0, 8) + '-' + v.slice(8);
            }
            input.value = v;
        }

        function aplicarMascaraTelefono(input) {
            let v = input.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 4) {
                v = v.slice(0, 4) + '-' + v.slice(4);
            }
            input.value = v;
        }

        // CONTROL ESTRICTO DE ESCALAFÓN: PREFIJO ESC- FIJO + HASTA 5 DÍGITOS
        function aplicarMascaraEscalafon(input) {
            let val = input.value.toUpperCase();
            if (!val.startsWith('ESC-')) {
                val = 'ESC-';
            }
            let numeros = val.substring(4).replace(/\D/g, '').slice(0, 5);
            input.value = 'ESC-' + numeros;
        }

        function prevenirBorrarPrefijo(e, input) {
            // Evitar que Backspace o Delete borren el prefijo 'ESC-'
            if ((e.key === 'Backspace' || e.key === 'Delete') && input.selectionStart <= 4 && input.selectionEnd <= 4) {
                e.preventDefault();
            }
        }

        // MANEJO DINÁMICO DE MATERIAS CON COMBOBOX + BADGES (MÁXIMO 2)
        let materiasSeleccionadas = [];

        function agregarMateriaDesdeSelect(select) {
            const materia = select.value;
            if (!materia) return;

            if (materiasSeleccionadas.includes(materia)) {
                alert('La asignatura ya fue agregada.');
                select.value = '';
                return;
            }

            if (materiasSeleccionadas.length >= 2) {
                alert('Solo puedes seleccionar un máximo de 2 materias que imparte el docente.');
                select.value = '';
                return;
            }

            materiasSeleccionadas.push(materia);
            select.value = '';
            renderizarMaterias();
        }

        function removerMateria(materia) {
            materiasSeleccionadas = materiasSeleccionadas.filter(m => m !== materia);
            renderizarMaterias();
        }

        function renderizarMaterias() {
            const container = document.getElementById('badgesContainer');
            const hiddenInputs = document.getElementById('hiddenInputsMaterias');
            const select = document.getElementById('selectorMateriaCombo');

            container.innerHTML = '';
            hiddenInputs.innerHTML = '';

            materiasSeleccionadas.forEach(mat => {
                // Crear badge visual
                const badge = document.createElement('div');
                badge.className = 'materia-badge';
                badge.innerHTML = `<span><i class="fa-solid fa-book"></i> ${mat}</span> <i class="fa-solid fa-xmark remove-btn" onclick="removerMateria('${mat}')" title="Eliminar"></i>`;
                container.appendChild(badge);

                // Crear input hidden para envío en POST
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'materias_imparte[]';
                hidden.value = mat;
                hiddenInputs.appendChild(hidden);
            });

            // Si ya hay 2 materias, deshabilita temporalmente el combobox
            if (materiasSeleccionadas.length >= 2) {
                select.disabled = true;
            } else {
                select.disabled = false;
            }
        }

        function limpiarTexto(str) {
            return (str || '')
                .toLowerCase()
                .trim()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9]/g, "");
        }

        function actualizarGeneracionCredenciales() {
            const rol = document.getElementById('rol').value.toLowerCase();
            const grupoNie = document.getElementById('grupoNie');
            const grupoDui = document.getElementById('grupoDui');
            const duiInput = document.getElementById('dui');
            const grupoEscalafon = document.getElementById('grupoEscalafon');
            const escalafonInput = document.getElementById('escalafon');
            const grupoAdminCargo = document.getElementById('grupoAdminCargo');
            const grupoDocenteMaterias = document.getElementById('grupoDocenteMaterias');
            const grupoDocenteOrientacion = document.getElementById('grupoDocenteOrientacion');
            
            const nieInput = document.getElementById('nie');
            const nombreInput = document.getElementById('nombre');
            const apellidoInput = document.getElementById('apellido');
            const usuarioInput = document.getElementById('usuario');
            const passPreview = document.getElementById('password_preview');
            const passHidden = document.getElementById('password');
            const infoBox = document.getElementById('infoBox');

            if (rol === 'estudiante') {
                grupoNie.style.display = 'flex';
                nieInput.setAttribute('required', 'required');

                grupoDui.style.display = 'none';
                duiInput.removeAttribute('required');

                grupoEscalafon.style.display = 'none';
                escalafonInput.removeAttribute('required');

                grupoAdminCargo.style.display = 'none';
                grupoDocenteMaterias.style.display = 'none';
                grupoDocenteOrientacion.style.display = 'none';

                const nieLimpio = (nieInput.value || '').trim();
                const correo = (nieLimpio ? nieLimpio : 'nie') + '@inca.edu.sv';
                const clave = nieLimpio ? nieLimpio : 'El mismo NIE';

                usuarioInput.value = nieLimpio ? correo : '';
                passPreview.value = nieLimpio ? clave : '';
                passHidden.value = nieLimpio;

                infoBox.innerHTML = '<i class="fa-solid fa-circle-info"></i> Para <strong>Estudiantes</strong>, el usuario/correo se generará con su NIE y extensión <code>@inca.edu.sv</code>, y la contraseña inicial será su mismo NIE.';
            } else if (rol === 'docente') {
                grupoNie.style.display = 'none';
                nieInput.removeAttribute('required');

                grupoDui.style.display = 'flex';
                duiInput.setAttribute('required', 'required');

                grupoEscalafon.style.display = 'flex';
                escalafonInput.setAttribute('required', 'required');

                grupoAdminCargo.style.display = 'none';
                grupoDocenteMaterias.style.display = 'flex';
                grupoDocenteOrientacion.style.display = 'flex';

                const primerNombre = limpiarTexto(nombreInput.value.split(' ')[0]);
                const primerApellido = limpiarTexto(apellidoInput.value.split(' ')[0]);

                let usuarioGenerado = '';
                if (primerNombre && primerApellido) {
                    usuarioGenerado = primerNombre + '_' + primerApellido + '@inca.edu.sv';
                }

                usuarioInput.value = usuarioGenerado;
                passPreview.value = '12345';
                passHidden.value = '12345';

                infoBox.innerHTML = '<i class="fa-solid fa-circle-info"></i> Para <strong>Docentes</strong>, se valida obligatoriamente <strong>DUI</strong>, <strong>Teléfono</strong>, <strong>Escalafón (ESC- + 5 dígitos)</strong>, selección acumulable de hasta 2 materias y grado de orientación.';
            } else { // admin
                grupoNie.style.display = 'none';
                nieInput.removeAttribute('required');

                grupoDui.style.display = 'flex';
                duiInput.setAttribute('required', 'required');

                grupoEscalafon.style.display = 'none';
                escalafonInput.removeAttribute('required');

                grupoAdminCargo.style.display = 'flex';
                grupoDocenteMaterias.style.display = 'none';
                grupoDocenteOrientacion.style.display = 'none';

                const primerNombre = limpiarTexto(nombreInput.value.split(' ')[0]);
                const primerApellido = limpiarTexto(apellidoInput.value.split(' ')[0]);

                let usuarioGenerado = '';
                if (primerNombre && primerApellido) {
                    usuarioGenerado = primerNombre + '_' + primerApellido + '@inca.edu.sv';
                }

                usuarioInput.value = usuarioGenerado;
                passPreview.value = '12345';
                passHidden.value = '12345';

                infoBox.innerHTML = '<i class="fa-solid fa-circle-info"></i> Para <strong>Administradores</strong>, se valida obligatoriamente su <strong>DUI</strong>, <strong>Teléfono</strong> y su cargo institucional asignado.';
            }
        }

        // Validación final antes de enviar
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const rol = document.getElementById('rol').value.toLowerCase();
            const tel = document.getElementById('telefono').value.trim();

            if (!/^\d{4}-\d{4}$/.test(tel)) {
                e.preventDefault();
                alert('Error: El formato del teléfono debe ser 0000-0000 (8 números con guion central).');
                document.getElementById('telefono').focus();
                return;
            }

            if (rol === 'docente' || rol === 'admin') {
                const dui = document.getElementById('dui').value.trim();
                if (!/^\d{8}-\d{1}$/.test(dui)) {
                    e.preventDefault();
                    alert('Error: El formato del DUI debe ser 00000000-0 (8 números, guion y dígito verificador).');
                    document.getElementById('dui').focus();
                    return;
                }
            }

            if (rol === 'docente') {
                const esc = document.getElementById('escalafon').value.trim();
                // Formato exacto ESC- seguido de 1 a 5 dígitos
                if (!/^ESC-\d{1,5}$/.test(esc)) {
                    e.preventDefault();
                    alert('Error: El Escalafón debe tener el prefijo ESC- seguido de hasta 5 dígitos numéricos (ej. ESC-12345).');
                    document.getElementById('escalafon').focus();
                    return;
                }

                if (materiasSeleccionadas.length === 0) {
                    e.preventDefault();
                    alert('Por favor selecciona al menos una materia que imparte el docente.');
                    document.getElementById('selectorMateriaCombo').focus();
                    return;
                }

                if (materiasSeleccionadas.length > 2) {
                    e.preventDefault();
                    alert('Solo puedes seleccionar un máximo de 2 materias.');
                    return;
                }
            }
        });

        function cerrarAviso(id) {
            const modal = document.getElementById(id);
            if (modal) modal.style.display = 'none';
        }

        actualizarGeneracionCredenciales();
    </script>
</body>
</html>