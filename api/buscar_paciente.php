<?php
header('Content-Type: application/json');
require_once '../config/db.php'; // Asegúrate de incluir la conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar la consulta para buscar el paciente (sin usar LIKE, ya que estamos buscando por id exacto)
    $sqlPaciente = "SELECT * FROM recien_nacidos WHERE id = :id";
    $stmtPaciente = $pdo->prepare($sqlPaciente);
    $stmtPaciente->execute([':id' => $id]);  // No es necesario el símbolo de % al buscar por id exacto
    $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);

    if ($paciente) {
        // Preparar la consulta para obtener los familiares del paciente
        // Utilizamos GROUP_CONCAT para concatenar las enfermedades separadas por comas
        $sqlFamiliares = "
            SELECT f.id, f.nombre, p.descripcion AS parentesco, GROUP_CONCAT(f.enfermedades SEPARATOR ', ') AS enfermedades
            FROM familiares f
            JOIN familiar_recien_nacido frn ON f.id = frn.familiar_id
            JOIN recien_nacidos rn ON frn.recien_nacido_id = rn.id
            JOIN parentescos p ON frn.parentesco_id = p.id
            WHERE rn.id = :paciente_id
            GROUP BY f.id, f.nombre, p.descripcion
        ";

        $stmtFamiliares = $pdo->prepare($sqlFamiliares);
        $stmtFamiliares->execute([':paciente_id' => $paciente['id']]);
        $familiares = $stmtFamiliares->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el nombre del padre
        $sqlPadre = "
            SELECT f.nombre AS padre
            FROM `recien_nacidos` rn
            LEFT JOIN familiar_recien_nacido frn ON frn.recien_nacido_id = rn.id
            LEFT JOIN familiares f ON f.id = frn.familiar_id
            WHERE frn.parentesco_id = '1'
            AND rn.id = :paciente_id
        ";

        $stmtPadre = $pdo->prepare($sqlPadre);
        $stmtPadre->execute([':paciente_id' => $paciente['id']]);
        $padre = $stmtPadre->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra el padre, asignar "No registra"
        if (!$padre) {
            $padre = ['padre' => 'No registra'];
        }

        // Obtener el nombre de la madre
        $sqlMadre = "
            SELECT f.nombre AS madre
            FROM `recien_nacidos` rn
            LEFT JOIN familiar_recien_nacido frn ON frn.recien_nacido_id = rn.id
            LEFT JOIN familiares f ON f.id = frn.familiar_id
            WHERE frn.parentesco_id = '2'
            AND rn.id = :paciente_id
        ";

        $stmtMadre = $pdo->prepare($sqlMadre);
        $stmtMadre->execute([':paciente_id' => $paciente['id']]);
        $madre = $stmtMadre->fetch(PDO::FETCH_ASSOC);

        // Si no se encuentra la madre, asignar "No registra"
        if (!$madre) {
            $madre = ['madre' => 'No registra'];
        }

        // Obtener lugar de nacimiento (nombre del hospital) y dirección
        $sqlLugarNacimiento = "
            SELECT h.nombre AS lugar_nacimiento, h.direccion
            FROM `recien_nacidos` rn
            LEFT JOIN hospitales h ON h.id = rn.hospital_id
            WHERE rn.id = :paciente_id
        ";

        $stmtLugarNacimiento = $pdo->prepare($sqlLugarNacimiento);
        $stmtLugarNacimiento->execute([':paciente_id' => $paciente['id']]);
        $lugarNacimiento = $stmtLugarNacimiento->fetch(PDO::FETCH_ASSOC);

        // Crear una cadena de enfermedades familiares concatenadas, separadas por comas
        $enfermedades_familiares = '';
        foreach ($familiares as $familiar) {
            if ($familiar['enfermedades']) {
                $enfermedades_familiares .= $familiar['enfermedades'] . ', ';
            }
        }

        // Eliminar la última coma y espacio, si existen
        $enfermedades_familiares = rtrim($enfermedades_familiares, ', ');

        // Devolver la respuesta con los datos del paciente, sus familiares, los nombres del padre y madre, y lugar_nacimiento y direccion
        // Devolver la respuesta
        echo json_encode([
            'success' => true,
            'paciente' => [
                'nombre' => $paciente['nombre'],
                'id' => $paciente['id'],
                'fecha_nacimiento' => $paciente['fecha_nacimiento'],
                'cuna' => $paciente['cuna'],
                'padre' => $padre['padre'],  // Nombre del padre
                'madre' => $madre['madre'],  // Nombre de la madre
                'enfermedades_familiares' => $enfermedades_familiares,  // Enfermedades concatenadas
                'lugar_nacimiento' => $lugarNacimiento['lugar_nacimiento'],  // Lugar de nacimiento
                'direccion' => $lugarNacimiento['direccion'],  // Dirección del hospital
            ],
            'familiares' => $familiares
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Paciente no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de paciente no proporcionado']);
}
