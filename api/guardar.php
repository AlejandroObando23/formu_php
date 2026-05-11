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

    // Validar requeridos básicos
    if (isset($data['name']) && isset($data['stage_name']) && isset($data['genre']) && isset($data['debut_year'])) {
        $name = $data['name'];
        $stage_name = $data['stage_name'];
        $genre = $data['genre'];
        $debut_year = $data['debut_year'];
        $albums = $data['albums'] ?? 0;
        $listeners = $data['listeners'] ?? 0;
        $awards = $data['awards'] ?? 0;
        $country = $data['country'] ?? '';

        try {
            $client = new MongoDB\Client($mongoUri);
            // Usamos la colección 'cantantes'
            $collection = $client->mi_base_de_datos->cantantes;

            $insertOneResult = $collection->insertOne([
                'name' => $name,
                'stage_name' => $stage_name,
                'genre' => $genre,
                'debut_year' => $debut_year,
                'albums' => $albums,
                'listeners' => $listeners,
                'awards' => $awards,
                'country' => $country,
                'fecha_registro' => new MongoDB\BSON\UTCDateTime()
            ]);

            if ($insertOneResult->getInsertedCount() == 1) {
                echo json_encode(['message' => '¡Estrella registrada exitosamente en la galaxia musical!']);
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
        echo json_encode(['error' => 'Faltan datos requeridos (nombre, nombre artístico, etc).']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
}