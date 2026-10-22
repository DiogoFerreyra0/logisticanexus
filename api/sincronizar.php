<?php
// /api/sincronizar.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$items = json_decode($raw, true);

if (!is_array($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido']);
    exit;
}

$userId = $_SESSION['user_id'] ?? 1;
$depotId = $_SESSION['depot_id'] ?? 1;
$procesados = 0;

try {
    // Cargar mapa de localidades y zonas desde la base de datos
    $stmtLoc = $pdo->query("
        SELECT l.id, l.name as loc_name, dz.name as zone_name 
        FROM locations l 
        LEFT JOIN delivery_zones dz ON l.zone_id = dz.id
    ");
    $mapaLocalidades = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);

    $stmtInsert = $pdo->prepare("
        INSERT INTO shipments (
            company_id, tracking_number, recipient_name, recipient_phone,
            address, recipient_address, location_name, location_id, zone_name,
            current_depot_id, driver_name, status, observations, user_id, created_at
        ) VALUES (
            1, :tracking, :rec_name, :rec_phone,
            :address, :address, :loc_name, :loc_id, :zone_name,
            :depot_id, :driver_name, :status, :obs, :user_id, NOW()
        )
        ON DUPLICATE KEY UPDATE
            location_name = VALUES(location_name),
            zone_name = VALUES(zone_name),
            recipient_address = IF(VALUES(recipient_address) != '', VALUES(recipient_address), recipient_address),
            driver_name = IF(VALUES(driver_name) != 'Sin asignar', VALUES(driver_name), driver_name),
            observations = VALUES(observations),
            updated_at = NOW()
    ");

    foreach ($items as $item) {
        $tracking = trim($item['tracking_number'] ?? '');
        if (empty($tracking)) continue;

        $locName = trim($item['location_name'] ?? 'Revisión manual');
        $zoneName = trim($item['zone_name'] ?? 'Sin asignar');
        $locId = null;

        // Buscar coincidencia en las 53 localidades de la BD para asignar Cordón automático
        foreach ($mapaLocalidades as $row) {
            if (mb_strtolower($row['loc_name'], 'UTF-8') === mb_strtolower($locName, 'UTF-8') ||
                stripos($locName, $row['loc_name']) !== false) {
                $locId = $row['id'];
                $locName = $row['loc_name'];
                $zoneName = $row['zone_name'] ?? 'Sin asignar';
                break;
            }
        }

        $driverName = !empty($item['driver_name']) ? trim($item['driver_name']) : 'Sin asignar';
        $status = ($driverName !== 'Sin asignar') ? 'en_ruta' : 'recibido';

        $stmtInsert->execute([
            ':tracking' => $tracking,
            ':rec_name' => trim($item['recipient_name'] ?? ''),
            ':rec_phone' => trim($item['recipient_phone'] ?? ''),
            ':address' => trim($item['recipient_address'] ?? ''),
            ':loc_name' => $locName,
            ':loc_id' => $locId,
            ':zone_name' => $zoneName,
            ':depot_id' => !empty($item['current_depot_id']) ? (int)$item['current_depot_id'] : $depotId,
            ':driver_name' => $driverName,
            ':status' => $status,
            ':obs' => trim($item['observations'] ?? ''),
            ':user_id' => $userId
        ]);

        $procesados++;
    }

    echo json_encode(['status' => 'success', 'procesados' => $procesados]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>