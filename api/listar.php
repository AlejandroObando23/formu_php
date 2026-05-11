<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require __DIR__ . '/../vendor/autoload.php';

$mongoUri = getenv('MONGODB_URI');
if (!$mongoUri) {
    http_response_code(500);
    echo json_encode(['error' => 'La variable de entorno MONGODB_URI no está configurada']);
    exit;
}

try {
    $client = new MongoDB\Client($mongoUri);
    $collection = $client->mi_base_de_datos->usuarios;
    $usuarios = $collection->find([], ['sort' => ['fecha_registro' => -1]]);
    $result = [];
    foreach ($usuarios as $usuario) {
        $result[] = [
            'nombre' => $usuario['nombre'] ?? '',
            'email' => $usuario['email'] ?? '',
            'type' => $usuario['type'] ?? '',
            'fecha_registro' => $usuario['fecha_registro'] ? $usuario['fecha_registro']->toDateTime()->format('Y-m-d H:i:s') : ''
        ];
    }
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepción de MongoDB: ' . $e->getMessage()]);
}