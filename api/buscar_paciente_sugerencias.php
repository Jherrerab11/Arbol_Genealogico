<?php
header('Content-Type: application/json');
require_once '../config/db.php'; // Suponiendo que aquí tienes tu archivo de conexión a la base de datos

if (isset($_GET['nombre'])) {
    $nombre = $_GET['nombre'];

    // Si el nombre está vacío, trae todos los pacientes
    if (empty($nombre)) {
        // Consulta para obtener todos los pacientes
        $sql = "SELECT * FROM recien_nacidos LIMIT 10"; // Limitar a 10 resultados
        $stmt = $pdo->prepare($sql);
    } else {
        // Consulta para buscar pacientes por nombre
        $sql = "SELECT * FROM recien_nacidos WHERE nombre LIKE :nombre LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nombre' => "%$nombre%"]);
    }

    // Ejecutar la consulta y obtener los resultados
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($pacientes) {
        echo json_encode(['success' => true, 'pacientes' => $pacientes]);
    } else {
        echo json_encode(['success' => false, 'pacientes' => []]);
    }
}
?>
