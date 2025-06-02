<?php

namespace Alexc\ProyectoAgustin\Models;

use PDO;
use PDOException;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\Tokenizer;

class Alumno
{
    public static function obtenerTodos()
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM alumnos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function crear($data)
    {
        try {
            $db = Database::getConnection();
            $nuevoId = Tokenizer::generarClaveApi();
            $stmt = $db->prepare("INSERT INTO alumnos (id, numControl, nombre, correo) VALUES (:id, :numControl, :nombre, :correo)");
            $stmt->execute([
                ':id' => $nuevoId,
                ':numControl' => $data['numControl'],
                ':nombre' => $data['nombre'],
                ':correo' => $data['correo'],
            ]);

            // Obtener el alumno recién creado
             $alumno=self::obtenerPorId($nuevoId);
            return ['success' => true, 'alumno' => $alumno];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Actualizar alumno
    public static function actualizar($id, $data)
    {
        try {
            $db = Database::getConnection();

            $campos = [];
            $params = [':id' => $id];

            if (isset($data['numControl']) && $data['numControl'] !== '') {
                $campos[] = 'numControl = :numControl';
                $params[':numControl'] = $data['numControl'];
            }

            if (isset($data['nombre']) && $data['nombre'] !== '') {
                $campos[] = 'nombre = :nombre';
                $params[':nombre'] = $data['nombre'];
            }

            if (isset($data['correo']) && $data['correo'] !== '') {
                $campos[] = 'correo = :correo';
                $params[':correo'] = $data['correo'];
            }

            // Si no hay campos que actualizar
            if (empty($campos)) {
                return ['error' => 'No se proporcionaron campos válidos para actualizar'];
            }

            // Armar el SQL dinámicamente
            $sql = 'UPDATE alumnos SET ' . implode(', ', $campos) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            if ($stmt->rowCount() === 0) {
                return ['error' => 'No se encontró el alumno o no se modificó ningún dato'];
            }

                // Obtener el alumno recién creado
            $alumno=self::obtenerPorId($id);
        return ['success' => true, 'alumno' => $alumno];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Eliminar encargado
    public static function eliminar($id)
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM alumnos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($stmt->rowCount() === 0) {
                return ['error' => 'Alumno no encontrado'];
            }

            return ['success' => true];
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    //Obtener encargado por id
    public static function obtenerPorId($id){
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM alumnos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if($stmt->rowCount()===0)return null;
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}