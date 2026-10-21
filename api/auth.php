<?php
// /api/auth.php
session_start();
require 'conexion.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, role, depot_id FROM users WHERE username = :username AND password = :password LIMIT 1");
    $stmt->execute([':username' => $username, ':password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['depot_id'] = $user['depot_id'];
        echo json_encode(['status' => 'success', 'user' => $user]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos']);
    }
} elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'logged_in', 'user' => ['id' => $_SESSION['user_id'], 'role' => $_SESSION['role'], 'depot_id' => $_SESSION['depot_id']]]);
    } else {
        echo json_encode(['status' => 'not_logged_in']);
    }
}
?>