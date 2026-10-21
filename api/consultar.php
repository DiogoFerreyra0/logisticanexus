<?php
// /api/consultar.php
require 'conexion.php';

header('Content-Type: application/json');

$tracking = isset($_GET['tracking']) ? trim($_GET['tracking']) : '';

if (empty($tracking)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, ingresá un número de seguimiento.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            s.tracking_number,
            c.name as company_name,
            d.name as depot_name,
            s.status,
            s.location_name,
            s.created_at
        FROM shipments s
        LEFT JOIN companies c ON s.company_id = c.id
        LEFT JOIN depots d ON s.current_depot_id = d.id
        WHERE s.tracking_number = :tracking
        LIMIT 1
    ");
    
    $stmt->execute([':tracking' => $tracking]);
    $paquete = $stmt->fetch();

    if ($paquete) {
        echo json_encode(['status' => 'success', 'data' => $paquete]);
    } else {
        echo json_encode(['status' => 'not_found', 'message' => 'No encontramos ningún paquete con ese número de seguimiento.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor.']);
}
?>