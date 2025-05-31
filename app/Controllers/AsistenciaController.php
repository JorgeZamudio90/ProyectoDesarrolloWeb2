<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Asistencia;

class AsistenciaController extends BaseController
{
    public function index()
    {
        $asistencias = Asistencia::obtenerTodas();
        return $this->json($asistencias);
    }

    public function store()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $resultado = Asistencia::crear($data);
        return $this->json($resultado);
    }

    public function update($id)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $resultado = Asistencia::actualizar($id, $data);
        return $this->json($resultado);
    }

    //Eliminar Asistencia
    public function destroy($id)
    {
        $resultado = Asistencia::eliminar($id);
        return $this->json($resultado);
    }

    //Mostrar asistencia por id
    public function show($id)
    {
        $asistencia = Asistencia::obtenerPorId($id);
        if (!$asistencia) {
            return $this->json(['error' => 'Asistencia no encontrado'], 404);
        }
        return $this->json($asistencia);
    }
}
