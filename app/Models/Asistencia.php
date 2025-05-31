<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;

class Asistencia
{
    public static function obtenerTodas()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM asistencias");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO asistencias (inscripcionId, fechaSesion, estado) VALUES (:inscripcion_id, :fecha_sesion, :estado)");
            $stmt->execute([
                ':inscripcionId' => $data['inscripcionId'],
                ':fechaSesion' => $data['fechaSesion'],
                ':estado' => $data['estado'],
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Actualizar asistencia
    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE asistencias SET inscripcionId = :inscripcionId, fechaSesion = :fechaSesion, estado = :estado WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':inscripcionId' => $data['inscripcionId'],
                ':fechaSesion' => $data['fechaSesion'],
                ':estado' => $data['estado'],
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}