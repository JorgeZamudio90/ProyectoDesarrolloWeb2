<?php
require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once ROOT . 'config/config.php';

$url = isset($_GET['url']) ? $_GET['url'] : null;

if ($url && strpos($url, 'public/') === 0) {
    $url = substr($url, 7);
}

if (!$url) {
    die("No se especificó controlador y método en la URL.");
}

$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

$controllerName = !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'HomeController';
$methodName = $urlParts[1] ?? 'index';
$params = array_slice($urlParts, 2);

// Nombre completo del controlador con namespace
$controllerClass = "Alexc\\ProyectoAgustin\\Controllers\\$controllerName";

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    if (method_exists($controller, $methodName)) {
        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "Método '$methodName' no encontrado en '$controllerClass'";
    }
} else {
    echo "Controlador '$controllerClass' no encontrado.";
}
