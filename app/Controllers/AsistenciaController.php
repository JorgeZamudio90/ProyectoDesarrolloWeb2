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
        $data = $_POST;
        $resultado = Asistencia::crear($data);
        return $this->json($resultado);
    }
}
