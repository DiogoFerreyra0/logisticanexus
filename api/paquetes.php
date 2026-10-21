<?php
// /api/paquetes.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

// Función para asegurar que la tabla shipments tenga todas las columnas requeridas
function asegurarEstructuraTabla($pdo) {
    $columnas = [
        'recipient_name' => "VARCHAR(150) DEFAULT ''",
        'recipient_phone' => "VARCHAR(50) DEFAULT ''",
        'recipient_address' => "VARCHAR(255) DEFAULT ''",
        'zone_name' => "VARCHAR(100) DEFAULT 'Sin asignar'",
        'driver_name' => "VARCHAR(150) DEFAULT 'Sin asignar'",
        'user_id' => "INT DEFAULT 1"
    ];
    
    foreach ($columnas as $col => $tipo) {
        try {
            $pdo->query("SELECT $col FROM shipments LIMIT 1");
        } catch (Exception $e) {
            try {
                $pdo->exec("ALTER TABLE shipments ADD COLUMN $col $tipo");
            } catch (Exception $ex) {}
        }
    }
}
asegurarEstructuraTabla($pdo);

$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        // Consulta segura utilizando LEFT JOIN opcionales
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
        $paquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $paquetes
        ]);
    } 
    elseif ($action === 'update') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID de paquete faltante']);
            exit;
        }

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
            ':tracking' => trim($input['tracking_number'] ?? ''),
            ':company_id' => !empty($input['company_id']) ? $input['company_id'] : 1,
            ':recipient_name' => trim($input['recipient_name'] ?? ''),
            ':recipient_phone' => trim($input['recipient_phone'] ?? ''),
            ':recipient_address' => trim($input['recipient_address'] ?? ''),
            ':location_name' => trim($input['location_name'] ?? ''),
            ':zone_name' => trim($input['zone_name'] ?? 'Sin asignar'),
            ':driver_name' => trim($input['driver_name'] ?? 'Sin asignar'),
            ':status' => $input['status'] ?? 'recibido',
            ':observations' => trim($input['observations'] ?? ''),
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Paquete actualizado correctamente']);
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