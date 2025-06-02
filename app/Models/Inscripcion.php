<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\Tokenizer;

class Inscripcion
{
    public static function obtenerTodas()
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->query("SELECT * FROM inscripciones");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db      = Database::getConnection();
            // Generar un ID único (token) para la nueva inscripción
            $nuevoId = Tokenizer::generarClaveApi();

            $stmt = $db->prepare("
                INSERT INTO inscripciones (id, alumnoId, eventoId, fechaRegistro)
                VALUES (:id, :alumnoId, :eventoId, :fechaRegistro)
            ");
            $stmt->execute([
                ':id'            => $nuevoId,
                ':alumnoId'      => $data['alumnoId'],
                ':eventoId'      => $data['eventoId'],
                ':fechaRegistro' => $data['fechaRegistro'],
            ]);

            // Obtener la inscripción recién creada
            $inscripcion = self::obtenerPorId($nuevoId);
            return ['success' => true, 'inscripcion' => $inscripcion];
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

            if (isset($data['alumnoId']) && $data['alumnoId'] !== '') {
                $campos[]           = 'alumnoId = :alumnoId';
                $params[':alumnoId'] = $data['alumnoId'];
            }
            if (isset($data['eventoId']) && $data['eventoId'] !== '') {
                $campos[]          = 'eventoId = :eventoId';
                $params[':eventoId'] = $data['eventoId'];
            }
            if (isset($data['fechaRegistro']) && $data['fechaRegistro'] !== '') {
                $campos[]             = 'fechaRegistro = :fechaRegistro';
                $params[':fechaRegistro'] = $data['fechaRegistro'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql  = 'UPDATE inscripciones SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'No se encontró la inscripción o no se modificó ningún dato'];
            }

            // Obtener la inscripción actualizada
            $inscripcion = self::obtenerPorId($id);
            return ['success' => true, 'inscripcion' => $inscripcion];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function eliminar($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM inscripciones WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'Inscripción no encontrada'];
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
            $stmt = $db->prepare("SELECT * FROM inscripciones WHERE id = :id");
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
            $stmt = $db->prepare("SELECT * FROM alumnos AS a INNER JOIN inscripciones AS i ON a.id = i.alumno_id WHERE evento_id = :eventoId");
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
            $stmt = $db->prepare("SELECT * FROM eventos AS e INNER JOIN inscripciones AS i ON e.id = i.evento_id WHERE i.alumno_id = :alumnoId");
            $stmt->execute([':alumnoId' => $alumnoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
