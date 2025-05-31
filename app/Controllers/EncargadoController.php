<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Encargado;

class EncargadoController extends BaseController
{
    //Mostrar todos los encargados
    public function index()
    {
        $encargados = Encargado::obtenerTodos();
        return $this->json($encargados);
    }

    //Crear encargado
    public function crear()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!isset($data['nombre'], $data['correo'], $data['telefono'])) {
            return $this->json(['error' => 'Faltan campos requeridos'], 400);
        }
        $resultado = Encargado::crear($data);
        return $this->json($resultado);
    }

    //Actualizar usuario
    public function update($id)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $resultado = Encargado::actualizar($id, $data);
        return $this->json($resultado);
    }

    //Eliminar encargado
    public function destroy($id)
    {
        $resultado = Encargado::eliminar($id);
        return $this->json($resultado);
    }

    //Mostrar encargado por id
    public function show($id)
    {
        $encargado = Encargado::obtenerPorId($id);
        if (!$encargado) {
            return $this->json(['error' => 'Encargado no encontrado'], 404);
        }
        return $this->json($encargado);
    }


}
