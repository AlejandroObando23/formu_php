<?php
// Habilitar CORS si es necesario (útil para pruebas locales)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Autoloader de Composer para la librería de MongoDB
require __DIR__ . '/../vendor/autoload.php';

// Obtener la URI desde las variables de entorno de Vercel (o tu entorno local)
$mongoUri = getenv('MONGODB_URI');

if (!$mongoUri) {
    http_response_code(500);
    echo json_encode(['error' => 'La variable de entorno MONGODB_URI no está configurada']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Leer los datos JSON que envía fetch desde el frontend
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (isset($data['nombre']) && isset($data['email']) && isset($data['password'])) {
        $nombre = $data['nombre'];
        $email = $data['email'];
        // Hashear la contraseña por seguridad
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            // Conectar a MongoDB Atlas
            $client = new MongoDB\Client($mongoUri);

            // Seleccionar la base de datos y colección
            // Reemplaza 'mi_base_de_datos' con el nombre de tu base de datos en Atlas
            $collection = $client->mi_base_de_datos->usuarios;

            // Insertar el documento
            $insertOneResult = $collection->insertOne([
                'nombre' => $nombre,
                'email' => $email,
                'password' => $password,
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
        echo json_encode(['error' => 'Faltan datos requeridos (nombre, email o password).']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
}
