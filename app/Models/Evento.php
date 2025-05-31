<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;

class Evento
{
    public static function obtenerTodos()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM eventos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $ultimoId = $db->query("SELECT MAX(id) FROM eventos")->fetchColumn();
            $nuevoId = $ultimoId ? $ultimoId + 1 : 1;
            $stmt = $db->prepare("INSERT INTO eventos (id, titulo, descripcion, fechaInicio, fechaFin, tipoEvento, cupoMaximo, encargadoId) 
            VALUES (id, :titulo, :descripcion, :fechaInicio, :fechaFin, :tipoEvento, :cupoMaximo, :encargadoId)");
            $stmt->execute([
                ':id' => $nuevoId,
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':fechaInicio' => $data['fechaInicio'],
                ':fechaFin' => $data['fechaFin'],
                ':tipoEvento' => $data['tipoEvento'],
                ':cupoMaximo' => $data['cupoMaximo'],
                ':encargadoId' => $data['encargadoId'],
            ]);
            return ['success' => true, 'id' => $db->lastInsertId()];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Actualizar evento
    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();

            $campos = [];
            $params = [':id' => $id];

            if (isset($data['titulo']) && $data['titulo'] !== '') {
                $campos[] = 'titulo = :titulo';
                $params[':titulo'] = $data['titulo'];
            }

            if (isset($data['descripcion']) && $data['descripcion'] !== '') {
                $campos[] = 'descripcion = :descripcion';
                $params[':descripcion'] = $data['descripcion'];
            }

            if (isset($data['fechaInicio']) && $data['fechaInicio'] !== '') {
                $campos[] = 'fechaInicio = :fechaInicio';
                $params[':fechaInicio'] = $data['fechaInicio'];
            }

            if (isset($data['fechaFin']) && $data['fechaFin'] !== '') {
                $campos[] = 'fechaFin = :fechaFin';
                $params[':fechaFin'] = $data['fechaFin'];
            }

            if (isset($data['tipoEvento']) && $data['tipoEvento'] !== '') {
                $campos[] = 'tipoEvento = :tipoEvento';
                $params[':tipoEvento'] = $data['tipoEvento'];
            }

            if (isset($data['cupoMaximo']) && $data['cupoMaximo'] !== '') {
                $campos[] = 'cupoMaximo = :cupoMaximo';
                $params[':cupoMaximo'] = $data['cupoMaximo'];
            }

            if (isset($data['encargadoId']) && $data['encargadoId'] !== '') {
                $campos[] = 'encargadoId = :encargadoId';
                $params[':encargadoId'] = $data['encargadoId'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql = 'UPDATE eventos SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Eliminar evento
    public static function eliminar($id)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM eventos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }


    //Obtener por evento
    public static function obtenerPorEncargado($encargadoId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM eventos WHERE encargadoId = :encargadoId");
            $stmt->execute([':encargadoId' => $encargadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener por id del evento
    public static function obtenerPorId($id){
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM eventos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
