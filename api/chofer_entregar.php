<?php
// /api/chofer_entregas.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$driver = $_GET['driver'] ?? ($_SESSION['username'] ?? '');

try {
    if ($action === 'list') {
        // Traer los paquetes asignados a este chofer (o todos si es supervisor/admin de prueba)
        if (strtolower($_SESSION['role'] ?? '') === 'supervisor' || strtolower($_SESSION['role'] ?? '') === 'admin') {
            $stmt = $pdo->query("SELECT * FROM shipments ORDER BY id DESC");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM shipments WHERE driver_name LIKE :driver OR driver_name = 'Sin asignar' ORDER BY id DESC");
            $stmt->execute([':driver' => "%$driver%"]);
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    } 
    elseif ($action === 'deliver') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID de paquete faltante']);
            exit;
        }

        // Actualizar el paquete a entregado y guardar datos del receptor en observaciones o campos
        $stmt = $pdo->prepare("
            UPDATE shipments SET 
                status = 'entregado',
                recipient_name = :rec_name,
                observations = CONCAT(COALESCE(observations, ''), ' | Entregado a DNI: ', :rec_dni, ' (POD OK)')
            WHERE id = :id
        ");

        $stmt->execute([
            ':rec_name' => trim($input['recipient_name'] ?? ''),
            ':rec_dni' => trim($input['recipient_dni'] ?? ''),
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Entrega confirmada con éxito']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>