<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Asistencia;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Exception;

class AsistenciaController extends BaseController
{
    public function get($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válida', 401);
        }
        if (!empty($params) && isset($params[0])) 
        {
            if (isset($params[1]) &&$params[1]=== 'evento') {
                return $this->asistenciasPorEvento($params[0]);
            }elseif (isset($params[1]) &&$params[1]=== 'alumno') {
                return $this->asistenciasPorAlumno($params[0]);
            }else{
                return self::show($params[0]);
            }
        }

        $asistencias = Asistencia::obtenerTodas();

        if (isset($asistencias['error'])) {
            return self::respuestaJson(03, $asistencias['error'], 404);
        }

        return self::respuestaJson(01, 'Datos obtenidos correctamente', 200, ['asistencias' => $asistencias]);
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if ($params[0]) return self::respuestaJson(03, 'No se debe proporcionar un id al crear una nueva asistencia', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $data = $_POST;
            }

            // Valparams[0]ar campos obligatorios (ajusta según tu modelo)
            if (empty($data['alumno_params[0]']) || empty($data['fecha']) || empty($data['estado'])) {
                return self::respuestaJson(03, 'Faltan campos obligatorios', 400);
            }

            $resultado = Asistencia::crear($data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, 'Asistencia registrada correctamente', 201, ['asistencia' => $resultado['asistencia']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function put($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'params[0] de asistencia no proporcionado', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                parse_str($input, $data);
            }

            $resultado = Asistencia::actualizar($params[0], $data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Asistencia con params[0] $params[0] actualizada correctamente", 200, ['asistencia' => $resultado['asistencia']]);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function delete($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'params[0] de asistencia no proporcionado', 400);

        try {
            $resultado = Asistencia::eliminar($params[0]);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Asistencia con params[0] $params[0] eliminada correctamente", 200);
        } catch (Exception $exception) {
            return self::respuestaJson(02, $exception->getMessage(), 500);
        }
    }

    public function show($params)
    {
        $asistencia = Asistencia::obtenerPorId($params);

        if (!$asistencia) {
            return self::respuestaJson(03, 'Asistencia no encontrada', 404);
        }

        return self::respuestaJson(01, 'Asistencia encontrada', 200, ['asistencia' => $asistencia]);
    }

    public function asistenciasPorAlumno($alumno)
    {
        $asistencias = Asistencia::obtenerPorAlumno($alumno);

        if (!$asistencias || empty($asistencias)) {
            return self::respuestaJson(03, 'No se encontraron asistencias para este alumno', 404);
        }

        return self::respuestaJson(01, 'Asistencias encontradas', 200, ['asistencias' => $asistencias]);
    }


    public function asistenciasPorEvento($evento){
        $asistencias = Asistencia::obtenerPorEvento($evento);

        if (!$asistencias || empty($asistencias)) {
            return self::respuestaJson(03, 'No se encontraron asistencias para este evento', 404);
        }

        return self::respuestaJson(01, 'Asistencias encontradas', 200, ['asistencias' => $asistencias]);
    }
}
