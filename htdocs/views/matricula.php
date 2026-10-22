<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
    header('Location: index.php?action=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Matrícula Escolar</title>
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
            max-width: 720px;
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
        select,
        input[type="file"] {
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
            grid-column: span 2;
        }

        .btn-submit {
            flex: 2;
            background: var(--primary);
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
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <div class="brand-logo-circle">
                <img src="img/logo_inca.png" alt="Logo INCA" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
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
                <h2>Matrícula Escolar</h2>
                <p>Inscribe a un estudiante con su expediente completo y datos de contacto verificados.</p>
            </div>

            <form action="index.php?action=guardar_matricula" method="POST" enctype="multipart/form-data" onsubmit="return validarFormularioMatricula(event)">
                <div class="form-grid">
                    
                    <div class="section-subhead">
                        <i class="fa-solid fa-id-card"></i> 1. Identificación y Asignación Académica
                    </div>

                    <div class="form-group full-width">
                        <label for="nie">NIE (Carné Único - 6 a 8 dígitos)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-id-badge"></i>
                            <input type="text" id="nie" name="nie" placeholder="Ej: 12345678" maxlength="8" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombres">Nombres</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="nombres" name="nombres" placeholder="Ej: Carlos Alberto" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="apellidos">Apellidos</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" id="apellidos" name="apellidos" placeholder="Ej: Martínez Pérez" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="grado">Grado Escolar</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <select id="grado" name="grado" required>
                                <option value="1° Grado">1° Grado</option>
                                <option value="2° Grado">2° Grado</option>
                                <option value="3° Grado">3° Grado</option>
                                <option value="4° Grado">4° Grado</option>
                                <option value="5° Grado">5° Grado</option>
                                <option value="6° Grado">6° Grado</option>
                                <option value="7° Grado">7° Grado</option>
                                <option value="8° Grado">8° Grado</option>
                                <option value="9° Grado" selected>9° Grado</option>
                                <option value="1° Año Bachillerato">1° Año Bachillerato</option>
                                <option value="2° Año Bachillerato">2° Año Bachillerato</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="seccion">Sección Asignada</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-users-rectangle"></i>
                            <select id="seccion" name="seccion" required>
                                <option value="Sección A" selected>Sección A</option>
                                <option value="Sección B">Sección B</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="correo">Correo Institucional</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" id="correo" name="correo" placeholder="ejemplo@inca.edu.sv">
                        </div>
                    </div>

                    <div class="section-subhead">
                        <i class="fa-solid fa-user-group"></i> 2. Datos Personales y de Contacto
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-calendar"></i>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
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

                    <div class="form-group">
                        <label for="encargado">Nombre del Encargado / Tutor</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user-shield"></i>
                            <input type="text" id="encargado" name="encargado" placeholder="Ej: Roberto Díaz">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="dui_encargado">DUI del Encargado (00000000-0)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid id-card"></i>
                            <input type="text" id="dui_encargado" name="dui_encargado" placeholder="00000000-0" maxlength="10">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono de Contacto (0000-0000)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" id="telefono" name="telefono" placeholder="7123-4567" maxlength="9">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="direccion">Dirección Residencial</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-location-dot"></i>
                            <input type="text" id="direccion" name="direccion" placeholder="Ej: San Salvador, El Salvador">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="foto">Fotografía de Perfil (Opcional, máx. 2MB)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" id="foto" name="foto" accept=".jpg, .jpeg, .png">
                        </div>
                    </div>

                    <div class="actions-group">
                        <a href="index.php?action=gestion_notas" class="btn-cancel">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-floppy-disk"></i> Registrar Matrícula
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </main>

    <script>
        // Máscara automática para DUI (00000000-0)
        document.getElementById('dui_encargado').addEventListener('input', function (e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 8) {
                val = val.slice(0, 8) + '-' + val.slice(8, 9);
            }
            e.target.value = val;
        });

        // Máscara automática para Teléfono (0000-0000)
        document.getElementById('telefono').addEventListener('input', function (e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.length > 4) {
                val = val.slice(0, 4) + '-' + val.slice(4, 8);
            }
            e.target.value = val;
        });

        // Validación estricta al enviar el formulario
        function validarFormularioMatricula(e) {
            const nie = document.getElementById('nie').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const dui = document.getElementById('dui_encargado').value.trim();

            // Validar NIE (ej: solo números, entre 5 y 8 dígitos)
            const regexNie = /^\d{5,8}$/;
            if (!regexNie.test(nie)) {
                alert('El NIE debe contener solo números y tener entre 5 y 8 dígitos.');
                document.getElementById('nie').focus();
                e.preventDefault();
                return false;
            }

            // Validar Teléfono formato salvadoreño (0000-0000)
            const regexTel = /^\d{4}-\d{4}$/;
            if (telefono !== '' && !regexTel.test(telefono)) {
                alert('El teléfono de contacto debe tener el formato válido 0000-0000.');
                document.getElementById('telefono').focus();
                e.preventDefault();
                return false;
            }

            // Validar DUI formato salvadoreño (00000000-0)
            const regexDui = /^\d{8}-\d{1}$/;
            if (dui !== '' && !regexDui.test(dui)) {
                alert('El DUI del encargado debe tener el formato válido 00000000-0.');
                document.getElementById('dui_encargado').focus();
                e.preventDefault();
                return false;
            }

            return true;
        }
    </script>
</body>
</html>