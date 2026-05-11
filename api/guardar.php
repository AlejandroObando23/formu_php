<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . '/../vendor/autoload.php';

$mongoUri = getenv('MONGODB_URI');

if (!$mongoUri) {
    http_response_code(500);
    echo json_encode(['error' => 'La variable de entorno MONGODB_URI no está configurada']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['nombre']) && isset($data['edad'])) {
        $nombre = $data['nombre'];
        $edad = $data['edad'];
        $type = $data['type'] ?? 'user';

        // Validar que instrument esté presente y no vacío
        if (!isset($data['instrument']) || empty($data['instrument'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Debe especificar un instrumento.']);
            exit;
        }
        $instrument = $data['instrument'];
        // Si instrument es array, convertirlo a string
        if (is_array($instrument)) {
            $instrument = implode(', ', $instrument);
        }

        try {
            $client = new MongoDB\Client($mongoUri);
            $collection = $client->mi_base_de_datos->usuarios;

            $insertOneResult = $collection->insertOne([
                'nombre' => $nombre,
                'edad' => $edad,
                'instrument' => $instrument,
                'type' => $type,
                'fecha_registro' => new MongoDB\BSON\UTCDateTime()
            ]);

            if ($insertOneResult->getInsertedCount() == 1) {
                echo json_encode(['message' => '¡Registro exitoso en MongoDB Atlas a través de PHP!']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar el registro en la base de datos.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Excepción de MongoDB: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan datos requeridos (nombre, edad).']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
}