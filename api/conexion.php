<?php
// /api/conexion.php
$host = 'localhost'; 
$dbname = 'u511249229_Rupack'; 
$username = 'u511249229_rupackbd'; 
$password = 'Rupack2026!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit;
}
?>