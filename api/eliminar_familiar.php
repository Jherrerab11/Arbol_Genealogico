<?php
header('Content-Type: application/json');
require_once '../config/db.php'; // Conexión a la base de datos

// Verificar que se pasó el id
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Iniciar transacción para asegurar que ambas eliminaciones se hagan correctamente
        $pdo->beginTransaction();

        // Primero eliminar registros de la tabla familiar_recien_nacido
        $sqlDeleteRecienNacido = "DELETE FROM familiar_recien_nacido WHERE familiar_id = :id";
        $stmtDeleteRecienNacido = $pdo->prepare($sqlDeleteRecienNacido);
        $stmtDeleteRecienNacido->execute([':id' => $id]);

        // Luego eliminar el registro del familiar
        $sqlDeleteFamiliar = "DELETE FROM familiares WHERE id = :id";
        $stmtDeleteFamiliar = $pdo->prepare($sqlDeleteFamiliar);
        $stmtDeleteFamiliar->execute([':id' => $id]);

        // Si ambas operaciones fueron exitosas, confirmar la transacción
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Si ocurre un error, hacer rollback de la transacción
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el familiar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltó el ID del familiar.']);
}
?>
