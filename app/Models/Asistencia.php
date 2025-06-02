<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\Tokenizer;

class Asistencia
{
    public static function obtenerTodas()
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->query("SELECT * FROM asistencias");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db      = Database::getConnection();
            // Generar un ID único (token) para la nueva fila
            $nuevoId = Tokenizer::generarClaveApi();

            $stmt = $db->prepare("
                INSERT INTO asistencias (id, inscripcionId, fechaSesion, estado)
                VALUES (:id, :inscripcionId, :fechaSesion, :estado)
            ");

            $stmt->execute([
                ':id'            => $nuevoId,
                ':inscripcionId' => $data['inscripcionId'],
                ':fechaSesion'   => $data['fechaSesion'],
                ':estado'        => $data['estado'],
            ]);

            // Obtener el registro recién creado
            $asistencia = self::obtenerPorId($nuevoId);
            return ['success' => true, 'asistencia' => $asistencia];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function actualizar($id, $data)
    {
        try {
            $db     = Database::getConnection();
            $campos = [];
            $params = [':id' => $id];

            if (isset($data['inscripcionId']) && $data['inscripcionId'] !== '') {
                $campos[]                = 'inscripcionId = :inscripcionId';
                $params[':inscripcionId'] = $data['inscripcionId'];
            }
            if (isset($data['fechaSesion']) && $data['fechaSesion'] !== '') {
                $campos[]              = 'fechaSesion = :fechaSesion';
                $params[':fechaSesion'] = $data['fechaSesion'];
            }
            if (isset($data['estado']) && $data['estado'] !== '') {
                $campos[]            = 'estado = :estado';
                $params[':estado'] = $data['estado'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql  = 'UPDATE asistencias SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'No se encontró la asistencia o no se modificó ningún dato'];
            }

            // Obtener la asistencia actualizada
            $asistencia = self::obtenerPorId($id);
            return ['success' => true, 'asistencia' => $asistencia];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function eliminar($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM asistencias WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'Asistencia no encontrada'];
            }
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function obtenerPorId($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM asistencias WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return null;
            }
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }


    public static function obtenerPorEvento($eventoId)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM asistencias WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE evento_id = :eventoId)");
            $stmt->execute([':eventoId' => $eventoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function obtenerPorAlumno($alumnoId)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM asistencias WHERE inscripcion_id IN (SELECT id FROM inscripciones WHERE alumno_id = :alumnoId)");
            $stmt->execute([':alumnoId' => $alumnoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
