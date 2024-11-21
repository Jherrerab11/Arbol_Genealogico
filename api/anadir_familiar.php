<?php
header('Content-Type: application/json');
require_once '../config/db.php'; // Conexión a la base de datos

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['paciente_id']) && isset($data['pariente']) && isset($data['parentesco']) && isset($data['enfermedades'])) {
    $paciente_id = $data['paciente_id'];
    $pariente = $data['pariente'];
    $parentesco = $data['parentesco'];
    $enfermedad = $data['enfermedades'];

    try {
        // Iniciar transacción para asegurar que ambas inserciones se hagan correctamente
        $pdo->beginTransaction();

        // Primero insertar en la tabla de familiares
        $sqlInsertFamiliar = "INSERT INTO familiares (`nombre`, `enfermedades`) VALUES (:pariente, :enfermedad)";
        $stmtInsertFamiliar = $pdo->prepare($sqlInsertFamiliar);
        $stmtInsertFamiliar->execute([':pariente' => $pariente, ':enfermedad' => $enfermedad]);

        // Obtener el ID del familiar insertado
        $familiar_id = $pdo->lastInsertId();

        // Luego insertar en la tabla familiar_recien_nacido
        $sqlInsertRecienNacido = "INSERT INTO familiar_recien_nacido (`familiar_id`, `recien_nacido_id`, `parentesco_id`) VALUES (:familiar_id, :paciente_id, :parentesco_id)";
        $stmtInsertRecienNacido = $pdo->prepare($sqlInsertRecienNacido);
        $stmtInsertRecienNacido->execute([':paciente_id' => $paciente_id, ':familiar_id' => $familiar_id, ':parentesco_id' => $parentesco]);

        // Si ambas operaciones fueron exitosas, confirmar la transacción
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Si ocurre un error, hacer rollback de la transacción
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al insertar el familiar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltaron parámetros para insertar el familiar.']);
}
?>
