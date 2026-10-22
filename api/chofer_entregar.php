<?php
// /api/chofer_entregas.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
$driver = trim($_GET['driver'] ?? ($_SESSION['username'] ?? ''));

try {
    if ($action === 'list') {
        $stmt = $pdo->prepare("
            SELECT * FROM shipments 
            WHERE driver_name LIKE :driver 
               OR driver_name = 'Sin asignar' 
               OR status IN ('en_ruta', 'recibido')
            ORDER BY CASE WHEN driver_name LIKE :driver THEN 0 ELSE 1 END, id DESC
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

        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'update_phone') {
        // Permite al chofer agregar o corregir el teléfono del destinatario desde la calle
        $input = json_decode(file_get_contents('php://input'), true);
        $stmt = $pdo->prepare("UPDATE shipments SET recipient_phone = :phone WHERE id = :id");
        $stmt->execute([
            ':phone' => trim($input['recipient_phone'] ?? ''),
            ':id' => $input['id'] ?? 0
        ]);
        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'ping_gps') {
        // El celular del chofer envía su ubicación GPS en tiempo real
        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? ($_SESSION['username'] ?? ''));
        $lat = $input['lat'] ?? null;
        $lng = $input['lng'] ?? null;

        if ($username && $lat && $lng) {
            $stmt = $pdo->prepare("UPDATE users SET last_lat = :lat, last_lng = :lng, last_ping = NOW() WHERE username = :u");
            $stmt->execute([':lat' => $lat, ':lng' => $lng, ':u' => $username]);
        }
        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'get_drivers_gps') {
        // Exclusivo para el mapa del supervisor: devuelve choferes, su GPS y cuántos paquetes tienen
        $stmt = $pdo->query("
            SELECT 
                u.id, u.username, u.role, u.last_lat, u.last_lng, u.last_ping,
                TIMESTAMPDIFF(SECOND, u.last_ping, NOW()) as seconds_ago,
                (SELECT COUNT(*) FROM shipments s WHERE s.driver_name LIKE CONCAT('%', u.username, '%') AND s.status != 'entregado') as pendientes,
                (SELECT COUNT(*) FROM shipments s WHERE s.driver_name LIKE CONCAT('%', u.username, '%') AND s.status = 'entregado') as entregados
            FROM users u
            WHERE u.role = 'chofer' OR u.last_lat IS NOT NULL
            ORDER BY u.last_ping DESC
        ");
        echo json_encode(['status' => 'success', 'drivers' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    elseif ($action === 'create_user') {
        // Permite al supervisor crear un nuevo chofer u operario desde el panel web
        $input = json_decode(file_get_contents('php://input'), true);
        $u = trim($input['username'] ?? '');
        $p = trim($input['password'] ?? '1234');
        $r = trim($input['role'] ?? 'chofer');

        if (empty($u)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de usuario es obligatorio']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (depot_id, username, password_hash, role) 
            VALUES (1, :u, :p, :r)
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)
        ");
        $stmt->execute([':u' => $u, ':p' => $p, ':r' => $r]);
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>