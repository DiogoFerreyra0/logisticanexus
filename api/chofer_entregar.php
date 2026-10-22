<?php
// /api/chofer_entregas.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
$driver = trim($_GET['driver'] ?? ($_SESSION['username'] ?? ''));

try {
    if ($action === 'list') {
        // Muestra los paquetes asignados a este chofer o los que estén listos para salir
        $stmt = $pdo->prepare("
            SELECT * FROM shipments 
            WHERE driver_name LIKE :driver 
               OR driver_name = 'Sin asignar' 
               OR status = 'en_ruta'
               OR status = 'recibido'
            ORDER BY CASE WHEN status = 'en_ruta' THEN 0 ELSE 1 END, id DESC
        ");
        $stmt->execute([':driver' => "%$driver%"]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    elseif ($action === 'deliver') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Falta ID del paquete']);
            exit;
        }

        $nuevoEstado = $input['status'] ?? 'entregado';
        $recName = trim($input['recipient_name'] ?? '');
        $recDni = trim($input['recipient_dni'] ?? '');
        $notaExtra = trim($input['notes'] ?? '');

        $detallePod = ($nuevoEstado === 'entregado') 
            ? " | POD OK - Recibió: $recName (DNI: $recDni) el " . date('d/m H:i')
            : " | INCIDENCIA: $notaExtra (" . date('d/m H:i') . ")";

        $stmt = $pdo->prepare("
            UPDATE shipments SET 
                status = :status,
                recipient_name = IF(:rec_name != '', :rec_name, recipient_name),
                recipient_dni = :rec_dni,
                delivered_at = NOW(),
                observations = CONCAT(COALESCE(observations, ''), :detalle)
            WHERE id = :id
        ");

        $stmt->execute([
            ':status' => $nuevoEstado,
            ':rec_name' => $recName,
            ':rec_dni' => $recDni,
            ':detalle' => $detallePod,
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Actualizado correctamente']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>