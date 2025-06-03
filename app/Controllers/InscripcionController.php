<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Inscripcion;
use Alexc\ProyectoAgustin\Controllers\UsuariosController;
use Exception;

class InscripcionController extends BaseController
{
    public function get($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no valida', 401);
        }
        if(!empty($params) && isset($params[0])){

            if (isset($params[1]) &&$params[1]=== 'evento') {
                return $this->inscripcionesPorEvento($params[0]);
            }elseif (isset($params[1]) &&$params[1] === 'alumno') {
                return $this->inscripcionesPorAlumno($params[0]);
            }else{
                return self::show($params[0]);
            }
        }

        $inscripciones = Inscripcion::obtenerTodas();

        if (isset($inscripciones['error'])) {
            return self::respuestaJson(03, $inscripciones['error'], 404);
        }

        return self::respuestaJson(01, 'Inscripciones obtenparams[0]as correctamente', 200, ['inscripciones' => $inscripciones]);
    }

    public function post($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if ($params[0]) return self::respuestaJson(03, 'No se debe proporcionar un params[0] al crear una nueva inscripción', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                $data = $_POST;
            }

            // Valparams[0]ación de campos necesarios
            if (empty($data['alumno_params[0]']) || empty($data['evento_params[0]'])) {
                return self::respuestaJson(03, 'Faltan campos obligatorios: alumno_params[0] y evento_params[0]', 400);
            }

            $resultado = Inscripcion::crear($data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, 'Inscripción creada correctamente', 201, ['inscripcion' => $resultado['inscripcion']]);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function put($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'params[0] de inscripción no proporcionado', 400);

        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                parse_str($input, $data);
            }

            $resultado = Inscripcion::actualizar($params[0], $data);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Inscripción con params[0] $params[0] actualizada correctamente", 200, ['inscripcion' => $resultado['inscripcion']]);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function delete($params)
    {
        if (!UsuariosController::autorizar()) {
            return self::respuestaJson(03, 'Acceso denegado. Clave API no válparams[0]a', 401);
        }

        if (!$params[0]) return self::respuestaJson(03, 'params[0] de inscripción no proporcionado', 400);

        try {
            $resultado = Inscripcion::eliminar($params[0]);

            if (isset($resultado['error'])) {
                return self::respuestaJson(02, $resultado['error'], 404);
            }

            return self::respuestaJson(01, "Inscripción con params[0] $params[0] eliminada correctamente", 200);
        } catch (Exception $e) {
            return self::respuestaJson(02, $e->getMessage(), 500);
        }
    }

    public function show($params)
    {
        $inscripcion = Inscripcion::obtenerPorId($params[0]);

        if (!$inscripcion) {
            return self::respuestaJson(03, 'Inscripción no encontrada', 404);
        }

        return self::respuestaJson(01, 'Inscripción encontrada', 200, ['inscripcion' => $inscripcion]);
    }

    public function inscripcionesPorAlumno($alumno)
    {
        $inscripciones = Inscripcion::obtenerPorAlumno($alumno);

        if (!$inscripciones || empty($inscripciones)) {
            return self::respuestaJson(03, 'No se encontraron inscripcion para este alumno', 404);
        }

        return self::respuestaJson(01, 'Inscripcion encontradas', 200, ['inscripciones' => $inscripciones]);
    }

    
    public function inscripcionesPorEvento($evento){
        $inscripciones = Inscripcion::obtenerPorEvento($evento);

        if (!$inscripciones || empty($inscripciones)) {
            return self::respuestaJson(03, 'No se encontraron inscripciones para este evento', 404);
        }

        return self::respuestaJson(01, 'Inscripciones encontradas', 200, ['inscripciones' => $inscripciones]);
    }
}
