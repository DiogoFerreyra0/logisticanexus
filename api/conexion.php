<?php
// /api/conexion.php
$host = 'localhost'; 
$dbname = 'u511249229_Rupack'; 
$username = 'u511249229_rupackbd'; 
$password = 'Rupack2026!'; // Poné esta misma clave en los 3 puntitos de Hostinger

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']));
}
?>