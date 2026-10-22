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

            // PBI-39: Columnas para histórico de recuperación
            // PBI-15: Columnas para auditoría y trazabilidad de modificaciones/anulaciones
            try {
                if ($this->db) {
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS nota_original_reprobada DECIMAL(4,2) DEFAULT NULL");
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS nota_recuperacion DECIMAL(4,2) DEFAULT NULL");
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS es_recuperada TINYINT(1) DEFAULT 0");
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS fecha_recuperacion DATETIME DEFAULT NULL");

                    // PBI-15 Auditoría
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS modificado_por INT DEFAULT NULL");
                    $this->db->exec("ALTER TABLE notas_periodos ADD COLUMN IF NOT EXISTS fecha_modificacion DATETIME DEFAULT NULL");
                }
            } catch (PDOException $e) {}
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

                            $uClean = strtolower(explode('@', $user['username'] ?? $usuario)[0]);
                            $passLower = strtolower($password);
                            $rolUpper = strtoupper($user['rol'] ?? 'DOCENTE');
                            $esPrimerLogin = false;

                            if ($rolUpper === 'ESTUDIANTE') {
                                if ($passLower === $uClean || strpos($passLower, $uClean . '*') === 0 || $passLower === strtolower($user['username'] ?? '')) {
                                    $esPrimerLogin = true;
                                }
                            } else {
                                if ($password === '12345') {
                                    $esPrimerLogin = true;
                                }
                            }

                            if ($esPrimerLogin) {
                                $_SESSION['primer_login'] = true;
                                header('Location: index.php?action=datos_personales&primer_inicio=1');
                                exit;
                            } else {
                                unset($_SESSION['primer_login']);
                                header('Location: index.php?action=dashboard');
                                exit;
                            }

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

            if (!empty($_SESSION['primer_login'])) {
                header('Location: index.php?action=datos_personales&primer_inicio=1');
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
            $docenteInfo = null;

            if ($rol === 'DOCENTE') {
                try {
                    $stmtDoc = $this->db->prepare("SELECT materias_imparte, grados_imparte, grado_orientacion FROM usuarios WHERE id = :id LIMIT 1");
                    $stmtDoc->execute([':id' => $_SESSION['usuario_id']]);
                    $docenteInfo = $stmtDoc->fetch(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $docenteInfo = null;
                }

                $gradosAsignados = [];
                if (!empty($docenteInfo['grados_imparte'])) {
                    $gradosAsignados = array_map('trim', explode(',', $docenteInfo['grados_imparte']));
                }
                if (!empty($docenteInfo['grado_orientacion']) && $docenteInfo['grado_orientacion'] !== 'Ninguno') {
                    $gradosAsignados[] = trim($docenteInfo['grado_orientacion']);
                }
                $gradosAsignados = array_values(array_unique(array_filter($gradosAsignados)));

                try {
                    if (!empty($gradosAsignados)) {
                        $placeholders = implode(',', array_fill(0, count($gradosAsignados), '?'));
                        $sql = "SELECT id, nie, nombre, apellido, correo, grado, seccion, encargado, telefono, direccion, fecha_nacimiento, genero, foto 
                                FROM estudiantes 
                                WHERE grado IN ($placeholders) 
                                ORDER BY apellido ASC, nombre ASC";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute($gradosAsignados);
                        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $estudiantes = [];
                    }
                } catch (PDOException $e) {
                    $estudiantes = [];
                }

                require_once __DIR__ . '/../views/gestion_notas_docente.php';
            } else {
                try {
                    $stmt = $this->db->query("SELECT id, nie, nombre, apellido, correo, grado, seccion, encargado, telefono, direccion, fecha_nacimiento, genero, foto FROM estudiantes ORDER BY apellido ASC, nombre ASC");
                    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    $estudiantes = [];
                }

                require_once __DIR__ . '/../views/gestion_notas.php';
            }
        }

        public function gestionDocentes() {
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            try {
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS escalafon VARCHAR(50) DEFAULT NULL");
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS grado_orientacion VARCHAR(100) DEFAULT NULL");
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS materias_imparte TEXT DEFAULT NULL");
                $this->db->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS grados_imparte TEXT DEFAULT NULL");
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
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id                = intval($_POST['id'] ?? 0);
                $escalafon         = trim($_POST['escalafon'] ?? '');
                $grado_orientacion = trim($_POST['grado_orientacion'] ?? '');
                $materiasArray     = $_POST['materias'] ?? [];
                $materias_imparte  = implode(', ', $materiasArray);

                $gradosArray       = $_POST['grados'] ?? [];
                $grados_imparte    = implode(', ', $gradosArray);

                if ($id > 0) {
                    try {
                        $stmt = $this->db->prepare("UPDATE usuarios SET escalafon = :esc, grado_orientacion = :grado, materias_imparte = :mat, grados_imparte = :grados WHERE id = :id");
                        $stmt->execute([
                            ':esc'    => $escalafon,
                            ':grado'  => $grado_orientacion,
                            ':mat'    => $materias_imparte,
                            ':grados' => $grados_imparte,
                            ':id'     => $id
                        ]);
                    } catch (PDOException $e) {}
                }

                header('Location: index.php?action=gestion_docentes&msg=docente_actualizado');
                exit;
            }
        }

        public function usuariosLista() {
            // RESTRICCIÓN ESTRICTA: Solo Administrador puede acceder al Directorio de Usuarios
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $datosCargados = [];
            try {
                $sql = "SELECT u.*, 
                               COALESCE(e.nie, u.username) AS nie,
                               e.grado, 
                               e.seccion, 
                               e.encargado, 
                               e.dui_encargado, 
                               e.direccion AS direccion_estudiante
                        FROM usuarios u
                        LEFT JOIN estudiantes e ON (LOWER(e.nie) = LOWER(u.username) OR LOWER(e.correo) = LOWER(u.correo))
                        ORDER BY u.id DESC";
                $stmt = $this->db->query($sql);
                $datosCargados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($datosCargados)) {
                    $stmtEst = $this->db->query("SELECT id, nie AS username, nie, nombre, apellido, correo, 'ESTUDIANTE' AS rol, telefono, genero, foto, grado, seccion, encargado, dui_encargado, direccion FROM estudiantes ORDER BY id DESC");
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
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id       = intval($_POST['id'] ?? 0);
                $nombres  = trim($_POST['nombre'] ?? '');
                $apellidos= trim($_POST['apellido'] ?? '');
                $correo   = trim($_POST['correo'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $genero   = trim($_POST['genero'] ?? 'Masculino');
                $rol      = strtoupper(trim($_POST['rol'] ?? 'ESTUDIANTE'));
                $password = trim($_POST['nueva_password'] ?? '');

                $nie          = trim($_POST['nie'] ?? '');
                $grado        = trim($_POST['grado'] ?? '');
                $seccion      = trim($_POST['seccion'] ?? '');
                $encargado    = trim($_POST['encargado'] ?? '');
                $duiEncargado = trim($_POST['dui_encargado'] ?? '');
                $direccion    = trim($_POST['direccion'] ?? '');

                $duiDocente   = trim($_POST['dui'] ?? '');
                $escalafon    = trim($_POST['escalafon'] ?? '');
                $materias     = trim($_POST['materias_imparte'] ?? '');
                $orientacion  = trim($_POST['grado_orientacion'] ?? '');

                $duiAdmin     = trim($_POST['dui_admin'] ?? '');
                $cargoAdmin   = trim($_POST['cargo'] ?? '');

                $duiFinal = !empty($duiDocente) ? $duiDocente : (!empty($duiAdmin) ? $duiAdmin : null);

                if ($id > 0) {
                    try {
                        $stmtPrev = $this->db->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
                        $stmtPrev->execute([':id' => $id]);
                        $prevUser = $stmtPrev->fetch(PDO::FETCH_ASSOC);

                        $camposUpdate = [
                            'nombre = :n',
                            'apellido = :a',
                            'correo = :c',
                            'email = :c',
                            'telefono = :t',
                            'genero = :g'
                        ];
                        $params = [
                            ':n'  => $nombres,
                            ':a'  => $apellidos,
                            ':c'  => $correo,
                            ':t'  => $telefono,
                            ':g'  => $genero,
                            ':id' => $id
                        ];

                        if (!empty($duiFinal)) {
                            $camposUpdate[] = 'dui = :dui';
                            $params[':dui'] = $duiFinal;
                        }
                        if ($rol === 'DOCENTE') {
                            $camposUpdate[] = 'escalafon = :esc';
                            $camposUpdate[] = 'materias_imparte = :mat';
                            $camposUpdate[] = 'grado_orientacion = :ori';
                            $params[':esc'] = $escalafon;
                            $params[':mat'] = $materias;
                            $params[':ori'] = $orientacion;
                        }
                        if ($rol === 'ADMIN' || $rol === 'ADMINISTRADOR') {
                            $camposUpdate[] = 'cargo = :cargo';
                            $params[':cargo'] = $cargoAdmin;
                        }
                        if (!empty($password)) {
                            $camposUpdate[] = 'password = :p';
                            $params[':p'] = password_hash($password, PASSWORD_DEFAULT);
                        }

                        $sqlUp = "UPDATE usuarios SET " . implode(', ', $camposUpdate) . " WHERE id = :id";
                        $stmtUp = $this->db->prepare($sqlUp);
                        $stmtUp->execute($params);

                        if ($rol === 'ESTUDIANTE') {
                            $nieMatch = !empty($nie) ? $nie : ($prevUser['username'] ?? '');
                            $correoMatch = $prevUser['correo'] ?? $correo;

                            $stmtEstUp = $this->db->prepare("UPDATE estudiantes 
                                                             SET nombre = :n, 
                                                                 apellido = :a, 
                                                                 correo = :c, 
                                                                 telefono = :t, 
                                                                 genero = :g, 
                                                                 grado = :grado, 
                                                                 seccion = :seccion, 
                                                                 encargado = :enc, 
                                                                 dui_encargado = :dui_enc, 
                                                                 direccion = :dir 
                                                             WHERE LOWER(nie) = LOWER(:nieM) 
                                                                OR LOWER(correo) = LOWER(:corM)");
                            $stmtEstUp->execute([
                                ':n'       => $nombres,
                                ':a'       => $apellidos,
                                ':c'       => $correo,
                                ':t'       => $telefono,
                                ':g'       => $genero,
                                ':grado'   => $grado,
                                ':seccion' => $seccion,
                                ':enc'     => $encargado,
                                ':dui_enc' => $duiEncargado,
                                ':dir'     => $direccion,
                                ':nieM'    => $nieMatch,
                                ':corM'    => $correoMatch
                            ]);
                        }

                    } catch (PDOException $e) {}
                }

                header('Location: index.php?action=usuarios_lista&msg=perfil_actualizado');
                exit;
            }
        }

        public function eliminarUsuario() {
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
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
            if (!isset($_SESSION['usuario_id']) || !in_array(strtoupper($_SESSION['rol'] ?? ''), ['ADMIN', 'ADMINISTRADOR'])) {
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

                        unset($_SESSION['primer_login']);
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
                        unset($_SESSION['primer_login']);
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

        // PBI-39: GUARDADO DE NOTA DE RECUPERACIÓN CON SUSTITUCIÓN Y AUDITORÍA HISTÓRICA
        public function guardarRecuperacion() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $estudianteId     = intval($_POST['estudiante_id'] ?? 0);
                $materia          = trim(html_entity_decode($_POST['materia'] ?? '', ENT_QUOTES, 'UTF-8'));
                $periodo          = intval($_POST['periodo'] ?? 1);
                $notaRecuperacion = floatval($_POST['nota_recuperacion'] ?? 0);

                if ($estudianteId <= 0 || empty($materia)) {
                    header('Location: index.php?action=gestion_notas&error=recuperacion_invalida');
                    exit;
                }

                try {
                    $stmtCheck = $this->db->prepare("SELECT id, act1, act2, examen, nota_periodo, nota_original_reprobada 
                                                     FROM notas_periodos 
                                                     WHERE estudiante_id = :id AND materia = :m AND periodo = :p 
                                                     LIMIT 1");
                    $stmtCheck->execute([
                        ':id' => $estudianteId,
                        ':m'  => $materia,
                        ':p'  => $periodo
                    ]);
                    $regActual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if (!$regActual || floatval($regActual['nota_periodo']) >= 6.0) {
                        header('Location: index.php?action=gestion_notas&error=no_reprobado');
                        exit;
                    }

                    $notaOriginalReprobada = !empty($regActual['nota_original_reprobada']) 
                        ? floatval($regActual['nota_original_reprobada']) 
                        : floatval($regActual['nota_periodo']);

                    $notaRecuperacion = max(0.0, min(10.0, round($notaRecuperacion, 2)));

                    // PBI-15 + PBI-39: Guardar sustitución y registrar traza de auditoría
                    $sqlUp = "UPDATE notas_periodos 
                              SET nota_original_reprobada = :orig,
                                  nota_recuperacion = :rec,
                                  nota_periodo = :nueva_nota,
                                  es_recuperada = 1,
                                  fecha_recuperacion = NOW(),
                                  modificado_por = :usr,
                                  fecha_modificacion = NOW()
                              WHERE id = :id";
                    $stmtUp = $this->db->prepare($sqlUp);
                    $stmtUp->execute([
                        ':orig'       => $notaOriginalReprobada,
                        ':rec'        => $notaRecuperacion,
                        ':nueva_nota' => $notaRecuperacion,
                        ':usr'        => $_SESSION['usuario_id'] ?? null,
                        ':id'         => $regActual['id']
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=recuperacion_exitosa');
                    exit;
                } catch (PDOException $e) {
                    die("Error al procesar recuperación: " . htmlspecialchars($e->getMessage()));
                }
            }
        }

        // PBI-15: ANULACIÓN SEGURA DE REGISTRO ERRÓNEO CON TRAZA DE AUDITORÍA
        public function anularNota() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $estudianteId = intval($_POST['estudiante_id'] ?? 0);
                $materia      = trim(html_entity_decode($_POST['materia'] ?? '', ENT_QUOTES, 'UTF-8'));
                $periodo      = intval($_POST['periodo'] ?? 0);

                if ($estudianteId <= 0 || empty($materia) || $periodo <= 0) {
                    header('Location: index.php?action=gestion_notas&error=parametros_invalidos');
                    exit;
                }

                // Si es docente, comprobar que imparte esa materia
                if (strtoupper($_SESSION['rol'] ?? '') === 'DOCENTE') {
                    try {
                        $stmtDoc = $this->db->prepare("SELECT materias_imparte FROM usuarios WHERE id = :id LIMIT 1");
                        $stmtDoc->execute([':id' => $_SESSION['usuario_id']]);
                        $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);
                        if ($doc && !empty($doc['materias_imparte'])) {
                            $matsAutorizadas = array_map('trim', explode(',', $doc['materias_imparte']));
                            if (!in_array($materia, $matsAutorizadas)) {
                                header('Location: index.php?action=gestion_notas&error=materia_no_autorizada');
                                exit;
                            }
                        }
                    } catch (PDOException $eDoc) {}
                }

                try {
                    // Criterio 2 y 3 de PBI-15: Colocar en NULL (Sin calificar) y registrar la traza del responsable
                    $sqlAnular = "UPDATE notas_periodos 
                                  SET act1 = NULL, 
                                      act2 = NULL, 
                                      examen = NULL, 
                                      nota_periodo = NULL, 
                                      nota_recuperacion = NULL,
                                      es_recuperada = 0,
                                      modificado_por = :usr, 
                                      fecha_modificacion = NOW() 
                                  WHERE estudiante_id = :id AND materia = :m AND periodo = :p";
                    
                    $stmtAnular = $this->db->prepare($sqlAnular);
                    $stmtAnular->execute([
                        ':usr' => $_SESSION['usuario_id'] ?? null,
                        ':id'  => $estudianteId,
                        ':m'   => $materia,
                        ':p'   => $periodo
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=nota_anulada_exito');
                    exit;
                } catch (PDOException $e) {
                    die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>
                            <h2>Error al Anular Nota (PBI-15):</h2>
                            <p>" . htmlspecialchars($e->getMessage()) . "</p>
                            <a href='index.php?action=gestion_notas'>Volver a Gestión de Notas</a>
                         </div>");
                }
            }
        }

        // GUARDADO DE NOTAS CON PBI-12 (TRANSACCIÓN) Y PBI-15 (AUDITORÍA)
        public function guardarNota() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Si el formulario envía solicitud explícita de anulación
                if (!empty($_POST['accion_anular'])) {
                    $this->anularNota();
                    return;
                }

                $materia = trim(html_entity_decode($_POST['materia'] ?? 'Matemática', ENT_QUOTES, 'UTF-8'));
                $periodo = intval($_POST['periodo'] ?? 1);

                if (strtoupper($_SESSION['rol'] ?? '') === 'DOCENTE') {
                    try {
                        $stmtDoc = $this->db->prepare("SELECT materias_imparte FROM usuarios WHERE id = :id LIMIT 1");
                        $stmtDoc->execute([':id' => $_SESSION['usuario_id']]);
                        $doc = $stmtDoc->fetch(PDO::FETCH_ASSOC);
                        if ($doc && !empty($doc['materias_imparte'])) {
                            $matsAutorizadas = array_map('trim', explode(',', $doc['materias_imparte']));
                            if (!in_array($materia, $matsAutorizadas)) {
                                header('Location: index.php?action=gestion_notas&error=materia_no_autorizada');
                                exit;
                            }
                        }
                    } catch (PDOException $eDoc) {}
                }

                // PBI-12: Matriz masiva con transacción atómica y PBI-15: Traza de auditoría
                if (!empty($_POST['modo_matriz']) && isset($_POST['estudiante_ids']) && is_array($_POST['estudiante_ids'])) {
                    $idsArr  = $_POST['estudiante_ids'];
                    $act1Arr = $_POST['act1'] ?? [];
                    $act2Arr = $_POST['act2'] ?? [];
                    $examArr = $_POST['examen'] ?? [];

                    try {
                        $this->db->beginTransaction();

                        $sqlMatriz = "INSERT INTO notas_periodos (estudiante_id, materia, periodo, act1, act2, examen, nota_periodo, modificado_por, fecha_modificacion) 
                                      VALUES (:estudiante_id, :materia, :periodo, :act1, :act2, :examen, :nota_periodo, :usr, NOW())
                                      ON DUPLICATE KEY UPDATE 
                                      act1 = VALUES(act1), 
                                      act2 = VALUES(act2), 
                                      examen = VALUES(examen), 
                                      nota_periodo = VALUES(nota_periodo),
                                      modificado_por = VALUES(modificado_por),
                                      fecha_modificacion = NOW()";
                        $stmtM = $this->db->prepare($sqlMatriz);

                        foreach ($idsArr as $eIdRaw) {
                            $eId = intval($eIdRaw);
                            if ($eId <= 0) continue;

                            $a1 = isset($act1Arr[$eId]) && $act1Arr[$eId] !== '' ? floatval($act1Arr[$eId]) : 0.0;
                            $a2 = isset($act2Arr[$eId]) && $act2Arr[$eId] !== '' ? floatval($act2Arr[$eId]) : 0.0;
                            $ex = isset($examArr[$eId]) && $examArr[$eId] !== '' ? floatval($examArr[$eId]) : 0.0;

                            $a1 = max(0.0, min(10.0, round($a1, 2)));
                            $a2 = max(0.0, min(10.0, round($a2, 2)));
                            $ex = max(0.0, min(10.0, round($ex, 2)));

                            $notaPeriodo = round(($a1 * 0.35) + ($a2 * 0.35) + ($ex * 0.30), 2, PHP_ROUND_HALF_UP);

                            $stmtM->execute([
                                ':estudiante_id' => $eId,
                                ':materia'       => $materia,
                                ':periodo'       => $periodo,
                                ':act1'          => $a1,
                                ':act2'          => $a2,
                                ':examen'        => $ex,
                                ':nota_periodo'  => $notaPeriodo,
                                ':usr'           => $_SESSION['usuario_id'] ?? null
                            ]);
                        }

                        $this->db->commit();
                        header('Location: index.php?action=gestion_notas&msg=matriz_guardada');
                        exit;
                    } catch (PDOException $eMat) {
                        if ($this->db->inTransaction()) {
                            $this->db->rollBack();
                        }
                        die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>
                                <h2>Error en Transacción de Matriz:</h2>
                                <p>" . htmlspecialchars($eMat->getMessage()) . "</p>
                                <a href='index.php?action=gestion_notas'>Volver a Gestión de Notas</a>
                             </div>");
                    }
                }

                // Guardado individual estándar con traza de auditoría (PBI-15)
                $estudianteId = intval($_POST['estudiante_id'] ?? 0);
                $act1         = max(0.0, min(10.0, round(floatval($_POST['act1'] ?? 0), 2)));
                $act2         = max(0.0, min(10.0, round(floatval($_POST['act2'] ?? 0), 2)));
                $examen       = max(0.0, min(10.0, round(floatval($_POST['examen'] ?? 0), 2)));

                if ($estudianteId <= 0) {
                    header('Location: index.php?action=gestion_notas&error=estudiante_invalido');
                    exit;
                }

                try {
                    $stmtCheck = $this->db->prepare("SELECT id FROM estudiantes WHERE id = :id LIMIT 1");
                    $stmtCheck->execute([':id' => $estudianteId]);
                    $existeEst = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                    if (!$existeEst) {
                        $stmtUsr = $this->db->prepare("SELECT username, correo, nombre, apellido FROM usuarios WHERE id = :id LIMIT 1");
                        $stmtUsr->execute([':id' => $estudianteId]);
                        $usrInfo = $stmtUsr->fetch(PDO::FETCH_ASSOC);

                        if ($usrInfo) {
                            $nieTarget = explode('@', $usrInfo['username'])[0];
                            $stmtEstRel = $this->db->prepare("SELECT id FROM estudiantes WHERE LOWER(nie) = LOWER(:nie) OR LOWER(correo) = LOWER(:cor) LIMIT 1");
                            $stmtEstRel->execute([
                                ':nie' => $nieTarget,
                                ':cor' => $usrInfo['correo'] ?? ($nieTarget . '@inca.edu.sv')
                            ]);
                            $relacionado = $stmtEstRel->fetch(PDO::FETCH_ASSOC);

                            if ($relacionado) {
                                $estudianteId = intval($relacionado['id']);
                            } else {
                                $stmtInsertEst = $this->db->prepare("INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion) 
                                                                     VALUES (:nie, :n, :a, :c, '1° Año Bachillerato', 'Sección A')");
                                $stmtInsertEst->execute([
                                    ':nie' => $nieTarget,
                                    ':n'   => $usrInfo['nombre'] ?? 'Estudiante',
                                    ':a'   => $usrInfo['apellido'] ?? 'INCA',
                                    ':c'   => $usrInfo['correo'] ?? ($nieTarget . '@inca.edu.sv')
                                ]);
                                $estudianteId = intval($this->db->lastInsertId());
                            }
                        }
                    }

                    $notaPeriodo = round(($act1 * 0.35) + ($act2 * 0.35) + ($examen * 0.30), 2, PHP_ROUND_HALF_UP);

                    $sql = "INSERT INTO notas_periodos (estudiante_id, materia, periodo, act1, act2, examen, nota_periodo, modificado_por, fecha_modificacion) 
                            VALUES (:estudiante_id, :materia, :periodo, :act1, :act2, :examen, :nota_periodo, :usr, NOW())
                            ON DUPLICATE KEY UPDATE 
                            act1 = VALUES(act1), 
                            act2 = VALUES(act2), 
                            examen = VALUES(examen), 
                            nota_periodo = VALUES(nota_periodo),
                            modificado_por = :usr,
                            fecha_modificacion = NOW()";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        ':estudiante_id' => $estudianteId,
                        ':materia'       => $materia,
                        ':periodo'       => $periodo,
                        ':act1'          => $act1,
                        ':act2'          => $act2,
                        ':examen'        => $examen,
                        ':nota_periodo'  => $notaPeriodo,
                        ':usr'           => $_SESSION['usuario_id'] ?? null
                    ]);

                    header('Location: index.php?action=gestion_notas&msg=nota_guardada');
                    exit;
                } catch (PDOException $e) {
                    die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>
                            <h2>Error al Guardar Nota:</h2>
                            <p>" . htmlspecialchars($e->getMessage()) . "</p>
                            <a href='index.php?action=gestion_notas'>Volver a Gestión de Notas</a>
                         </div>");
                }
            }
        }

        public function boletaNotas() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $rol = strtoupper($_SESSION['rol'] ?? '');
            $estudianteId = intval($_GET['estudiante_id'] ?? $_GET['id'] ?? 0);
            $estudiante = [];

            if ($rol === 'ESTUDIANTE' || $estudianteId <= 0) {
                $usuarioSesion = trim($_SESSION['usuario'] ?? '');
                $nieSesion = explode('@', $usuarioSesion)[0];
                $correoSesion = $nieSesion . '@inca.edu.sv';

                try {
                    $stmtEstSesion = $this->db->prepare("SELECT * FROM estudiantes 
                                                         WHERE LOWER(nie) = LOWER(:nie) 
                                                            OR LOWER(correo) = LOWER(:cor) 
                                                            OR LOWER(correo) = LOWER(:usr) 
                                                         LIMIT 1");
                    $stmtEstSesion->execute([
                        ':nie' => $nieSesion,
                        ':cor' => $correoSesion,
                        ':usr' => $usuarioSesion
                    ]);
                    $estudiante = $stmtEstSesion->fetch(PDO::FETCH_ASSOC) ?: [];

                    if (!empty($estudiante['id'])) {
                        $estudianteId = intval($estudiante['id']);
                    }
                } catch (PDOException $e) {}
            }

            if (empty($estudiante) && $estudianteId > 0) {
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
                    $stmtNotas = $this->db->prepare("SELECT materia, periodo, act1, act2, examen, nota_periodo, nota_original_reprobada, nota_recuperacion, es_recuperada, modificado_por, fecha_modificacion 
                                                     FROM notas_periodos 
                                                     WHERE estudiante_id = :id 
                                                     ORDER BY materia ASC, periodo ASC");
                    $stmtNotas->execute([':id' => $estudianteId]);
                    $filas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($filas as $f) {
                        $calificaciones[] = $f;
                        $notas[] = [
                            'materia'                  => trim($f['materia']),
                            'periodo'                  => 'Periodo ' . $f['periodo'],
                            'promedio'                 => $f['nota_periodo'],
                            'nota_original_reprobada'  => $f['nota_original_reprobada'],
                            'nota_recuperacion'        => $f['nota_recuperacion'],
                            'es_recuperada'            => $f['es_recuperada'],
                            'modificado_por'           => $f['modificado_por'],
                            'fecha_modificacion'       => $f['fecha_modificacion']
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

        // =========================================================================
        // PBI-46: CUADRO DE HONOR Y RANKING AUTOMÁTICO DE RENDIMIENTO POR SECCIÓN
        // =========================================================================
        public function cuadroHonor() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $rolUsuario = strtoupper($_SESSION['rol'] ?? 'DOCENTE');

            // REGLA 1: Bloqueo de seguridad al Estudiante
            if ($rolUsuario === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $esAdmin = in_array($rolUsuario, ['ADMIN', 'ADMINISTRADOR']);

            // Catálogo de grados existentes en la BD + estándar institucional
            $gradosBase = ['7° Grado', '8° Grado', '9° Grado', '1° Año Bachillerato', '2° Año Bachillerato'];
            try {
                $stmtGr = $this->db->query("SELECT DISTINCT grado FROM estudiantes WHERE grado IS NOT NULL AND grado != '' ORDER BY grado ASC");
                $gradosDB = $stmtGr->fetchAll(PDO::FETCH_COLUMN);
                $grados = array_values(array_unique(array_merge($gradosBase, $gradosDB)));
            } catch (PDOException $e) {
                $grados = $gradosBase;
            }

            $secciones = ['A', 'B', 'C'];
            $gradoSeleccionado = $grados[0] ?? '9° Grado';
            $seccionSeleccionada = 'A';

            // REGLAS 2 y 3: Restricción según Rol (Admin ve todo, Docente solo su sección asignada)
            if (!$esAdmin) {
                try {
                    $stmtDoc = $this->db->prepare("SELECT grado_orientacion, grados_imparte FROM usuarios WHERE id = :id LIMIT 1");
                    $stmtDoc->execute([':id' => $_SESSION['usuario_id']]);
                    $docInfo = $stmtDoc->fetch(PDO::FETCH_ASSOC);

                    $orientacion = trim($docInfo['grado_orientacion'] ?? '');
                    if (!empty($orientacion) && strtolower($orientacion) !== 'ninguno') {
                        if (preg_match('/^(.*?)\s*[-–]?\s*Secci[oó]n\s*([A-C])$/i', $orientacion, $matches)) {
                            $gradoSeleccionado = trim($matches[1]);
                            $seccionSeleccionada = strtoupper(trim($matches[2]));
                        } else {
                            $gradoSeleccionado = $orientacion;
                        }
                    } elseif (!empty($docInfo['grados_imparte'])) {
                        $listaGrados = array_map('trim', explode(',', $docInfo['grados_imparte']));
                        if (!empty($listaGrados[0])) {
                            $gradoSeleccionado = $listaGrados[0];
                        }
                    }

                    $stmtSecDoc = $this->db->prepare("SELECT seccion FROM estudiantes WHERE grado = :g LIMIT 1");
                    $stmtSecDoc->execute([':g' => $gradoSeleccionado]);
                    $secEncontrada = $stmtSecDoc->fetchColumn();
                    if ($secEncontrada) {
                        $seccionSeleccionada = trim(str_ireplace(['Sección', 'Seccion'], '', $secEncontrada));
                    }
                } catch (PDOException $e) {}
            } else {
                // Administrador puede elegir cualquier grado y sección
                if (!empty($_GET['grado'])) {
                    $gradoSeleccionado = trim($_GET['grado']);
                }
                if (!empty($_GET['seccion'])) {
                    $seccionSeleccionada = trim(str_ireplace(['Sección', 'Seccion'], '', $_GET['seccion']));
                }
            }

            // Criterio 3: Alternar entre Periodo Activo (1, 2, 3, 4) y Promedio Acumulado Anual
            $periodoSeleccionado = $_GET['periodo'] ?? 'anual';

            // 1. Obtener los estudiantes de la sección (compatible con 'A' y 'Sección A')
            $estudiantes = [];
            try {
                $secCorto = trim($seccionSeleccionada);
                $secLargo = 'Sección ' . $secCorto;
                $secLargoSinTilde = 'Seccion ' . $secCorto;

                $stmtEst = $this->db->prepare("SELECT id, nie, nombre, apellido, foto 
                                               FROM estudiantes 
                                               WHERE grado = :g 
                                                 AND (UPPER(TRIM(seccion)) = UPPER(:s1) 
                                                   OR UPPER(TRIM(seccion)) = UPPER(:s2) 
                                                   OR UPPER(TRIM(seccion)) = UPPER(:s3))
                                               ORDER BY apellido ASC, nombre ASC");
                $stmtEst->execute([
                    ':g'  => $gradoSeleccionado,
                    ':s1' => $secCorto,
                    ':s2' => $secLargo,
                    ':s3' => $secLargoSinTilde
                ]);
                $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $estudiantes = [];
            }

            // REGLA 4: Promedio estrictamente GLOBAL tomando todas las materias registradas del alumno
            $rankingData = [];

            foreach ($estudiantes as $est) {
                $estId = intval($est['id']);

                try {
                    if ($periodoSeleccionado === 'anual') {
                        $stmtNotas = $this->db->prepare("SELECT materia, AVG(nota_periodo) AS prom_materia 
                                                         FROM notas_periodos 
                                                         WHERE estudiante_id = :eid 
                                                           AND nota_periodo IS NOT NULL 
                                                         GROUP BY materia");
                        $stmtNotas->execute([':eid' => $estId]);
                        $materiasEval = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                        if (count($materiasEval) > 0) {
                            $sumaMaterias = 0;
                            foreach ($materiasEval as $mRow) {
                                $sumaMaterias += floatval($mRow['prom_materia']);
                            }
                            $promedioGlobal = $sumaMaterias / count($materiasEval);

                            $rankingData[] = [
                                'id'             => $estId,
                                'nie'            => $est['nie'],
                                'nombres'        => $est['nombre'] ?? '',
                                'apellidos'      => $est['apellido'] ?? '',
                                'foto'           => $est['foto'] ?? '',
                                'promedio'       => round($promedioGlobal, 2),
                                'total_materias' => count($materiasEval)
                            ];
                        }
                    } else {
                        $pNum = intval($periodoSeleccionado);
                        $stmtNotas = $this->db->prepare("SELECT materia, nota_periodo 
                                                         FROM notas_periodos 
                                                         WHERE estudiante_id = :eid 
                                                           AND periodo = :p 
                                                           AND nota_periodo IS NOT NULL");
                        $stmtNotas->execute([
                            ':eid' => $estId,
                            ':p'   => $pNum
                        ]);
                        $materiasEval = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

                        if (count($materiasEval) > 0) {
                            $sumaMaterias = 0;
                            foreach ($materiasEval as $mRow) {
                                $sumaMaterias += floatval($mRow['nota_periodo']);
                            }
                            $promedioGlobal = $sumaMaterias / count($materiasEval);

                            $rankingData[] = [
                                'id'             => $estId,
                                'nie'            => $est['nie'],
                                'nombres'        => $est['nombre'] ?? '',
                                'apellidos'      => $est['apellido'] ?? '',
                                'foto'           => $est['foto'] ?? '',
                                'promedio'       => round($promedioGlobal, 2),
                                'total_materias' => count($materiasEval)
                            ];
                        }
                    }
                } catch (PDOException $e) {}
            }

            // Criterio 1: Ordenar descendentemente por promedio global
            usort($rankingData, function($a, $b) {
                if ($b['promedio'] == $a['promedio']) {
                    return strcmp($a['apellidos'], $b['apellidos']);
                }
                return ($b['promedio'] > $a['promedio']) ? 1 : -1;
            });

            require_once __DIR__ . '/../views/cuadro_honor.php';
        }
    }
}