<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Evento;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Exception;

class EventoController extends BaseController
{
    public function get($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no valida', 401);
        }
        if(!empty($params) && isset($params[0])){

            if (isset($params[1]) && $params[1] === 'encargado') {
                return $this->eventosPorEncargado($params[0]);
            }else{
                return $this->show($params[0]);
            }
        }

        $eventos = Evento::obtenerTodos();

        if (isset($eventos['error'])) {
            return self::respuestaJson(03, $eventos['error'], 404);
        }

        return self::respuestaJson(01, 'Eventos obtenidos correctamente', 200, ['eventos' => $eventos]);
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no validaa', 401);
        }

        if ($params[0]) return self::respuestaJson(03, 'No se debe proporcionar un$params[0] al crear un nuevo evento', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $data = $_POST;
            }

            if (empty($data['nombre']) || empty($data['fecha']) || empty($data['encargado$params[0]'])) {
                return self::respuestaJson(03, 'Faltan campos obligatorios', 400);
            }

            $resultado = Evento::crear($data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, 'Evento creado correctamente', 201, ['evento' => $resultado['evento']]);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function put($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no vá$params[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'ID de evento no proporcionado', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                parse_str($input, $data);
            }

            $resultado = Evento::actualizar($params[0], $data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Evento con$params[0] $params[0] actualizado correctamente", 200, ['evento' => $resultado['evento']]);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function delete($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no vá$params[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'ID de evento no proporcionado', 400);

        try {
            $resultado = Evento::eliminar($params[0]);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Evento con$params[0] $params[0] eliminado correctamente", 200);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function show($params)
    {
        $evento = Evento::obtenerPorId($params);

        if (!$evento) {
            return self::respuestaJson(03, 'Evento no encontrado', 404);
        }

        return self::respuestaJson(01, 'Evento encontrado', 200, ['evento' => $evento]);
    }

    public function eventosPorEncargado($encargado)
    {
        $eventos = Evento::obtenerPorEncargado($encargado);

        if (!$eventos || empty($eventos)) {
            return self::respuestaJson(03, 'No se encontraron eventos para este encargado', 404);
        }

        return self::respuestaJson(01, 'Eventos encontrados', 200, ['eventos' => $eventos]);
    }
}
