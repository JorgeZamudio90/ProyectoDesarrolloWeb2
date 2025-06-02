<?php

namespace Alexc\ProyectoAgustin\Core;

class BaseController
{
    protected static function json($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected static function respuestaJson($codigo,$mensaje,$statusCode=200,$extra=[]){
        
        return self::json(array_merge(['estado'=>$codigo,'mensaje'=>$mensaje],$extra),$statusCode);
    }
}