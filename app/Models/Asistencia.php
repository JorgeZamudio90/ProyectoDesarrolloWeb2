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
            $ultimoId = $db->query("SELECT MAX(id) FROM asistencias")->fetchColumn();
            $nuevoId = $ultimoId ? $ultimoId + 1 : 1;
            $stmt = $db->prepare("INSERT INTO asistencias (id,,inscripcionId, fechaSesion, estado) VALUES (:id,, :inscripcion_id, :fecha_sesion, :estado)");
            $stmt->execute([
                ':id' => $nuevoId,
                ':inscripcionId' => $data['inscripcionId'],
                ':fechaSesion' => $data['fechaSesion'],
                ':estado' => $data['estado'],
            ]);
            return ['success' => true, 'id' => $nuevoId];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Actualizar asistencia
    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();

            $campos = [];
            $params = [':id' => $id];

            if (isset($data['inscripcionId']) && $data['inscripcionId'] !== '') {
                $campos[] = 'inscripcionId = :inscripcionId';
                $params[':inscripcionId'] = $data['inscripcionId'];
            }

            if (isset($data['fechaSesion']) && $data['fechaSesion'] !== '') {
                $campos[] = 'fechaSesion = :fechaSesion';
                $params[':fechaSesion'] = $data['fechaSesion'];
            }

            if (isset($data['estado']) && $data['estado'] !== '') {
                $campos[] = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }

            // Si no hay campos que actualizar
            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            // Armar el SQL dinámicamente
            $sql = 'UPDATE asistencias SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Eliminar asistencia
    public static function eliminar($id)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM asistencias WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener asistencia por id
    public static function obtenerPorId($id){
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM asistencias WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}