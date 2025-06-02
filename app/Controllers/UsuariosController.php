<?php

namespace Alexc\ProyectoAgustin\Controllers;

use Alexc\ProyectoAgustin\Core\Tokenizer;
use Alexc\ProyectoAgustin\Core\Database;
use Alexc\ProyectoAgustin\Core\BaseController;
use PDO;
use PDOException;

class UsuariosController extends BaseController
{
    public function get($peticion)
    {
        self::respuestaJson(1, "Petición GET recibida", 200,["Info" => "Esta ruta solo funciona para el método POST y tiene como funcionalidades disponibles 'registro' y 'login'"]);
    }

    public function post($peticion)
    {
        if ($peticion == 'registro') {
            return self::registrar();
        } else if ($peticion == 'login') {
            return self::loguear();
        } else {
            self::respuestaJson(2, "URL mal formada", 400);
        }
    }

    private function registrar()
    {
        $cuerpo = file_get_contents('php://input');
        $usuario = json_decode($cuerpo);

        if (!is_object($usuario)) {
            $usuario = (object)$_POST;
        }

        $resultado = self::crear($usuario);

        switch ($resultado['estado']) {
            case 1:
                http_response_code(201);
                return self::respuestaJson($resultado['estado'], "¡Registro con éxito!", 201, $resultado['resultados']);
            case 2:
                self::respuestaJson($resultado['estado'], "Ha ocurrido un error al crear al usuario", 400);
            default:
                self::respuestaJson(4, "Falla desconocida", 500);
        }
    }

    private function crear($datosUsuario)
    {
        $nombre = $datosUsuario->nombre ?? null;
        $contrasena = $datosUsuario->contrasena ?? null;
        $correo = $datosUsuario->correo ?? null;

        if (!$nombre || !$contrasena || !$correo) {
            return ['estado' => 2];
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            self::respuestaJson(3, "Correo inválido", 400);
        }
        if (self::obtenerUsuarioPorCorreo($correo) !== null) {
            self::respuestaJson(3, "El correo ya está registrado", 400);
        }

        $contrasenaEncriptada = self::encriptarContrasena($contrasena);
        $claveApi = Tokenizer::generarClaveApi();

        try {
            $pdo = Database::getConnection();
            $comando = "INSERT INTO usuarios (nombre, contrasena, claveApi, correo) VALUES (?, ?, ?, ?)";
            $sentencia = $pdo->prepare($comando);

            $sentencia->bindParam(1, $nombre);
            $sentencia->bindParam(2, $contrasenaEncriptada);
            $sentencia->bindParam(3, $claveApi);
            $sentencia->bindParam(4, $correo);

            if ($sentencia->execute()) {
                return [
                    'estado' => 1,
                    'resultados' => [
                        'id' => $pdo->lastInsertId(),
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'claveApi' => $claveApi
                    ]
                ];
            } else {
                return ['estado' => 2];
            }
        } catch (PDOException $e) {
            self::respuestaJson(2, $e->getMessage(), 500);
        }
    }

    private function encriptarContrasena($contrasenaPlana)
    {
        return $contrasenaPlana ? password_hash($contrasenaPlana, PASSWORD_DEFAULT) : null;
    }

    private function loguear()
    {
        $body = file_get_contents('php://input');
        $usuario = json_decode($body);

        if (!is_object($usuario)) {
            $usuario = (object)$_POST;
        }

        $correo = $usuario->correo ?? null;
        $contrasena = $usuario->contrasena ?? null;

        if (!$correo || !$contrasena) {
            self::respuestaJson(3, "Faltan datos de autenticación", 400);
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            self::respuestaJson(3, "Correo inválido", 400);
        }
        if (self::autenticar($correo, $contrasena)) {
            $usuarioBD = self::obtenerUsuarioPorCorreo($correo);

            if ($usuarioBD !== null) {
                http_response_code(200);
                return self::respuestaJson(1, "Inicio de sesión correcto", 200, [
                    "nombre" => $usuarioBD["nombre"],
                    "correo" => $usuarioBD["correo"],
                    "claveApi" => $usuarioBD["claveApi"]
                ]);
            } else {
                self::respuestaJson(2, "No se encontró el usuario con el correo proporcionado", 404);
            }
        } else {
            self::respuestaJson(3, "Correo o contraseña inválidos", 401);
        }
    }

    private function obtenerUsuarioPorCorreo($correo)
    {
        $comando = "SELECT * FROM usuarios WHERE correo = ?";
        $sentencia = Database::getConnection()->prepare($comando);
        $sentencia->bindParam(1, $correo);
        $sentencia->execute();
        return $sentencia->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function autenticar($correo, $contrasena)
    {
        $comando = "SELECT contrasena FROM usuarios WHERE correo = ?";
        try {
            $sentencia = Database::getConnection()->prepare($comando);
            $sentencia->bindParam(1, $correo);
            $sentencia->execute();

            $resultado = $sentencia->fetch();
            return $resultado && self::validarContrasena($contrasena, $resultado['contrasena']);
        } catch (PDOException $e) {
            self::respuestaJson(2, $e->getMessage(), 500);
        }
    }

    private function validarContrasena($contrasenaPlana, $contrasenaHash)
    {
        return password_verify($contrasenaPlana, $contrasenaHash);
    }

    public static function autorizar()
    {
        $cabeceras = apache_request_headers();

        if (isset($cabeceras["Authorization"])) {
            $claveApi = $cabeceras["Authorization"];
            list($type,$claveApi)=explode(" ", $claveApi, 2);
            if (strcasecmp($type,'Bearer')) {
                self::respuestaJson(3, "Tipo de autorización no soportado", 401);
            }
            return self::validarClaveApi($claveApi);
        } else {
            self::respuestaJson(3, "Se requiere una clave de autorización", 401);
        }
    }

    private static function validarClaveApi($claveApi)
    {
        $comando = "SELECT COUNT(id) FROM usuarios WHERE claveApi = ?";
        $sentencia = Database::getConnection()->prepare($comando);
        $sentencia->bindParam(1, $claveApi);
        $sentencia->execute();
        return $sentencia->fetchColumn() > 0;
    }
}
