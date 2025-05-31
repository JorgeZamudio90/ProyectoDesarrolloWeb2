<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Inscripcion;

class InscripcionController extends BaseController
{
    public function index()
    {
        $inscripciones = Inscripcion::obtenerTodas();
        return $this->json($inscripciones);
    }

    public function store()
    {
        $data = $_POST;
        $resultado = Inscripcion::crear($data);
        return $this->json($resultado);
    }
}
