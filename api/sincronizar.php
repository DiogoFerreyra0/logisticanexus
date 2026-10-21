<?php
// /api/sincronizar.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    echo json_encode(['status' => 'success']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT IGNORE INTO shipments (tracking_number, company_id, current_depot_id, location_name, observations, status, user_id, created_at) VALUES (:tracking, 1, :depot, :location, :obs, 'recibido', :user_id, :fecha)");

    foreach ($data as $pkg) {
        $stmt->execute([
            ':tracking' => $pkg['tracking_number'],
            ':depot' => $pkg['current_depot_id'],
            ':location' => $pkg['location_name'],
            ':obs' => $pkg['observations'],
            ':user_id' => $_SESSION['user_id'],
            ':fecha' => $pkg['fecha']
        ]);
    }
    $pdo->commit();
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al sincronizar']);
}
?>