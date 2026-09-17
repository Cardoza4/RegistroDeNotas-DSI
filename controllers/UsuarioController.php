<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carga compatible de Conexion y Modelo
if (file_exists(__DIR__ . '/../config/Conexion.php')) {
    require_once __DIR__ . '/../config/Conexion.php';
} elseif (file_exists(__DIR__ . '/../config/conexion.php')) {
    require_once __DIR__ . '/../config/conexion.php';
}

if (file_exists(__DIR__ . '/../models/UsuarioModel.php')) {
    require_once __DIR__ . '/../models/UsuarioModel.php';
} elseif (file_exists(__DIR__ . '/../models/usuariomodel.php')) {
    require_once __DIR__ . '/../models/usuariomodel.php';
}

if (!class_exists('UsuarioController')) {

    class UsuarioController {
        private $db;
        private $usuarioModel;

        public function __construct() {
            if (class_exists('Conexion')) {
                $conexionObj = new Conexion();
                $this->db = $conexionObj->conectar();
            } else {
                global $pdo;
                $this->db = $pdo;
            }

            if (class_exists('UsuarioModel')) {
                $this->usuarioModel = new UsuarioModel($this->db);
            }
        }

        public function login() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $identificador = trim($_POST['usuario'] ?? $_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? $_POST['clave'] ?? '');

                if (empty($identificador) || empty($password)) {
                    header('Location: index.php?action=login&error=campos_vacios');
                    exit;
                }

                $user = null;
                try {
                    // Consulta insensible a mayúsculas y minúsculas (LOWER)
                    $sql = "SELECT * FROM usuarios 
                            WHERE LOWER(username) = LOWER(:u) 
                               OR LOWER(correo) = LOWER(:u) 
                            LIMIT 1";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':u' => $identificador]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Si escribió solo el NIE sin @inca.edu.sv o viceversa
                    if (!$user) {
                        $niePuro = explode('@', $identificador)[0];
                        $correoAlt = $niePuro . '@inca.edu.sv';

                        $stmt->execute([':u' => $niePuro]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);

                        if (!$user) {
                            $stmt->execute([':u' => $correoAlt]);
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                } catch (PDOException $e) {
                    if ($this->usuarioModel && method_exists($this->usuarioModel, 'obtenerPorUsuario')) {
                        $user = $this->usuarioModel->obtenerPorUsuario($identificador);
                    }
                }

                if ($user) {
                    $dbPass = $user['password'] ?? $user['clave'] ?? '';

                    $valido = password_verify($password, $dbPass) 
                              || ($password === $dbPass) 
                              || (md5($password) === $dbPass);

                    if ($valido) {
                        $_SESSION['usuario_id']     = $user['id'];
                        $_SESSION['usuario']        = $user['username'] ?? $identificador;
                        $_SESSION['username']       = $_SESSION['usuario'];
                        $_SESSION['usuario_nombre'] = trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''));
                        $_SESSION['nombre']         = $user['nombre'] ?? '';
                        $_SESSION['apellido']       = $user['apellido'] ?? '';
                        $_SESSION['rol']            = strtoupper($user['rol'] ?? 'ESTUDIANTE');
                        $_SESSION['usuario_foto']   = $user['foto'] ?? null;

                        header('Location: index.php?action=dashboard');
                        exit;
                    }
                }

                header('Location: index.php?action=login&error=credenciales_invalidas');
                exit;
            } else {
                if (file_exists(__DIR__ . '/../views/login.php')) {
                    require_once __DIR__ . '/../views/login.php';
                } elseif (file_exists(__DIR__ . '/../views/Login.php')) {
                    require_once __DIR__ . '/../views/Login.php';
                }
            }
        }

        public function actualizarUsuarioDirectorio() {
            if (!isset($_SESSION['usuario_id']) || (strtoupper($_SESSION['rol'] ?? '') !== 'ADMIN' && strtoupper($_SESSION['rol'] ?? '') !== 'ADMINISTRADOR')) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id       = $_POST['id'] ?? '';
                $nombre   = trim($_POST['nombre'] ?? '');
                $apellido = trim($_POST['apellido'] ?? '');
                $telefono = trim($_POST['telefono'] ?? '');
                $genero   = trim($_POST['genero'] ?? 'Masculino');
                $password = trim($_POST['password'] ?? '');

                try {
                    $hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

                    if (strpos($id, 'est_') === 0) {
                        $idReal = str_replace('est_', '', $id);

                        // 1. Actualizar datos en estudiantes
                        $sql = "UPDATE estudiantes SET nombre = :n, apellido = :a, telefono = :t, genero = :g WHERE id = :id";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            ':n' => $nombre,
                            ':a' => $apellido,
                            ':t' => $telefono,
                            ':g' => $genero,
                            ':id' => $idReal
                        ]);

                        // Obtener el NIE del estudiante para actualizar o sincronizar su password en usuarios
                        $stmtNie = $this->db->prepare("SELECT nie, correo FROM estudiantes WHERE id = :id LIMIT 1");
                        $stmtNie->execute([':id' => $idReal]);
                        $estInfo = $stmtNie->fetch(PDO::FETCH_ASSOC);

                        if ($estInfo && !empty($password)) {
                            $nieTarget = $estInfo['nie'];
                            $correoTarget = $estInfo['correo'] ?? ($nieTarget . '@inca.edu.sv');

                            // Actualizar contraseña en tabla usuarios vinculando por NIE o Correo
                            $sqlUpUsr = "UPDATE usuarios SET password = :p, nombre = :n, apellido = :a 
                                         WHERE LOWER(username) = LOWER(:nie) OR LOWER(correo) = LOWER(:cor)";
                            $stmtUpUsr = $this->db->prepare($sqlUpUsr);
                            $stmtUpUsr->execute([
                                ':p'   => $hash,
                                ':n'   => $nombre,
                                ':a'   => $apellido,
                                ':nie' => $nieTarget,
                                ':cor' => $correoTarget
                            ]);

                            // Si no existía aún en usuarios, insertarlo para permitir acceso
                            if ($stmtUpUsr->rowCount() === 0) {
                                $stmtIns = $this->db->prepare("INSERT INTO usuarios (username, correo, password, nombre, apellido, rol, telefono, genero) 
                                                               VALUES (:u, :c, :p, :n, :a, 'estudiante', :t, :g)");
                                $stmtIns->execute([
                                    ':u' => $nieTarget,
                                    ':c' => $correoTarget,
                                    ':p' => $hash,
                                    ':n' => $nombre,
                                    ':a' => $apellido,
                                    ':t' => $telefono,
                                    ':g' => $genero
                                ]);
                            }
                        }
                    } else {
                        // Usuario directo de la tabla usuarios
                        if (!empty($password)) {
                            $sql = "UPDATE usuarios SET nombre = :n, apellido = :a, telefono = :t, genero = :g, password = :p WHERE id = :id";
                            $stmt = $this->db->prepare($sql);
                            $stmt->execute([
                                ':n'  => $nombre,
                                ':a'  => $apellido,
                                ':t'  => $telefono,
                                ':g'  => $genero,
                                ':p'  => $hash,
                                ':id' => $id
                            ]);
                        } else {
                            $sql = "UPDATE usuarios SET nombre = :n, apellido = :a, telefono = :t, genero = :g WHERE id = :id";
                            $stmt = $this->db->prepare($sql);
                            $stmt->execute([
                                ':n'  => $nombre,
                                ':a'  => $apellido,
                                ':t'  => $telefono,
                                ':g'  => $genero,
                                ':id' => $id
                            ]);
                        }
                    }

                    header('Location: index.php?action=usuarios_lista&msg=actualizado');
                    exit;
                } catch (PDOException $e) {
                    die("Error al actualizar usuario: " . $e->getMessage());
                }
            }
        }

        public function guardarUsuario() {
            if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $rol               = strtolower(trim($_POST['rol'] ?? 'estudiante'));
                $nombre            = trim($_POST['nombre'] ?? '');
                $apellido          = trim($_POST['apellido'] ?? '');
                $nie               = trim($_POST['nie'] ?? '');
                $dui               = trim($_POST['dui'] ?? '');
                $escalafon         = strtoupper(trim($_POST['escalafon'] ?? ''));
                $telefono          = trim($_POST['telefono'] ?? '');
                $genero            = trim($_POST['genero'] ?? 'Masculino');
                $cargo             = trim($_POST['cargo'] ?? 'Administrador');
                $gradoOrientacion  = trim($_POST['grado_orientacion'] ?? 'Ninguno');
                $materiasImparteArr = $_POST['materias_imparte'] ?? [];
                $fotoNombre        = null;

                if (!empty($telefono) && !preg_match('/^\d{4}-\d{4}$/', $telefono)) {
                    header('Location: index.php?action=registro&error=formato_invalido');
                    exit;
                }

                if ($rol === 'admin' || $rol === 'docente') {
                    if (!preg_match('/^\d{8}-\d{1}$/', $dui)) {
                        header('Location: index.php?action=registro&error=formato_invalido');
                        exit;
                    }
                }

                $materiasImparte = null;
                if ($rol === 'docente') {
                    if (!preg_match('/^ESC-\d{1,5}$/', $escalafon)) {
                        header('Location: index.php?action=registro&error=formato_invalido');
                        exit;
                    }
                    if (count($materiasImparteArr) > 2) {
                        header('Location: index.php?action=registro&error=materias_excedidas');
                        exit;
                    }
                    $materiasImparte = !empty($materiasImparteArr) ? implode(', ', $materiasImparteArr) : null;
                } else {
                    $escalafon = null;
                    $gradoOrientacion = null;
                }

                if ($rol !== 'admin') {
                    $cargo = null;
                }

                $limpiar = function($str) {
                    $str = strtolower(trim($str));
                    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
                    return preg_replace('/[^a-z0-9]/', '', $str);
                };

                if ($rol === 'estudiante') {
                    if (empty($nie)) {
                        header('Location: index.php?action=registro&error=campos_vacios');
                        exit;
                    }
                    $usuarioCorreo = $nie . '@inca.edu.sv';
                    $passwordPlana = $nie;
                    $dui = null;
                } else {
                    $pNombre = $limpiar(explode(' ', $nombre)[0] ?? '');
                    $pApellido = $limpiar(explode(' ', $apellido)[0] ?? '');

                    if (empty($pNombre) || empty($pApellido)) {
                        header('Location: index.php?action=registro&error=campos_vacios');
                        exit;
                    }

                    $usuarioCorreo = $pNombre . '_' . $pApellido . '@inca.edu.sv';
                    $passwordPlana = '12345';
                }

                $stmtCheck = $this->db->prepare("SELECT id FROM usuarios WHERE LOWER(username) = LOWER(:u) OR LOWER(correo) = LOWER(:u) LIMIT 1");
                $stmtCheck->execute([':u' => $usuarioCorreo]);
                if ($stmtCheck->fetch()) {
                    header('Location: index.php?action=registro&error=duplicado');
                    exit;
                }

                $hash = password_hash($passwordPlana, PASSWORD_DEFAULT);

                $sql = "INSERT INTO usuarios (username, correo, password, nombre, apellido, dui, escalafon, telefono, genero, foto, rol, cargo, materias_imparte, grado_orientacion) 
                        VALUES (:u, :c, :p, :n, :a, :dui, :esc, :tel, :gen, :f, :rol, :cargo, :mat, :ori)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':u'     => $usuarioCorreo,
                    ':c'     => $usuarioCorreo,
                    ':p'     => $hash,
                    ':n'     => $nombre,
                    ':a'     => $apellido,
                    ':dui'   => $dui,
                    ':esc'   => $escalafon,
                    ':tel'   => $telefono,
                    ':gen'   => $genero,
                    ':f'     => $fotoNombre,
                    ':rol'   => $rol,
                    ':cargo' => $cargo,
                    ':mat'   => $materiasImparte,
                    ':ori'   => $gradoOrientacion
                ]);

                if ($rol === 'estudiante') {
                    try {
                        $sqlEst = "INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion) 
                                   VALUES (:nie, :n, :a, :c, '9° Grado', 'Sección A')
                                   ON DUPLICATE KEY UPDATE 
                                   nombre = VALUES(nombre), apellido = VALUES(apellido)";
                        $stmtEst = $this->db->prepare($sqlEst);
                        $stmtEst->execute([
                            ':nie' => $nie,
                            ':n'   => $nombre,
                            ':a'   => $apellido,
                            ':c'   => $usuarioCorreo
                        ]);
                    } catch (PDOException $eEst) {}
                }

                header('Location: index.php?action=registro&msg=creado');
                exit;
            }
        }

        public function listarUsuarios() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if (strtoupper($_SESSION['rol'] ?? '') !== 'ADMIN' && strtoupper($_SESSION['rol'] ?? '') !== 'ADMINISTRADOR') {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $usuarios = [];
            try {
                $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY id DESC");
                $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {}

            try {
                $stmtEst = $this->db->query("SELECT * FROM estudiantes");
                $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

                $correosRegistrados = array_map(function($u) {
                    return strtolower($u['correo'] ?? $u['username'] ?? '');
                }, $usuarios);

                foreach ($estudiantes as $est) {
                    $correoEst = strtolower($est['correo'] ?? ($est['nie'] . '@inca.edu.sv'));
                    if (!in_array($correoEst, $correosRegistrados)) {
                        $usuarios[] = [
                            'id'                => 'est_' . $est['id'],
                            'username'          => $correoEst,
                            'correo'            => $correoEst,
                            'nombre'            => $est['nombre'] ?? '',
                            'apellido'          => $est['apellido'] ?? '',
                            'nie'               => $est['nie'] ?? '',
                            'dui'               => null,
                            'escalafon'         => null,
                            'telefono'          => $est['telefono'] ?? '',
                            'genero'            => $est['genero'] ?? 'Masculino',
                            'foto'              => $est['foto'] ?? null,
                            'rol'               => 'estudiante',
                            'cargo'             => null,
                            'materias_imparte'  => null,
                            'grado_orientacion' => null
                        ];
                    }
                }
            } catch (PDOException $eEst) {}

            require_once __DIR__ . '/../views/usuarios_lista.php';
        }

        public function eliminarUsuario() {
            if (!isset($_SESSION['usuario_id']) || (strtoupper($_SESSION['rol'] ?? '') !== 'ADMIN' && strtoupper($_SESSION['rol'] ?? '') !== 'ADMINISTRADOR')) {
                header('Location: index.php?action=dashboard');
                exit;
            }

            $id = $_GET['id'] ?? '';
            if ($id == $_SESSION['usuario_id']) {
                header('Location: index.php?action=usuarios_lista&error=autoeliminacion');
                exit;
            }

            try {
                if (strpos($id, 'est_') === 0) {
                    $idReal = str_replace('est_', '', $id);
                    $stmt = $this->db->prepare("DELETE FROM estudiantes WHERE id = :id");
                    $stmt->execute([':id' => $idReal]);
                } else {
                    $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                }
                header('Location: index.php?action=usuarios_lista&msg=eliminado');
                exit;
            } catch (PDOException $e) {
                die("Error al eliminar usuario: " . $e->getMessage());
            }
        }

        public function mostrarPerfil() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            $datosUsuario = null;
            if ($this->usuarioModel && method_exists($this->usuarioModel, 'obtenerPorId')) {
                $datosUsuario = $this->usuarioModel->obtenerPorId($_SESSION['usuario_id']);
            }
            require_once __DIR__ . '/../views/perfil.php';
        }

        public function actualizarPerfil() {
            if (!isset($_SESSION['usuario_id'])) {
                header('Location: index.php?action=login');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id            = $_SESSION['usuario_id'];
                $telefono      = trim($_POST['telefono'] ?? '');
                $direccion     = trim($_POST['direccion'] ?? '');
                $nuevaPass     = trim($_POST['nueva_password'] ?? '');
                $confirmarPass = trim($_POST['confirmar_password'] ?? '');

                if (!empty($nuevaPass)) {
                    if ($nuevaPass !== $confirmarPass) {
                        header('Location: index.php?action=perfil&error=password_mismatch');
                        exit;
                    }
                    $hash = password_hash($nuevaPass, PASSWORD_DEFAULT);
                    $stmtP = $this->db->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
                    $stmtP->execute([':p' => $hash, ':id' => $id]);
                }

                if ($this->usuarioModel && method_exists($this->usuarioModel, 'actualizarDatosPersonales')) {
                    $this->usuarioModel->actualizarDatosPersonales($id, $telefono, $direccion, null);
                }
                header('Location: index.php?action=perfil&msg=exito');
                exit;
            }
        }

        public function logout() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy();
            header('Location: index.php?action=login');
            exit;
        }
    }

}