<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/config/Conexion.php')) {
    require_once __DIR__ . '/config/Conexion.php';
} else {
    require_once __DIR__ . '/config/conexion.php';
}

$c = new Conexion();
$db = $c->conectar();

echo "<h2>Estado de la Base de Datos INCA NOTES</h2>";

// 1. Conteo en tabla estudiantes
$resEst = $db->query("SELECT * FROM estudiantes");
$estudiantes = $resEst->fetchAll(PDO::FETCH_ASSOC);
echo "<p><b>Total en tabla `estudiantes`:</b> " . count($estudiantes) . "</p>";

// 2. Conteo en tabla usuarios (rol estudiante)
$resUsr = $db->query("SELECT * FROM usuarios WHERE LOWER(rol) = 'estudiante'");
$usuariosEst = $resUsr->fetchAll(PDO::FETCH_ASSOC);
echo "<p><b>Total estudiantes en tabla `usuarios`:</b> " . count($usuariosEst) . "</p>";

// 3. Sincronizar: Pasar de usuarios a estudiantes los que falten
$migrados = 0;
foreach ($usuariosEst as $u) {
    $nie = explode('@', $u['username'])[0];
    
    // Verificar si ya existe en la tabla estudiantes
    $stmtCheck = $db->prepare("SELECT id FROM estudiantes WHERE nie = :nie LIMIT 1");
    $stmtCheck->execute([':nie' => $nie]);
    
    if (!$stmtCheck->fetch()) {
        $stmtIns = $db->prepare("INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion, telefono, genero, foto) 
                                 VALUES (:nie, :nombre, :apellido, :correo, '9° Grado', 'Sección A', :tel, :gen, :foto)");
        $stmtIns->execute([
            ':nie'      => $nie,
            ':nombre'   => $u['nombre'],
            ':apellido' => $u['apellido'],
            ':correo'   => $u['correo'] ?? ($nie . '@inca.edu.sv'),
            ':tel'      => $u['telefono'] ?? null,
            ':gen'      => $u['genero'] ?? 'Masculino',
            ':foto'     => $u['foto'] ?? null
        ]);
        $migrados++;
    }
}

echo "<h3 style='color:green;'>Sincronización completada: Se añadieron $migrados estudiantes a la lista académica.</h3>";
echo "<p><a href='index.php?action=gestion_notas'>Volver a Gestión de Notas</a></p>";