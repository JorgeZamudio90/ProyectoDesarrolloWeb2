<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\Tokenizer;

class Encargado
{
    public static function obtenerTodos()
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->query("SELECT * FROM encargados");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db      = Database::getConnection();
            $nuevoId = Tokenizer::generarClaveApi(); 
            $stmt    = $db->prepare("
                INSERT INTO encargados (id, nombre, correo, telefono)
                VALUES (:id, :nombre, :correo, :telefono)
            ");
            $stmt->execute([
                ':id'       => $nuevoId,
                ':nombre'   => $data['nombre'],
                ':correo'   => $data['correo'],
                ':telefono' => $data['telefono'],
            ]);

            // Obtener el registro recién creado
            $encargado = self::obtenerPorId($nuevoId);
            return ['success' => true, 'encargado' => $encargado];
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

            if (isset($data['nombre']) && $data['nombre'] !== '') {
                $campos[]           = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }
            if (isset($data['correo']) && $data['correo'] !== '') {
                $campos[]          = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }
            if (isset($data['telefono']) && $data['telefono'] !== '') {
                $campos[]           = 'telefono = :telefono';
                $params[':telefono'] = $data['telefono'];
            }

            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            $sql  = 'UPDATE encargados SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'No se encontró el encargado o no se modificó ningún dato'];
            }

            // Obtener el registro actualizado
            $encargado = self::obtenerPorId($id);
            return ['success' => true, 'encargado' => $encargado];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function eliminar($id)
    {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM encargados WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                return ['error' => 'Encargado no encontrado'];
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
            $stmt = $db->prepare("SELECT * FROM encargados WHERE id = :id");
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
