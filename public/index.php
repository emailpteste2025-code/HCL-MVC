<?php
session_start();

// Carrega helpers
require_once __DIR__ . '/../app/helpers.php';

// Carrega as rotas
require_once __DIR__ . '/../routes/web.php';

// Obtém a rota atual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove "/index.php" ou "/public" do caminho
$base = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$route = '/' . trim(str_replace($base, '', $uri), '/');

// Se vazio → rota "/"
if ($route === '/') {
    $route = '/login';
}

// Despacha para o controlador
if (isset($routes[$route])) {
    $action = $routes[$route];
    [$controller, $method] = explode('@', $action);
    $controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $obj = new $controller();
        echo $obj->$method();
    } else {
        http_response_code(404);
        echo "Controller não encontrado";
    }
} else {
    http_response_code(404);
    echo "Rota não encontrada";
}
