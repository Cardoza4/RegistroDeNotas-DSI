<?php
// models/AcademicoModel.php
class AcademicoModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // NUEVO: Obtener todos los docentes para el panel del Administrador
    public function obtenerTodosDocentes() {
        $query = "SELECT id, username, nombre, apellido, rol FROM usuarios WHERE rol = 'docente' ORDER BY apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener la lista de las materias oficiales
    public function obtenerMaterias() {
        $query = "SELECT * FROM materias";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estudiantes filtrados opcionalmente por Grado y Sección
    public function obtenerTodosEstudiantes($grado = null, $seccion = null) {
        $query = "SELECT * FROM estudiantes WHERE 1=1";
        
        if ($grado) { $query .= " AND grado = :grado"; }
        if ($seccion) { $query .= " AND seccion = :seccion"; }
        
        $query .= " ORDER BY grado ASC, seccion ASC, apellido ASC";
        $stmt = $this->conn->prepare($query);
        
        if ($grado) { $stmt->bindParam(':grado', $grado); }
        if ($seccion) { $stmt->bindParam(':seccion', $seccion); }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar alumnos por coincidencia de texto
    public function buscarEstudiantes($termino) {
        $query = "SELECT * FROM estudiantes WHERE nombre LIKE :termino OR apellido LIKE :termino OR nie LIKE :termino ORDER BY grado ASC, apellido ASC";
        $stmt = $this->conn->prepare($query);
        $likeTermino = "%" . $termino . "%";
        $stmt->bindParam(':termino', $likeTermino);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insertar o actualizar notas por materia para un alumno
    public function guardarNotasMateria($estudiante_id, $materia_id, $act1, $act2, $examen) {
        $checkQuery = "SELECT id FROM notas WHERE estudiante_id = :estudiante_id AND materia_id = :materia_id";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':estudiante_id', $estudiante_id);
        $checkStmt->bindParam(':materia_id', $materia_id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $query = "UPDATE notas SET actividad1 = :act1, actividad2 = :act2, examen_final = :examen 
                      WHERE estudiante_id = :estudiante_id AND materia_id = :materia_id";
        } else {
            $query = "INSERT INTO notas (estudiante_id, materia_id, actividad1, actividad2, examen_final) 
                      VALUES (:estudiante_id, :materia_id, :act1, :act2, :examen)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':materia_id', $materia_id);
        $stmt->bindParam(':act1', $act1);
        $stmt->bindParam(':act2', $act2);
        $stmt->bindParam(':examen', $examen);
        return $stmt->execute();
    }

    // Consultar el reporte completo de materias y promedios por NIE
    public function obtenerBoletaCompleta($nie) {
        $query = "SELECT e.nie, e.nombre, e.apellido, e.grado, e.seccion, m.nombre_materia, n.actividad1, n.actividad2, n.examen_final, n.promedio_final 
                  FROM estudiantes e
                  CROSS JOIN materias m
                  LEFT JOIN notas n ON e.id = n.estudiante_id AND m.id = n.materia_id
                  WHERE e.nie = :nie";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nie', $nie);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registro académico de nuevos alumnos
    public function insertarEstudianteManual($nie, $nombre, $apellido, $correo, $grado, $seccion) {
        $query = "INSERT INTO estudiantes (nie, nombre, apellido, correo, grado, seccion) VALUES (:nie, :nombre, :apellido, :correo, :grado, :seccion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nie', $nie);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':grado', $grado);
        $stmt->bindParam(':seccion', $seccion);
        return $stmt->execute();
    }

    // Modificación de datos (Sincroniza Ficha y Usuario)
    public function actualizarDatosEstudiante($id, $nie, $nombre, $apellido, $correo, $grado, $seccion) {
        try {
            $this->conn->beginTransaction();
            $queryEst = "UPDATE estudiantes SET nombre = :nombre, apellido = :apellido, correo = :correo, grado = :grado, seccion = :seccion WHERE id = :id";
            $stmtEst = $this->conn->prepare($queryEst);
            $stmtEst->bindParam(':nombre', $nombre);
            $stmtEst->bindParam(':apellido', $apellido);
            $stmtEst->bindParam(':correo', $correo);
            $stmtEst->bindParam(':grado', $grado);
            $stmtEst->bindParam(':seccion', $seccion);
            $stmtEst->bindParam(':id', $id);
            $stmtEst->execute();

            $queryUser = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido WHERE username = :nie";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':nombre', $nombre);
            $stmtUser->bindParam(':apellido', $apellido);
            $stmtUser->bindParam(':nie', $nie);
            $stmtUser->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // Dar de baja definitiva a un alumno y sus accesos
    public function eliminarEstudianteCompleto($id, $nie) {
        try {
            $this->conn->beginTransaction();
            $queryUser = "DELETE FROM usuarios WHERE username = :nie";
            $stmtUser = $this->conn->prepare($queryUser);
            $stmtUser->bindParam(':nie', $nie);
            $stmtUser->execute();

            $queryEst = "DELETE FROM estudiantes WHERE id = :id";
            $stmtEst = $this->conn->prepare($queryEst);
            $stmtEst->bindParam(':id', $id);
            $stmtEst->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // NUEVO: Eliminar un docente del sistema de forma definitiva
    public function eliminarDocenteCompleto($id) {
        $query = "DELETE FROM usuarios WHERE id = :id AND rol = 'docente'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>