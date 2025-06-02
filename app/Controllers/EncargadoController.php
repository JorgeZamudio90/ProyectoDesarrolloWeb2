<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Encargado;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Exception;

class EncargadoController extends BaseController
{
    public function get($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válida', 401);
        }
        if(!empty($params)){

            if ($params[0]) {
                return $this->show($params[0]);
            }
        }

        $encargados = Encargado::obtenerTodos();

        if (isset($encargados['error'])) {
            return self::respuestaJson(03, $encargados['error'], 404);
        }

        return self::respuestaJson(01, 'Datos obte$params[0]os correctamente', 200, ['encargados' => $encargados]);
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no vá$params[0]a', 401);
        }

        if ($params[0]) return self::respuestaJson(03, 'No se debe proporcionar un$params[0] al crear un nuevo encargado', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $data = $_POST;
            }

            if (empty($data['nombre']) || empty($data['correo']) || empty($data['telefono'])) {
                return self::respuestaJson(03, 'Faltan campos obligatorios', 400);
            }

            if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                return self::respuestaJson(03, 'Correo electrónico no vá$params[0]o', 400);
            }

            $resultado = Encargado::crear($data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, 'Encargado agregado correctamente', 201, ['encargado' => $resultado['encargado']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function put($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no vá$params[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, "ID de encargado no proporcionado", 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                parse_str($input, $data);
            }

            $resultado = Encargado::actualizar($params[0], $data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Encargado con$params[0] $params[0] actualizado correctamente", 200, ['encargado' => $resultado['encargado']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function delete($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no vá$params[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'ID de encargado no proporcionado', 400);

        try {
            $resultado = Encargado::eliminar($params[0]);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Encargado con$params[0] $params[0] eliminado correctamente", 200);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function show($params)
    {
        $encargado = Encargado::obtenerPorId($params);

        if (!$encargado) {
            return self::respuestaJson(03, 'Encargado no encontrado', 404);
        }

        return self::respuestaJson(01, 'Encargado encontrado', 200, ['encargado' => $encargado]);
    }
}
