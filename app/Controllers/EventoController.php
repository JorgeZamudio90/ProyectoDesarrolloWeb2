<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\BaseController;
use Alexc\ProyectoAgustin\Models\Evento;

class EventoController extends BaseController
{
    public function index()
    {
        $eventos = Evento::obtenerTodos();
        return $this->json($eventos);
    }

    public function store()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $resultado = Evento::crear($data);
        return $this->json($resultado);
    }

    public function update($id)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $resultado = Evento::actualizar($id, $data);
        return $this->json($resultado);
    }

    //Eliminar Evento
    public function destroy($id)
    {
        $resultado = Evento::eliminar($id);
        return $this->json($resultado);
    }

    //Mostrar Evento por id
    public function show($id)
    {
        $evento = Evento::obtenerPorId($id);
        if (!$evento) {
            return $this->json(['error' => 'Evento no encontrado'], 404);
        }
        return $this->json($evento);
    }

    //Mostrar eventos por encargado
    public function eventosPorEncargado($encargadoId)
    {
        $eventos = Evento::obtenerPorEncargado($encargadoId);
        if (!$eventos) {
            return $this->json(['error' => 'No se encontraron eventos para este encargado'], 404);
        }
        return $this->json($eventos);
    }
}
