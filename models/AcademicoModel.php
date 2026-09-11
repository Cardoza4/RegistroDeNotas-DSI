<?php
class AcademicoModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerGrados() {
        $stmt = $this->conn->prepare("SELECT DISTINCT grado FROM estudiantes WHERE grado IS NOT NULL AND grado != '' ORDER BY grado");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerSecciones() {
        $stmt = $this->conn->prepare("SELECT DISTINCT seccion FROM estudiantes WHERE seccion IS NOT NULL AND seccion != '' ORDER BY seccion");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMaterias() {
        $stmt = $this->conn->prepare("SELECT * FROM materias ORDER BY 1 ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEstudiantesPorFiltro($grado = null, $seccion = null) {
        $sql = "SELECT * FROM estudiantes WHERE 1=1";
        $params = [];

        if (!empty($grado) && $grado !== '%') {
            $sql .= " AND grado = :grado";
            $params[':grado'] = $grado;
        }
        if (!empty($seccion) && $seccion !== '%') {
            $sql .= " AND seccion = :seccion";
            $params[':seccion'] = $seccion;
        }

        $sql .= " ORDER BY apellido ASC, nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarEstudianteCompleto($nie, $nombre, $apellido, $correo, $grado, $seccion) {
        try {
            $this->conn->beginTransaction();

            $passHash = password_hash($nie, PASSWORD_BCRYPT);
            $queryUser = "INSERT INTO usuarios (username, password, rol, nombre, apellido) 
                          VALUES (:username, :password, 'estudiante', :nombre, :apellido)
                          ON DUPLICATE KEY UPDATE nombre = :nombre_u, apellido = :apellido_u";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->execute([
                ':username'   => $nie,
                ':password'   => $passHash,
                ':nombre'     => $nombre,
                ':apellido'   => $apellido,
                ':nombre_u'   => $nombre,
                ':apellido_u' => $apellido
            ]);

            $queryEst = "INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion) 
                         VALUES (:nie, :nombre, :apellido, :correo, :grado, :seccion)
                         ON DUPLICATE KEY UPDATE 
                         nombre = :nombre_e, apellido = :apellido_e, correo = :correo_e, grado = :grado_e, seccion = :seccion_e";
            $stmtEst = $this->conn->prepare($queryEst);
            $stmtEst->execute([
                ':nie'        => $nie,
                ':nombre'     => $nombre,
                ':apellido'   => $apellido,
                ':correo'     => $correo,
                ':grado'      => $grado,
                ':seccion'    => $seccion,
                ':nombre_e'   => $nombre,
                ':apellido_e' => $apellido,
                ':correo_e'   => $correo,
                ':grado_e'    => $grado,
                ':seccion_e'  => $seccion
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function guardarCalificacion($estudiante_id, $materia_id, $periodo, $act1, $act2, $examen) {
        $promedio = ($act1 * 0.35) + ($act2 * 0.35) + ($examen * 0.30);

        $query = "INSERT INTO notas (estudiante_id, materia_id, periodo, act1, act2, examen, promedio)
                  VALUES (:estudiante_id, :materia_id, :periodo, :act1, :act2, :examen, :promedio)
                  ON DUPLICATE KEY UPDATE 
                  act1 = :act1_u, act2 = :act2_u, examen = :examen_u, promedio = :promedio_u";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':estudiante_id' => $estudiante_id,
            ':materia_id'    => $materia_id,
            ':periodo'       => $periodo,
            ':act1'          => $act1,
            ':act2'          => $act2,
            ':examen'        => $examen,
            ':promedio'      => $promedio,
            ':act1_u'        => $act1,
            ':act2_u'        => $act2,
            ':examen_u'      => $examen,
            ':promedio_u'    => $promedio
        ]);
    }

    public function obtenerBoletaPorNIE($nie) {
        $query = "SELECT e.nombre, e.apellido, e.nie, e.grado, e.seccion, 
                         m.nombre_materia AS materia, 
                         n.periodo, n.act1, n.act2, n.examen, n.promedio
                  FROM estudiantes e
                  JOIN notas n ON e.id = n.estudiante_id
                  JOIN materias m ON n.materia_id = m.id
                  WHERE e.nie = :nie
                  ORDER BY n.periodo ASC, m.nombre_materia ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nie', $nie);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDocentes() {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE rol = 'docente' ORDER BY apellido ASC, nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function actualizarEstudiante($id, $nombre, $apellido, $correo, $grado, $seccion) {
        $stmt = $this->conn->prepare("UPDATE estudiantes SET nombre = :nombre, apellido = :apellido, correo = :correo, grado = :grado, seccion = :seccion WHERE id = :id");
        return $stmt->execute([
            ':id'       => $id,
            ':nombre'   => $nombre,
            ':apellido' => $apellido,
            ':correo'   => $correo,
            ':grado'    => $grado,
            ':seccion'  => $seccion
        ]);
    }

    public function eliminarEstudiante($id, $nie = null) {
        try {
            $this->conn->beginTransaction();

            // 1. Eliminar primero las notas registradas para no violar claves foráneas
            $stmtNotas = $this->conn->prepare("DELETE FROM notas WHERE estudiante_id = :id");
            $stmtNotas->execute([':id' => $id]);

            // 2. Eliminar expediente del estudiante
            $stmtEst = $this->conn->prepare("DELETE FROM estudiantes WHERE id = :id");
            $stmtEst->execute([':id' => $id]);

            // 3. Eliminar usuario asociado si cuenta con NIE
            if (!empty($nie)) {
                $stmtUser = $this->conn->prepare("DELETE FROM usuarios WHERE username = :nie AND rol = 'estudiante'");
                $stmtUser->execute([':nie' => $nie]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function eliminarUsuario($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
    public function registrarDocente($username, $nombre, $apellido, $password) {
        try {
            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO usuarios (username, password, rol, nombre, apellido) 
                      VALUES (:username, :password, 'docente', :nombre, :apellido)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':username' => $username,
                ':password' => $passHash,
                ':nombre'   => $nombre,
                ':apellido' => $apellido
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>