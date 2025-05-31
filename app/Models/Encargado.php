<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;

class Encargado
{

    //Mostrar todos los encargados
    public static function obtenerTodos()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM encargados");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Crear encargado
    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $ultimoId = $db->query("SELECT MAX(id) FROM encargados")->fetchColumn();
            $nuevoId = $ultimoId ? $ultimoId + 1 : 1;
            $stmt = $db->prepare("INSERT INTO encargados (id, nombre, correo, telefono) VALUES (:id, :nombre, :correo, :telefono)");
            $stmt->execute([
                ':id' => $nuevoId,
                ':nombre' => $data['nombre'],
                ':correo' => $data['correo'],
                ':telefono' => $data['telefono'],
            ]);
            return ['success' => true, 'id' => $nuevoId];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Actualizar encargado
    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();

            $campos = [];
            $params = [':id' => $id];

            if (isset($data['nombre']) && $data['nombre'] !== '') {
                $campos[] = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }

            if (isset($data['correo']) && $data['correo'] !== '') {
                $campos[] = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }

            if (isset($data['telefono']) && $data['telefono'] !== '') {
                $campos[] = 'telefono = :telefono';
                $params[':telefono'] = $data['telefono'];
            }

            // Si no hay campos que actualizar
            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            // Armar el SQL dinámicamente
            $sql = 'UPDATE encargados SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Eliminar encargado
    public static function eliminar($id)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM encargados WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener encargado por id
    public static function obtenerPorId($id){
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM encargados WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
