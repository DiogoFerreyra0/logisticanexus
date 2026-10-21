<?php
// /api/paquetes.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT 
                s.*,
                COALESCE(c.name, 'RuPack / Cliente') as company_name,
                COALESCE(d.name, 'Depósito Central') as depot_name,
                COALESCE(u.username, 'Operario') as operator_name
            FROM shipments s
            LEFT JOIN companies c ON s.company_id = c.id
            LEFT JOIN depots d ON s.current_depot_id = d.id
            LEFT JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC, s.id DESC
        ");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } 
    elseif ($action === 'create' || $action === 'update') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $tracking = trim($input['tracking_number'] ?? '');

        if (empty($tracking)) {
            echo json_encode(['status' => 'error', 'message' => 'El tracking es obligatorio']);
            exit;
        }

        // Asegurar que la empresa ID 1 exista
        $companyId = !empty($input['company_id']) ? $input['company_id'] : 1;
        $checkComp = $pdo->prepare("SELECT id FROM companies WHERE id = ? LIMIT 1");
        $checkComp->execute([$companyId]);
        if (!$checkComp->fetch()) {
            $pdo->exec("INSERT INTO companies (id, name) VALUES (1, 'RuPack General') ON DUPLICATE KEY UPDATE id=id");
            $companyId = 1;
        }

        $status = $input['status'] ?? 'recibido';

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE shipments SET
                    tracking_number = :tracking,
                    company_id = :company_id,
                    recipient_name = :recipient_name,
                    recipient_phone = :recipient_phone,
                    recipient_address = :recipient_address,
                    location_name = :location_name,
                    zone_name = :zone_name,
                    driver_name = :driver_name,
                    status = :status,
                    observations = :observations
                WHERE id = :id
            ");
            $stmt->execute([
                ':tracking' => $tracking,
                ':company_id' => $companyId,
                ':recipient_name' => trim($input['recipient_name'] ?? ''),
                ':recipient_phone' => trim($input['recipient_phone'] ?? ''),
                ':recipient_address' => trim($input['recipient_address'] ?? ''),
                ':location_name' => trim($input['location_name'] ?? 'Revisión manual'),
                ':zone_name' => trim($input['zone_name'] ?? 'Sin asignar'),
                ':driver_name' => trim($input['driver_name'] ?? 'Sin asignar'),
                ':status' => $status,
                ':observations' => trim($input['observations'] ?? ''),
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO shipments (
                    tracking_number, company_id, current_depot_id, recipient_name, 
                    recipient_phone, recipient_address, location_name, zone_name, 
                    driver_name, status, observations, user_id, created_at
                ) VALUES (
                    :tracking, :company_id, :depot_id, :recipient_name, 
                    :recipient_phone, :recipient_address, :location_name, :zone_name, 
                    :driver_name, :status, :observations, :user_id, NOW()
                )
            ");
            $stmt->execute([
                ':tracking' => $tracking,
                ':company_id' => $companyId,
                ':depot_id' => !empty($input['current_depot_id']) ? $input['current_depot_id'] : 1,
                ':recipient_name' => trim($input['recipient_name'] ?? ''),
                ':recipient_phone' => trim($input['recipient_phone'] ?? ''),
                ':recipient_address' => trim($input['recipient_address'] ?? ''),
                ':location_name' => trim($input['location_name'] ?? 'Revisión manual'),
                ':zone_name' => trim($input['zone_name'] ?? 'Sin asignar'),
                ':driver_name' => trim($input['driver_name'] ?? 'Sin asignar'),
                ':status' => $status,
                ':observations' => trim($input['observations'] ?? ''),
                ':user_id' => $_SESSION['user_id'] ?? 1
            ]);
            echo json_encode(['status' => 'success']);
        }
    }
    elseif ($action === 'delete') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM shipments WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>