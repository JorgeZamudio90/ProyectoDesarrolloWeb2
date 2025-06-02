<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Alumno;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Exception;

class AlumnoController extends BaseController
{
    public function get($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no valida', 401);
        }
        if(!empty($params)){
                if($params[0]){
                    $this->show($params[0]);
                }
            }
        $alumnos = Alumno::obtenerTodos();
        if (isset($alumnos['error'])) {
            return self::respuestaJson(03, $alumnos['error'], 404);
        }
        return self::respuestaJson(01, 'Datos obtenidos correctamente', 200, ['alumnos' => $alumnos]);
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if($params[0])return self::respuestaJson(03, 'No se debe proporcionar un params[0] al crear un nuevo alumno', 400);
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $data = $_POST;
            }

            if (empty($data['numControl']) || empty($data['nombre']) || empty($data['correo'])) {
                return self::respuestaJson(03, 'Faltan campos obligatorios', 400);
            }

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                return self::respuestaJson(03, 'Correo electrónico no válparams[0]o', 400);
            }

            $resultado = Alumno::crear($data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, 'Alumno agregado correctamente', 201, ['alumno' => $resultado['alumno']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function put($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if(!$params[0])return self::respuestaJson(03, 'params[0] de alumno no proporcionado', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                parse_str($input, $data);
            }

            $resultado = Alumno::actualizar($params[0], $data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Alumno con params[0] $params[0] actualizado correctamente", 200, ['alumno' => $resultado['alumno']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function delete($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if(!$params[0])return self::respuestaJson(03, 'params[0] de alumno no proporcionado', 400);

        try {
            $resultado = Alumno::eliminar($params[0]);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Alumno con params[0] $params[0] eliminado correctamente", 200);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function show($params)
    {
        $alumno = Alumno::obtenerPorId($params);

        if (!$alumno) {
            return self::respuestaJson(03, 'Alumno no encontrado', 404);
        }

        return self::respuestaJson(01, 'Alumno encontrado', 200, ['alumno' => $alumno]);
    }
}
