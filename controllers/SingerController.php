<?php

require_once __DIR__ . '/../models/Singer.php';

class SingerController
{
    public function store()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (
            isset($data['name']) && 
            isset($data['stage_name']) && 
            isset($data['genre']) && 
            isset($data['debut_year'])
        ) {
            try {
                $singer = new Singer();
                $singer->name = $data['name'];
                $singer->stage_name = $data['stage_name'];
                $singer->genre = $data['genre'];
                $singer->debut_year = $data['debut_year'];
                $singer->albums = $data['albums'] ?? 0;
                $singer->listeners = $data['listeners'] ?? 0;
                $singer->awards = $data['awards'] ?? 0;
                $singer->country = $data['country'] ?? '';
                $singer->fecha_registro = new MongoDB\BSON\UTCDateTime();

                if ($singer->save()) {
                    echo json_encode(['message' => 'Estrella registrada exitosamente en la galaxia musical!']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al guardar el registro.']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Excepción ORM: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos requeridos.']);
        }
    }

    public function index()
    {
        try {
            $singers = Singer::orderBy('fecha_registro', 'desc')->get();
            $result = [];
            
            foreach ($singers as $singer) {
                $date = '';
                if (isset($singer->fecha_registro)) {
                    if ($singer->fecha_registro instanceof MongoDB\BSON\UTCDateTime) {
                        $date = $singer->fecha_registro->toDateTime()->format('Y-m-d H:i:s');
                    } else {
                        $date = $singer->fecha_registro;
                    }
                }
                
                $result[] = [
                    '_id' => (string) $singer->_id,
                    'name' => $singer->name ?? '',
                    'stage_name' => $singer->stage_name ?? '',
                    'genre' => $singer->genre ?? '',
                    'debut_year' => $singer->debut_year ?? '',
                    'albums' => $singer->albums ?? '',
                    'listeners' => $singer->listeners ?? '',
                    'awards' => $singer->awards ?? '',
                    'country' => $singer->country ?? '',
                    'fecha_registro' => $date
                ];
            }
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Excepción ORM: ' . $e->getMessage()]);
        }
    }

    public function destroy()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (isset($data['id'])) {
            try {
                $singer = Singer::find($data['id']);
                
                if ($singer && $singer->delete()) {
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
    }
}
