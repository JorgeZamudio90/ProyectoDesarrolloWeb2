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
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $resultado = Inscripcion::crear($data);
        return $this->json($resultado);
    }

    public function update($id)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $resultado = Inscripcion::actualizar($id, $data);
        return $this->json($resultado);
    }

    //Eliminar Inscripcion
    public function destroy($id)
    {
        $resultado = Inscripcion::eliminar($id);
        return $this->json($resultado);
    }

    //Mostrar Inscripcion por id
    public function show($id)
    {
        $inscripcion = Inscripcion::obtenerPorId($id);
        if (!$inscripcion) {
            return $this->json(['error' => 'Inscripcion no encontrado'], 404);
        }
        return $this->json($inscripcion);
    }
}
