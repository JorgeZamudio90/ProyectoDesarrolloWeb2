<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Alumno;

class AlumnoController extends BaseController
{
    public function alumnos()
    {
        $alumnos = Alumno::obtenerTodos();
        return $this->json($alumnos);
    }

    public function crear()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $resultado = Alumno::crear($data);
        return $this->json($resultado);
    }

    public function update($id)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $resultado = Alumno::actualizar($id, $data);
        return $this->json($resultado);
    }

    //Eliminar alumno
    public function destroy($id)
    {
        $resultado = Alumno::eliminar($id);
        return $this->json($resultado);
    }

    //Mostrar alumno por id
    public function show($id)
    {
        $encargado = Alumno::obtenerPorId($id);
        if (!$encargado) {
            return $this->json(['error' => 'Encargado no encontrado'], 404);
        }
        return $this->json($encargado);
    }
}