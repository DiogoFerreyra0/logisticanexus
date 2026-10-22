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
                COALESCE(c.name, 'RuPack General') as company_name,
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
            echo json_encode(['status' => 'error', 'message' => 'El número de tracking es obligatorio']);
            exit;
        }

        $locName = trim($input['location_name'] ?? 'Revisión manual');
        $zoneName = trim($input['zone_name'] ?? '');

        // Si no puso zona manual, buscar a qué Cordón pertenece la localidad en la BD
        if (empty($zoneName) || $zoneName === 'Sin asignar') {
            $stmtZ = $pdo->prepare("
                SELECT dz.name as zone_name, l.name as loc_name 
                FROM locations l 
                LEFT JOIN delivery_zones dz ON l.zone_id = dz.id 
                WHERE LOWER(l.name) = LOWER(:loc) LIMIT 1
            ");
            $stmtZ->execute([':loc' => $locName]);
            if ($rowZ = $stmtZ->fetch(PDO::FETCH_ASSOC)) {
                $zoneName = $rowZ['zone_name'] ?? 'Sin asignar';
                $locName = $rowZ['loc_name'];
            } else {
                $zoneName = 'Sin asignar';
            }
        }

        $driverName = trim($input['driver_name'] ?? '');
        if (empty($driverName)) $driverName = 'Sin asignar';

        $status = $input['status'] ?? 'recibido';
        $address = trim($input['recipient_address'] ?? '');

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE shipments SET
                    tracking_number = :tracking,
                    recipient_name = :recipient_name,
                    recipient_phone = :recipient_phone,
                    address = :address,
                    recipient_address = :address,
                    location_name = :location_name,
                    zone_name = :zone_name,
                    driver_name = :driver_name,
                    status = :status,
                    observations = :observations
                WHERE id = :id
            ");
            $stmt->execute([
                ':tracking' => $tracking,
                ':recipient_name' => trim($input['recipient_name'] ?? ''),
                ':recipient_phone' => trim($input['recipient_phone'] ?? ''),
                ':address' => $address,
                ':location_name' => $locName,
                ':zone_name' => $zoneName,
                ':driver_name' => $driverName,
                ':status' => $status,
                ':observations' => trim($input['observations'] ?? ''),
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success']);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO shipments (
                    tracking_number, company_id, current_depot_id, recipient_name, 
                    recipient_phone, address, recipient_address, location_name, zone_name, 
                    driver_name, status, observations, user_id, created_at
                ) VALUES (
                    :tracking, 1, 1, :recipient_name, 
                    :recipient_phone, :address, :address, :location_name, :zone_name, 
                    :driver_name, :status, :observations, :user_id, NOW()
                )
                ON DUPLICATE KEY UPDATE
                    recipient_name = VALUES(recipient_name),
                    recipient_phone = VALUES(recipient_phone),
                    address = VALUES(address),
                    recipient_address = VALUES(recipient_address),
                    location_name = VALUES(location_name),
                    zone_name = VALUES(zone_name),
                    driver_name = VALUES(driver_name),
                    status = VALUES(status),
                    observations = VALUES(observations)
            ");
            $stmt->execute([
                ':tracking' => $tracking,
                ':recipient_name' => trim($input['recipient_name'] ?? ''),
                ':recipient_phone' => trim($input['recipient_phone'] ?? ''),
                ':address' => $address,
                ':location_name' => $locName,
                ':zone_name' => $zoneName,
                ':driver_name' => $driverName,
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