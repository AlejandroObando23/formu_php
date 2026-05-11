<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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
    // Leemos de la colección 'cantantes'
    $collection = $client->mi_base_de_datos->cantantes;
    $usuarios = $collection->find([], ['sort' => ['fecha_registro' => -1]]);
    $result = [];
    
    foreach ($usuarios as $usuario) {
        $fecha = '';
        if (isset($usuario['fecha_registro']) && $usuario['fecha_registro'] instanceof MongoDB\BSON\UTCDateTime) {
            $fecha = $usuario['fecha_registro']->toDateTime()->format('Y-m-d H:i:s');
        }
        $result[] = [
            'name' => $usuario['name'] ?? '',
            'stage_name' => $usuario['stage_name'] ?? '',
            'genre' => $usuario['genre'] ?? '',
            'debut_year' => $usuario['debut_year'] ?? '',
            'albums' => $usuario['albums'] ?? '',
            'listeners' => $usuario['listeners'] ?? '',
            'awards' => $usuario['awards'] ?? '',
            'country' => $usuario['country'] ?? '',
            'fecha_registro' => $fecha
        ];
    }
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Excepción de MongoDB: ' . $e->getMessage()]);
}