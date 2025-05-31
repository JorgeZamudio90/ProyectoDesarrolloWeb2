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
        $data = $_POST;
        $resultado = Evento::crear($data);
        return $this->json($resultado);
    }
}
