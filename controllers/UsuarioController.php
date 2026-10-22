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

if (!class_exists('UsuarioController')) {

    class UsuarioController {
        private $db;

        public function __construct() {
            if (class_exists('Conexion')) {
                $conexionObj = new Conexion();
                $this->db = $conexionObj->conectar();
            } else {
                global $pdo, $conexion;
                $this->db = $pdo ?? $conexion ?? null;
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
                    $sql = "SELECT * FROM usuarios 
                            WHERE LOWER(username) = LOWER(:u) 
                               OR LOWER(correo) = LOWER(:u) 
                               OR LOWER(email) = LOWER(:u)
                            LIMIT 1";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':u' => $identificador]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
                    $user = null;
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

                        $uClean = strtolower(explode('@', $user['username'] ?? $identificador)[0]);
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
                        }

                        unset($_SESSION['primer_login']);
                        header('Location: index.php?action=dashboard');
                        exit;
                    }
                }

                header('Location: index.php?action=login&error=credenciales_invalidas');
                exit;
            } else {
                if (file_exists(__DIR__ . '/../views/login.php')) {
                    require_once __DIR__ . '/../views/login.php';
                }
            }
        }

        public function guardarUsuario() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=registro');
                exit;
            }

            $rol = strtolower(trim($_POST['rol'] ?? 'estudiante'));

            $primerNombre   = trim($_POST['primer_nombre'] ?? '');
            $segundoNombre  = trim($_POST['segundo_nombre'] ?? '');
            $primerApellido = trim($_POST['primer_apellido'] ?? '');
            $segundoApellido= trim($_POST['segundo_apellido'] ?? '');

            if (empty($primerNombre) && !empty($_POST['nombre'])) {
                $partesNom = explode(' ', trim($_POST['nombre']));
                $primerNombre = $partesNom[0] ?? '';
                $segundoNombre = $partesNom[1] ?? '';
            }
            if (empty($primerApellido) && !empty($_POST['apellido'])) {
                $partesApe = explode(' ', trim($_POST['apellido']));
                $primerApellido = $partesApe[0] ?? '';
                $segundoApellido = $partesApe[1] ?? '';
            }

            $nombreCompleto   = trim($primerNombre . ' ' . $segundoNombre);
            $apellidoCompleto = trim($primerApellido . ' ' . $segundoApellido);

            $dui                = trim($_POST['dui'] ?? '');
            $escalafon          = strtoupper(trim($_POST['escalafon'] ?? ''));
            $telefono           = trim($_POST['telefono'] ?? '');
            $genero             = trim($_POST['genero'] ?? 'Masculino');
            $cargo              = trim($_POST['cargo'] ?? 'Administrador');
            $gradoOrientacion   = trim($_POST['grado_orientacion'] ?? 'Ninguno');
            $materiasImparteArr = $_POST['materias_imparte'] ?? [];
            $fechaNacimiento    = trim($_POST['fecha_nacimiento'] ?? '');
            $encargado          = trim($_POST['encargado'] ?? '');
            $duiEncargado       = trim($_POST['dui_encargado'] ?? '');
            $direccion          = trim($_POST['direccion'] ?? '');

            $gradoEstudiante    = trim($_POST['grado'] ?? '1° Año Bachillerato');
            $seccionEstudiante  = trim($_POST['seccion'] ?? 'Sección A');

            if (empty($telefono)) $telefono = null;
            if (empty($dui)) $dui = null;

            $materiasImparte = null;
            if ($rol === 'docente') {
                $materiasImparte = !empty($materiasImparteArr) ? implode(', ', $materiasImparteArr) : null;
            } else {
                $escalafon = null;
                $gradoOrientacion = null;
            }

            if ($rol !== 'admin' && $rol !== 'administrador') {
                $cargo = null;
            }

            $limpiar = function($str) {
                $str = strtolower(trim($str));
                $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
                return preg_replace('/[^a-z0-9]/', '', $str);
            };

            if (empty($primerNombre) || empty($primerApellido)) {
                header('Location: index.php?action=registro&error=campos_vacios');
                exit;
            }

            if ($rol === 'estudiante') {
                $ini1 = strtoupper(substr($limpiar($primerApellido), 0, 1)) ?: 'A';
                $ini2 = !empty($segundoApellido) ? (strtoupper(substr($limpiar($segundoApellido), 0, 1)) ?: 'X') : (strtoupper(substr($limpiar($primerApellido), 1, 1)) ?: 'X');
                $prefijo = $ini1 . $ini2;

                try {
                    $stmtNies = $this->db->prepare("SELECT nie FROM estudiantes WHERE nie LIKE :p UNION SELECT username AS nie FROM usuarios WHERE username LIKE :p");
                    $stmtNies->execute([':p' => $prefijo . '%']);
                    $existentes = $stmtNies->fetchAll(PDO::FETCH_COLUMN);

                    $maxNum = 0;
                    foreach ($existentes as $eNie) {
                        $numStr = substr(trim($eNie), 2);
                        if (is_numeric($numStr)) {
                            $n = intval($numStr);
                            if ($n > $maxNum) $maxNum = $n;
                        }
                    }

                    $siguienteCorrelativo = $maxNum + 1;
                    $nieGenerado = $prefijo . str_pad($siguienteCorrelativo, 5, '0', STR_PAD_LEFT);

                    while (in_array($nieGenerado, $existentes)) {
                        $siguienteCorrelativo++;
                        $nieGenerado = $prefijo . str_pad($siguienteCorrelativo, 5, '0', STR_PAD_LEFT);
                    }
                } catch (Exception $eNie) {
                    $nieGenerado = $prefijo . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                }

                $usernameFinal = $nieGenerado;
                $usuarioCorreo = $nieGenerado . '@inca.edu.sv';

                $variableSufijo = '2026';
                if (!empty($fechaNacimiento) && strpos($fechaNacimiento, '-') !== false) {
                    $partesFecha = explode('-', $fechaNacimiento);
                    if (count($partesFecha) === 3 && strlen($partesFecha[1]) === 2 && strlen($partesFecha[2]) === 2) {
                        $variableSufijo = $partesFecha[2] . $partesFecha[1];
                    }
                }
                $passwordPlana = $nieGenerado . '*' . $variableSufijo;
                $dui = null;
            } else {
                $pNombre = $limpiar($primerNombre);
                $pApellido = $limpiar($primerApellido);

                $usernameFinal = $pNombre . '_' . $pApellido . '@inca.edu.sv';
                $usuarioCorreo = $usernameFinal;
                $passwordPlana = '12345';
                $nieGenerado = null;
            }

            try {
                if (!$this->db) {
                    header('Location: index.php?action=registro&error=fallo_sql');
                    exit;
                }

                $stmtCheck = $this->db->prepare("SELECT id FROM usuarios 
                                                 WHERE LOWER(username) = LOWER(:u) 
                                                    OR LOWER(correo) = LOWER(:c) 
                                                    OR LOWER(email) = LOWER(:c) 
                                                 LIMIT 1");
                $stmtCheck->execute([
                    ':u' => $usernameFinal,
                    ':c' => $usuarioCorreo
                ]);

                if ($stmtCheck->fetch()) {
                    header('Location: index.php?action=registro&error=duplicado');
                    exit;
                }

                $hash = password_hash($passwordPlana, PASSWORD_DEFAULT);

                // Insertar en tabla usuarios
                $sql = "INSERT INTO usuarios (username, correo, email, password, nombre, apellido, dui, escalafon, telefono, genero, foto, rol, cargo, materias_imparte, grado_orientacion) 
                        VALUES (:u, :c, :email, :p, :n, :a, :dui, :esc, :tel, :gen, NULL, :rol, :cargo, :mat, :ori)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':u'     => $usernameFinal,
                    ':c'     => $usuarioCorreo,
                    ':email' => $usuarioCorreo,
                    ':p'     => $hash,
                    ':n'     => $nombreCompleto,
                    ':a'     => $apellidoCompleto,
                    ':dui'   => $dui,
                    ':esc'   => !empty($escalafon) ? $escalafon : null,
                    ':tel'   => $telefono,
                    ':gen'   => $genero,
                    ':rol'   => $rol,
                    ':cargo' => $cargo,
                    ':mat'   => $materiasImparte,
                    ':ori'   => $gradoOrientacion
                ]);

                // Sincronizar en tabla estudiantes con expediente completo de matrícula
                if ($rol === 'estudiante') {
                    try {
                        $sqlEst = "INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion, encargado, dui_encargado, telefono, direccion, fecha_nacimiento, genero) 
                                   VALUES (:nie, :n, :a, :c, :grado, :seccion, :encargado, :dui_enc, :tel, :dir, :fecha_nac, :gen)
                                   ON DUPLICATE KEY UPDATE 
                                   nombre = VALUES(nombre), 
                                   apellido = VALUES(apellido),
                                   grado = VALUES(grado),
                                   seccion = VALUES(seccion),
                                   encargado = VALUES(encargado),
                                   dui_encargado = VALUES(dui_encargado),
                                   telefono = VALUES(telefono),
                                   direccion = VALUES(direccion),
                                   fecha_nacimiento = VALUES(fecha_nacimiento),
                                   genero = VALUES(genero)";
                        $stmtEst = $this->db->prepare($sqlEst);
                        $stmtEst->execute([
                            ':nie'       => $nieGenerado,
                            ':n'         => $nombreCompleto,
                            ':a'         => $apellidoCompleto,
                            ':c'         => $usuarioCorreo,
                            ':grado'     => $gradoEstudiante,
                            ':seccion'   => $seccionEstudiante,
                            ':encargado' => !empty($encargado) ? $encargado : null,
                            ':dui_enc'   => !empty($duiEncargado) ? $duiEncargado : null,
                            ':tel'       => $telefono,
                            ':dir'       => !empty($direccion) ? $direccion : null,
                            ':fecha_nac' => !empty($fechaNacimiento) ? $fechaNacimiento : null,
                            ':gen'       => $genero
                        ]);
                    } catch (PDOException $eEst) {}
                }

                header('Location: index.php?action=registro&msg=creado');
                exit;

            } catch (PDOException $e) {
                die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif;'>
                        <h3>Error al registrar usuario:</h3>
                        <p>" . htmlspecialchars($e->getMessage()) . "</p>
                        <a href='index.php?action=registro'>Volver al registro</a>
                     </div>");
            }
        }

        public function logout() {
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