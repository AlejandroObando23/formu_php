<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$mongoUri = getenv('MONGODB_URI');

if (!$mongoUri) {
    http_response_code(500);
    echo json_encode(['error' => 'La variable de entorno MONGODB_URI no está configurada']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "DELETE" || $_SERVER["REQUEST_METHOD"] == "POST") {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['id'])) {
        try {
            $client = new MongoDB\Client($mongoUri);
            $collection = $client->mi_base_de_datos->cantantes;
            
            $deleteResult = $collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($data['id'])]);
            
            if ($deleteResult->getDeletedCount() == 1) {
                echo json_encode(['message' => 'Registro eliminado exitosamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Registro no encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID no proporcionado']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
