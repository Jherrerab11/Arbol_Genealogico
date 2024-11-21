<?php
header("Content-Type: application/json"); 

// Incluir archivo de conexión a la base de datos
require_once 'config/db.php';

// Obtener el método HTTP (GET, POST, etc.)
$request_method = $_SERVER['REQUEST_METHOD'];

// Verificamos si es un POST request para agregar un nuevo usuario
if ($request_method == 'POST') {
    // Obtener los datos enviados en formato JSON
    $input_data = json_decode(file_get_contents("php://input"), true);

    // Validar que los datos necesarios estén presentes
    if (isset($input_data['nombre']) && isset($input_data['correo']) && isset($input_data['edad'])) {
        // Datos de usuario
        $nombre = $input_data['nombre'];
        $correo = $input_data['correo'];
        $edad = $input_data['edad'];

        // Insertar los datos en la base de datos
        try {
            $query = "INSERT INTO usuarios (nombre, correo, edad) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$nombre, $correo, $edad]);

            // Responder con éxito
            echo json_encode(['message' => 'Usuario agregado exitosamente', 'status' => 201]);
        } catch (PDOException $e) {
            // Manejo de error de base de datos
            echo json_encode(['message' => 'Error al agregar el usuario: ' . $e->getMessage(), 'status' => 500]);
        }
    } else {
        // Si faltan datos
        echo json_encode(['message' => 'Faltan datos necesarios', 'status' => 400]);
    }
} else {
    // Método no permitido
    echo json_encode(['message' => 'Método no permitido', 'status' => 405]);
}
?>
