<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\Tokenizer;

class Evento
{
    public static function obtenerTodos()
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->query("SELECT * FROM eventos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db      = Database::getConnection();
            // Generar un ID único (token) para el nuevo evento
            $nuevoId = Tokenizer::generarClaveApi();

            $stmt = $db->prepare("
                INSERT INTO eventos (
                    id,
                    titulo,
                    descripcion,
                    fechaInicio,
                    fechaFin,
                    tipoEvento,
                    cupoMaximo,
                    encargadoId
                ) VALUES (
                    :id,
                    :titulo,
                    :descripcion,
                    :fechaInicio,
                    :fechaFin,
                    :tipoEvento,
                    :cupoMaximo,
                    :encargadoId
                )
            ");

            $stmt->execute([
                ':id'           => $nuevoId,
                ':titulo'       => $data['titulo'],
                ':descripcion'  => $data['descripcion'],
                ':fechaInicio'  => $data['fechaInicio'],
                ':fechaFin'     => $data['fechaFin'],
                ':tipoEvento'   => $data['tipoEvento'],
                ':cupoMaximo'   => $data['cupoMaximo'],
                ':encargadoId'  => $data['encargadoId'],
            ]);

            // Obtener el evento recién creado
            $evento = self::obtenerPorId($nuevoId);
            return ['success' => true, 'evento' => $evento];
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

            if (isset($data['titulo']) && $data['titulo'] !== '') {
                $campos[]           = 'titulo = :titulo';
                $params[':titulo'] = $data['titulo'];
            }
            if (isset($data['descripcion']) && $data['descripcion'] !== '') {
                $campos[]              = 'descripcion = :descripcion';
                $params[':descripcion'] = $data['descripcion'];
            }
            if (isset($data['fechaInicio']) && $data['fechaInicio'] !== '') {
                $campos[]              = 'fechaInicio = :fechaInicio';
                $params[':fechaInicio'] = $data['fechaInicio'];
            }
            if (isset($data['fechaFin']) && $data['fechaFin'] !== '') {
                $campos[]            = 'fechaFin = :fechaFin';
                $params[':fechaFin'] = $data['fechaFin'];
            }
            if (isset($data['tipoEvento']) && $data['tipoEvento'] !== '') {
                $campos[]              = 'tipoEvento = :tipoEvento';
                $params[':tipoEvento'] = $data['tipoEvento'];
            }
            if (isset($data['cupoMaximo']) && $data['cupoMaximo'] !== '') {
                $campos[]                = 'cupoMaximo = :cupoMaximo';
                $params[':cupoMaximo'] = $data['cupoMaximo'];
            }
            if (isset($data['encargadoId']) && $data['encargadoId'] !== '') {
                $campos[]                 = 'encargadoId = :encargadoId';
                $params[':encargadoId'] = $data['encargadoId'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql  = 'UPDATE eventos SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'No se encontró el evento o no se modificó ningún dato'];
            }

            // Obtener el evento actualizado
            $evento = self::obtenerPorId($id);
            return ['success' => true, 'evento' => $evento];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function eliminar($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM eventos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'Evento no encontrado'];
            }
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function obtenerPorEncargado($encargadoId)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM eventos WHERE encargadoId = :encargadoId");
            $stmt->execute([':encargadoId' => $encargadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function obtenerPorId($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM eventos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return null;
            }
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
