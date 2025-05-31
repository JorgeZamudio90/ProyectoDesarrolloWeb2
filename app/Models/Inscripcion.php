<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;

class Inscripcion
{
    public static function obtenerTodas()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM inscripcion");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO inscripcion (alumno_id, evento_id, fecha_registro) VALUES (:alumno_id, :evento_id, :fecha_registro)");
            $stmt->execute([
                ':alumno_id' => $data['alumno_id'],
                ':evento_id' => $data['evento_id'],
                ':fecha_registro' => $data['fecha_registro'],
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}