<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../vendor/autoload.php';

$mongoUri = getenv('MONGODB_URI');
if (!$mongoUri) {
    http_response_code(500);
    echo json_encode(['error' => 'La variable de entorno MONGODB_URI no esta configurada']);
    exit;
}

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../controllers/SingerController.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$controller = new SingerController();

if (strpos($requestUri, '/api/guardar.php') !== false) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $controller->store();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Metodo no permitido.']);
    }
} elseif (strpos($requestUri, '/api/listar.php') !== false) {
    if ($_SERVER["REQUEST_METHOD"] === "GET" || $_SERVER["REQUEST_METHOD"] === "POST") {
        $controller->index();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Metodo no permitido.']);
    }
} elseif (strpos($requestUri, '/api/eliminar.php') !== false) {
    if ($_SERVER["REQUEST_METHOD"] === "DELETE" || $_SERVER["REQUEST_METHOD"] === "POST") {
        $controller->destroy();
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Metodo no permitido.']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Ruta no encontrada']);
}
