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
            $stmt = $db->query("SELECT * FROM inscripciones");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $ultimoId = $db->query("SELECT MAX(id) FROM encargados")->fetchColumn();
            $nuevoId = $ultimoId ? $ultimoId + 1 : 1;
            $stmt = $db->prepare("INSERT INTO inscripciones (id,alumnoId, eventoId, fechaRegistro) VALUES (:id, :alumnoId, :eventoId, :fechaRegistro)");
            $stmt->execute([
                ':id' => $nuevoId,
                ':alumnoId' => $data['alumnoId'],
                ':eventoId' => $data['eventoId'],
                ':fechaRegistro' => $data['fechaRegistro'],
            ]);
            return ['success' => true, 'id' => $nuevoId];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();

            $campos = [];
            $params = [':id' => $id];

            if (isset($data['alumnoId']) && $data['alumnoId'] !== '') {
                $campos[] = 'alumnoId = :alumnoId';
                $params[':alumnoId'] = $data['alumnoId'];
            }

            if (isset($data['eventoId']) && $data['eventoId'] !== '') {
                $campos[] = 'eventoId = :eventoId';
                $params[':eventoId'] = $data['eventoId'];
            }

            if (isset($data['fechaRegistro']) && $data['fechaRegistro'] !== '') {
                $campos[] = 'fechaRegistro = :fechaRegistro';
                $params[':fechaRegistro'] = $data['fechaRegistro'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql = 'UPDATE inscripciones SET ' . implode(', ', $campos) . ' WHERE id = :id';
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
            $stmt = $db->prepare("DELETE FROM inscripciones WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener inscripcion por id
    public static function obtenerPorId($id){
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM inscripciones WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}