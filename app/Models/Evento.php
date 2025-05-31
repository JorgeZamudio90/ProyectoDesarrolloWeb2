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
            $stmt = $db->prepare("INSERT INTO eventos (titulo, descripcion, fechaInicio, fechaFin, tipoEvento, cupoMaximo, encargadoId) 
            VALUES (:titulo, :descripcion, :fechaInicio, :fechaFin, :tipoEvento, :cupoMaximo, :encargadoId)");
            $stmt->execute([
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
            $stmt = $db->prepare("UPDATE eventos SET titulo = :titulo, descripcion = :descripcion, fechaInicio = :fechaInicio, fechaFin = :fechaFin, tipoEvento = :tipoEvento, cupoMaximo = :cupoMaximo, encargadoId = :encargadoId WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':fechaInicio' => $data['fechaInicio'],
                ':fechaFin' => $data['fechaFin'],
                ':tipoEvento' => $data['tipoEvento'],
                ':cupoMaximo' => $data['cupoMaximo'],
                ':encargadoId' => $data['encargadoId'],
            ]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener por encargado
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

    //Obtener po id del evento
    public static function obtenerPorId($eventoId)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM eventos WHERE id = :encargadoId");
            $stmt->execute([':encargadoId' => $eventoId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
