<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../config/Conexion.php')) {
    require_once __DIR__ . '/../config/Conexion.php';
} elseif (file_exists(__DIR__ . '/../config/conexion.php')) {
    require_once __DIR__ . '/../config/conexion.php';
}

if (!class_exists('AcademicoController')) {

    class AcademicoController {
        private $db;

        public function __construct() {
            if (class_exists('Conexion')) {
                $conexionObj = new Conexion();
                $this->db = $conexionObj->conectar();
            } else {
                global $pdo;
                $this->db = $pdo;
            }
        }

        public function login() {
            $error = '';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $usuario  = trim($_POST['usuario'] ?? $_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (!empty($usuario) && !empty($password)) {
                    try {
                        $sql = "SELECT * FROM usuarios 
                                WHERE username = :u 
                                   OR correo = :u 
                                   OR CONCAT(nombre, ' ', apellido) LIKE :uLike 
                                LIMIT 1";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            ':u'     => $usuario,
                            ':uLike' => '%' . $usuario . '%'
                        ]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
                            $_SESSION['usuario_id'] = $user['id'];
                            $_SESSION['usuario']    = $user['username'] ?? $user['correo'];
                            $_SESSION['rol']        = $user['rol'] ?? 'Docente';
                            
                            $nombreReal = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));
                            $_SESSION['nombre']     = !empty($nombreReal) ? $nombreReal : ($user['username'] ?? 'Usuario');
                            $_SESSION['foto']       = $user['foto'] ?? '';

                            header('Location: index.php?action=dashboard');
                            exit;
                        } else {
                            $error = "Usuario o contraseña incorrectos.";
                        }
                    } catch (PDOException $e) {
                        $error = "Error de conexión con la base de datos.";
                    }
                } else {
                    $error = "Por favor completa todos los campos.";
                }
            }

            require_once __DIR__ . '/../views/login.php';
        }

        public function dashboard() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }
            require_once __DIR__ . '/../views/dashboard.php';
        }

        public function controlNotas() {
            $this->gestionNotas();
        }

        public function gestionNotas() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $rol = strtoupper($_SESSION['rol'] ?? '');
            if ($rol === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $estudiantes = [];
            try {
                $stmt = $this->db->query("SELECT id, nie, nombre, apellido, correo, grado, seccion, encargado, telefono, direccion, fecha_nacimiento, genero, foto FROM estudiantes ORDER BY apellido ASC, nombre ASC");
                $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $estudiantes = [];
            }

            if ($rol === 'ADMIN' || $rol === 'ADMINISTRADOR') {
                require_once __DIR__ . '/../views/gestion_notas.php';
            } else {
                require_once __DIR__ . '/../views/gestion_notas_docente.php';
            }
        }

        public function gestionDocentes() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            try {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS escalafon VARCHAR(50) DEFAULT NULL");
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS grado_orientacion VARCHAR(100) DEFAULT NULL");
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS materias_imparte TEXT DEFAULT NULL");
            } catch (PDOException $e) {}

            $docentes = [];
            try {
                $stmt = $this->db->query("SELECT * FROM usuarios WHERE rol = 'Docente' OR rol = 'DOCENTE' ORDER BY apellido ASC, nombre ASC");
                $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $docentes = [];
            }

            require_once __DIR__ . '/../views/gestion_docentes.php';
        }

        public function actualizarDocenteGestion() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id                = intval($_POST['id'] ?? 0);
                $escalafon         = trim($_POST['escalafon'] ?? '');
                $grado_orientacion = trim($_POST['grado_orientacion'] ?? '');
                $materiasArray     = $_POST['materias'] ?? [];
                $materias_imparte  = implode(', ', $materiasArray);

                if ($id > 0) {
                    try {
                        $stmt = $this->db->prepare("UPDATE usuarios SET escalafon = :esc, grado_orientacion = :grado, materias_imparte = :mat WHERE id = :id");
                        $stmt->execute([
                            ':esc'   => $escalafon,
                            ':grado' => $grado_orientacion,
                            ':mat'   => $materias_imparte,
                            ':id'    => $id
                        ]);
                    } catch (PDOException $e) {}
                }

                header('Location: index.php?action=gestion_docentes&msg=docente_actualizado');
                exit;
            }
        }

        public function usuariosLista() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $datosCargados = [];
            try {
                $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY id DESC");
                $datosCargados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($datosCargados)) {
                    $stmtEst = $this->db->query("SELECT id, nie AS username, nombre, apellido, correo, 'ESTUDIANTE' AS rol, telefono, genero, foto FROM estudiantes ORDER BY id DESC");
                    $datosCargados = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                $datosCargados = [];
            }

            $perfiles = $datosCargados;
            $usuarios = $datosCargados;
            $listaUsuarios = $datosCargados;
            $estudiantes = $datosCargados;

            $vistaDirectorio = __DIR__ . '/../views/usuarios_lista.php';
            if (file_exists($vistaDirectorio)) {
                require_once $vistaDirectorio;
            } else {
                header('Location: index.php?action=gestion_notas');
                exit;
            }
        }

        public function actualizarUsuarioDirectorio() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id       = intval($_POST['id'] ?? 0);
                $nombres  = trim($_POST['nombre'] ?? $_POST['nombres'] ?? '');
                $apellidos= trim($_POST['apellido'] ?? $_POST['apellidos'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $genero   = trim($_POST['genero'] ?? 'Masculino');
                $password = $_POST['nueva_password'] ?? '';

                if ($id > 0) {
                    try {
                        if (!empty($password)) {
                            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :n, apellido = :a, telefono = :t, genero = :g, password = :p WHERE id = :id");
                            $stmt->execute([
                                ':n'  => $nombres,
                                ':a'  => $apellidos,
                                ':t'  => $telefono,
                                ':g'  => $genero,
                                ':p'  => $password,
                                ':id' => $id
                            ]);
                        } else {
                            $stmt = $this->db->prepare("UPDATE usuarios SET nombre = :n, apellido = :a, telefono = :t, genero = :g WHERE id = :id");
                            $stmt->execute([
                                ':n'  => $nombres,
                                ':a'  => $apellidos,
                                ':t'  => $telefono,
                                ':g'  => $genero,
                                ':id' => $id
                            ]);
                        }
                    } catch (PDOException $e) {
                        try {
                            $stmtEst = $this->db->prepare("UPDATE estudiantes SET nombre = :n, apellido = :a, telefono = :t, genero = :g WHERE id = :id");
                            $stmtEst->execute([
                                ':n'  => $nombres,
                                ':a'  => $apellidos,
                                ':t'  => $telefono,
                                ':g'  => $genero,
                                ':id' => $id
                            ]);
                        } catch (PDOException $ex) {}
                    }
                }

                header('Location: index.php?action=usuarios_lista&msg=perfil_actualizado');
                exit;
            }
        }

        public function eliminarUsuario() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                try {
                    $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
                    $stmt->execute([':id' => $id]);

                    if ($stmt->rowCount() === 0) {
                        $stmtEst = $this->db->prepare("DELETE FROM estudiantes WHERE id = :id");
                        $stmtEst->execute([':id' => $id]);
                    }
                } catch (PDOException $e) {}
            }

            header('Location: index.php?action=usuarios_lista&msg=usuario_eliminado');
            exit;
        }

        public function registroUsuarios() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $vistaRegistro = __DIR__ . '/../views/registro.php';
            if (file_exists($vistaRegistro)) {
                require_once $vistaRegistro;
            } else {
                header('Location: index.php?action=dashboard');
                exit;
            }
        }

        public function datosPersonales() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $usuarioData = [];
            try {
                $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $_SESSION['usuario_id']]);
                $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                
                if (!empty($usuarioData['foto'])) {
                    $_SESSION['foto'] = trim($usuarioData['foto']);
                }
            } catch (PDOException $e) {
                $usuarioData = [];
            }

            $posiblesVistas = [
                __DIR__ . '/../views/datos_personales.php',
                __DIR__ . '/../views/perfil.php',
                __DIR__ . '/../views/mi_perfil.php',
                __DIR__ . '/../views/editar_usuario.php'
            ];

            $vistaEncontrada = '';
            foreach ($posiblesVistas as $rutaVista) {
                if (file_exists($rutaVista)) {
                    $vistaEncontrada = $rutaVista;
                    break;
                }
            }

            if (!empty($vistaEncontrada)) {
                require_once $vistaEncontrada;
            } else {
                echo "<div style='padding:20px; font-family:sans-serif;'><h3>⚠️ Aviso</h3><p>Falta el archivo de vista en <code>views/</code>.</p></div>";
            }
        }

        public function actualizarFotoPerfil() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['foto'];
                if ($file['size'] <= 2097152) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $dir = __DIR__ . '/../uploads/';
                        if (!is_dir($dir)) mkdir($dir, 0777, true);
                        
                        $fotoNombre = 'user_' . $_SESSION['usuario_id'] . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($file['tmp_name'], $dir . $fotoNombre)) {
                            try {
                                $stmt = $this->db->prepare("UPDATE usuarios SET foto = :f WHERE id = :id");
                                $stmt->execute([
                                    ':f'  => $fotoNombre,
                                    ':id' => $_SESSION['usuario_id']
                                ]);
                                $_SESSION['foto'] = $fotoNombre;
                            } catch (PDOException $e) {}
                        }
                    }
                }
            }

            header('Location: index.php?action=datos_personales&msg=foto_actualizada');
            exit;
        }

        public function actualizarPassword() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nuevaPassword = $_POST['nueva_password'] ?? $_POST['password'] ?? $_POST['new_password'] ?? $_POST['contrasena'] ?? '';

                if (!empty($nuevaPassword)) {
                    try {
                        $stmt = $this->db->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
                        $stmt->execute([
                            ':p'  => $nuevaPassword,
                            ':id' => $_SESSION['usuario_id']
                        ]);

                        header('Location: index.php?action=datos_personales&msg=password_actualizado');
                        exit;
                    } catch (PDOException $e) {
                        header('Location: index.php?action=datos_personales&error=sql_error');
                        exit;
                    }
                } else {
                    header('Location: index.php?action=datos_personales&error=password_vacio');
                    exit;
                }
            }
        }

        public function actualizarDatosPersonales() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $telefono  = trim($_POST['telefono'] ?? '');
                $direccion = trim($_POST['direccion'] ?? '');
                $password  = $_POST['nueva_password'] ?? $_POST['password'] ?? $_POST['new_password'] ?? '';

                try {
                    if (!empty($password)) {
                        $stmt = $this->db->prepare("UPDATE usuarios SET telefono = :tel, direccion = :dir, password = :p WHERE id = :id");
                        $stmt->execute([
                            ':tel' => $telefono,
                            ':dir' => $direccion,
                            ':p'   => $password,
                            ':id'  => $_SESSION['usuario_id']
                        ]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE usuarios SET telefono = :tel, direccion = :dir WHERE id = :id");
                        $stmt->execute([
                            ':tel' => $telefono,
                            ':dir' => $direccion,
                            ':id'  => $_SESSION['usuario_id']
                        ]);
                    }

                    header('Location: index.php?action=datos_personales&msg=actualizado_exitoso');
                    exit;
                } catch (PDOException $e) {
                    header('Location: index.php?action=datos_personales&error=sql_error');
                    exit;
                }
            }
        }

        public function matricula() {
            $this->mostrarMatricula();
        }

        public function mostrarMatricula() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if (strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            require_once __DIR__ . '/../views/matricula.php';
        }

        public function guardarMatricula() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nie              = trim($_POST['nie'] ?? '');
                $nombre           = trim($_POST['nombres'] ?? '');
                $apellido         = trim($_POST['apellidos'] ?? '');
                $correo           = trim($_POST['correo'] ?? '');
                $grado            = trim($_POST['grado'] ?? '9° Grado');
                $seccion          = trim($_POST['seccion'] ?? 'Sección A');
                $encargado        = trim($_POST['encargado'] ?? '');
                $dui_encargado    = trim($_POST['dui_encargado'] ?? '');
                $telefono         = trim($_POST['telefono'] ?? '');
                $direccion        = trim($_POST['direccion'] ?? '');
                $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
                $genero           = trim($_POST['genero'] ?? 'Masculino');
                $fotoNombre       = null;

                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['foto'];
                    if ($file['size'] <= 2097152) {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $dir = __DIR__ . '/../uploads/';
                            if (!is_dir($dir)) mkdir($dir, 0777, true);
                            $fotoNombre = 'perfil_' . preg_replace('/[^a-zA-Z0-9]/', '', $nie) . '_' . time() . '.' . $ext;
                            move_uploaded_file($file['tmp_name'], $dir . $fotoNombre);
                        }
                    }
                }

                try {
                    $sql = "INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion, encargado, dui_encargado, telefono, direccion, fecha_nacimiento, genero, foto) 
                            VALUES (:nie, :nombre, :apellido, :correo, :grado, :seccion, :encargado, :dui_encargado, :telefono, :direccion, :fecha_nacimiento, :genero, :foto)
                            ON DUPLICATE KEY UPDATE 
                            nombre = VALUES(nombre), 
                            apellido = VALUES(apellido), 
                            correo = VALUES(correo), 
                            grado = VALUES(grado), 
                            seccion = VALUES(seccion), 
                            encargado = VALUES(encargado), 
                            dui_encargado = VALUES(dui_encargado), 
                            telefono = VALUES(telefono), 
                            direccion = VALUES(direccion), 
                            fecha_nacimiento = VALUES(fecha_nacimiento), 
                            genero = VALUES(genero), 
                            foto = COALESCE(VALUES(foto), foto)";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':nie'              => $nie,
                        ':nombre'           => $nombre,
                        ':apellido'         => $apellido,
                        ':correo'           => $correo,
                        ':grado'            => $grado,
                        ':seccion'          => $seccion,
                        ':encargado'        => $encargado,
                        ':dui_encargado'    => $dui_encargado,
                        ':telefono'         => $telefono,
                        ':direccion'        => $direccion,
                        ':fecha_nacimiento' => $fecha_nacimiento,
                        ':genero'           => $genero,
                        ':foto'             => $fotoNombre
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=matricula_exitosa');
                    exit;
                } catch (PDOException $e) {
                    die("Error al registrar matrícula: " . $e->getMessage());
                }
            }
        }

  public function guardarNota() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $estudianteId = intval($_POST['estudiante_id'] ?? 0);
                $materia      = trim(html_entity_decode($_POST['materia'] ?? 'Matemática', ENT_QUOTES, 'UTF-8'));
                // Asegurar que el periodo se reciba como entero (1, 2, 3 o 4)
                $periodo      = intval($_POST['periodo'] ?? 1);
                $act1         = floatval($_POST['act1'] ?? 0);
                $act2         = floatval($_POST['act2'] ?? 0);
                $examen       = floatval($_POST['examen'] ?? 0);

                if ($estudianteId <= 0) {
                    header('Location: index.php?action=gestion_notas&error=estudiante_invalido');
                    exit;
                }

                // Cálculo oficial idéntico en PHP utilizando redondeo estándar
                $notaPeriodo = round(($act1 * 0.35) + ($act2 * 0.35) + ($examen * 0.30), 1, PHP_ROUND_HALF_UP);

                try {
                    $sql = "INSERT INTO notas_periodos (estudiante_id, materia, periodo, act1, act2, examen, nota_periodo) 
                            VALUES (:estudiante_id, :materia, :periodo, :act1, :act2, :examen, :nota_periodo)
                            ON DUPLICATE KEY UPDATE 
                            act1 = VALUES(act1), 
                            act2 = VALUES(act2), 
                            examen = VALUES(examen), 
                            nota_periodo = VALUES(nota_periodo)";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':estudiante_id' => $estudianteId,
                        ':materia'       => $materia,
                        ':periodo'       => $periodo,
                        ':act1'          => $act1,
                        ':act2'          => $act2,
                        ':examen'        => $examen,
                        ':nota_periodo'  => $notaPeriodo
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=nota_guardada');
                    exit;
                } catch (PDOException $e) {
                    die("Error al registrar notas: " . $e->getMessage());
                }
            }
        }
      public function boletaNotas() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $estudianteId = intval($_GET['estudiante_id'] ?? $_GET['id'] ?? 0);
            $estudiante = [];

            if ($estudianteId > 0) {
                try {
                    $stmt = $this->db->prepare("SELECT * FROM estudiantes WHERE id = :id LIMIT 1");
                    $stmt->execute([':id' => $estudianteId]);
                    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                } catch (PDOException $e) {}
            }

            if (!empty($estudiante)) {
                $nom = trim($estudiante['nombre'] ?? '');
                $ape = trim($estudiante['apellido'] ?? '');
                $estudiante['nombre_completo'] = trim($nom . ' ' . $ape) ?: 'Estudiante INCA';
                if (empty($estudiante['nie'])) $estudiante['nie'] = 'NIE-' . str_pad($estudiante['id'] ?? 1, 6, '0', STR_PAD_LEFT);
                if (empty($estudiante['grado'])) $estudiante['grado'] = '9° Grado';
                if (empty($estudiante['seccion'])) $estudiante['seccion'] = 'Sección A';
            }

            $calificaciones = [];
            $notas = [];
            if ($estudianteId > 0) {
                try {
                    $stmtNotas = $this->db->prepare("SELECT materia, periodo, act1, act2, examen, nota_periodo FROM notas_periodos WHERE estudiante_id = :id");
                    $stmtNotas->execute([':id' => $estudianteId]);
                    $filas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($filas as $f) {
                        $calificaciones[] = $f;
                        // Proveer los dos formatos de estructura de notas que usan las diferentes versiones de la boleta
                        $notas[] = [
                            'materia'  => trim($f['materia']),
                            'periodo'  => 'Periodo ' . $f['periodo'],
                            'promedio' => $f['nota_periodo']
                        ];
                    }
                } catch (PDOException $e) {
                    $calificaciones = [];
                    $notas = [];
                }
            }

            $vistaBoleta = __DIR__ . '/../views/boleta_notas.php';
            if (file_exists($vistaBoleta)) {
                require_once $vistaBoleta;
            } else {
                echo "No se encontró el archivo de la vista boleta_notas.php";
            }
        }
        public function editarEstudiante() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $id = intval($_GET['id'] ?? 0);
            $estudianteData = [];

            try {
                $stmt = $this->db->prepare("SELECT * FROM estudiantes WHERE id = :id LIMIT 1");
                $stmt->execute([':id' => $id]);
                $estudianteData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                if (empty($estudianteData['grado'])) {
                    $estudianteData['grado'] = '9° Grado';
                }
                if (empty($estudianteData['seccion'])) {
                    $estudianteData['seccion'] = 'Sección A';
                }
            } catch (PDOException $e) {
                $estudianteData = [];
            }

            require_once __DIR__ . '/../views/editar_estudiante.php';
        }

        public function actualizarEstudiante() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id               = intval($_POST['id'] ?? 0);
                $nie              = trim($_POST['nie'] ?? '');
                $nombre           = trim($_POST['nombres'] ?? '');
                $apellido         = trim($_POST['apellidos'] ?? '');
                $correo           = trim($_POST['correo'] ?? '');
                $grado            = trim($_POST['grado'] ?? '9° Grado');
                $seccion          = trim($_POST['seccion'] ?? 'Sección A');
                $encargado        = trim($_POST['encargado'] ?? '');
                $telefono         = trim($_POST['telefono'] ?? '');
                $direccion        = trim($_POST['direccion'] ?? '');
                $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
                $genero           = trim($_POST['genero'] ?? 'Masculino');

                try {
                    $sql = "UPDATE estudiantes 
                            SET nie = :nie, 
                                nombre = :nombre, 
                                apellido = :apellido, 
                                correo = :correo, 
                                grado = :grado, 
                                seccion = :seccion, 
                                encargado = :encargado, 
                                telefono = :telefono, 
                                direccion = :direccion, 
                                fecha_nacimiento = :fecha_nacimiento, 
                                genero = :genero 
                            WHERE id = :id";
                    
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':nie'              => $nie,
                        ':nombre'           => $nombre,
                        ':apellido'         => $apellido,
                        ':correo'           => $correo,
                        ':grado'            => $grado,
                        ':seccion'          => $seccion,
                        ':encargado'        => $encargado,
                        ':telefono'         => $telefono,
                        ':direccion'        => $direccion,
                        ':fecha_nacimiento' => $fecha_nacimiento,
                        ':genero'           => $genero,
                        ':id'               => $id
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=estudiante_actualizado');
                    exit;
                } catch (PDOException $e) {
                    die("Error al actualizar estudiante: " . $e->getMessage());
                }
            }
        }
    }
}