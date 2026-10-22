<?php
// /api/conexion.php
$host = 'localhost'; 
$dbname = 'u511249229_Rupack'; 
$username = 'u511249229_rupackbd'; 
$password = 'Rupack2026!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Asegurar Depósito y Empresa base
    $pdo->exec("INSERT IGNORE INTO depots (id, name, address) VALUES (1, 'Depósito Central Benavídez', 'Ruta 9 KM 40')");
    $pdo->exec("INSERT IGNORE INTO companies (id, name) VALUES (1, 'RuPack General')");

    // 2. Asegurar rol 'chofer' y columnas GPS en tabla users
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('operario','supervisor','admin','chofer') DEFAULT 'operario'");
        $pdo->exec("INSERT IGNORE INTO users (id, depot_id, username, password_hash, role) VALUES 
            (1, 1, 'juan.operario', '1234', 'operario'),
            (2, 1, 'pablo.supervisor', '1234', 'supervisor'),
            (3, 1, 'martin.chofer', '1234', 'chofer')
        ");
    } catch (Exception $e) {}

    $colsUsers = [
        'last_lat' => "DECIMAL(10,7) DEFAULT NULL",
        'last_lng' => "DECIMAL(10,7) DEFAULT NULL",
        'last_ping' => "DATETIME DEFAULT NULL"
    ];
    foreach ($colsUsers as $col => $def) {
        try {
            $pdo->query("SELECT $col FROM users LIMIT 1");
        } catch (Exception $e) {
            try { $pdo->exec("ALTER TABLE users ADD COLUMN $col $def"); } catch (Exception $ex) {}
        }
    }

    // 3. Asegurar columnas en shipments
    try {
        $pdo->exec("ALTER TABLE shipments MODIFY COLUMN status ENUM('recibido','clasificado','en_ruta','entregado','revision_pendiente','incidencia') DEFAULT 'recibido'");
    } catch (Exception $e) {}

    $colsShipments = [
        'recipient_address' => "VARCHAR(255) DEFAULT ''",
        'zone_name' => "VARCHAR(100) DEFAULT 'Sin asignar'",
        'driver_name' => "VARCHAR(150) DEFAULT 'Sin asignar'",
        'user_id' => "INT(11) DEFAULT 1",
        'recipient_dni' => "VARCHAR(30) DEFAULT ''",
        'delivered_at' => "DATETIME DEFAULT NULL"
    ];
    foreach ($colsShipments as $col => $def) {
        try {
            $pdo->query("SELECT $col FROM shipments LIMIT 1");
        } catch (Exception $e) {
            try { $pdo->exec("ALTER TABLE shipments ADD COLUMN $col $def"); } catch (Exception $ex) {}
        }
    }

} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión BD: ' . $e->getMessage()]));
}
?>