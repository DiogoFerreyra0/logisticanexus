<?php
// /api/auth.php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = trim($input['username'] ?? $_POST['username'] ?? '');
    $password = trim($input['password'] ?? $_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Completá usuario y contraseña']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $dbPass = $user['password_hash'] ?? $user['password'] ?? '';
            if ($password === $dbPass || password_verify($password, $dbPass)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = strtolower($user['role'] ?? 'operario');
                $_SESSION['depot_id'] = $user['depot_id'] ?? 1;

                echo json_encode([
                    'status' => 'success',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $_SESSION['role'],
                        'depot_id' => $_SESSION['depot_id']
                    ]
                ]);
                exit;
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
    }
} elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'logged_in',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? 'usuario',
                'role' => $_SESSION['role'] ?? 'operario',
                'depot_id' => $_SESSION['depot_id'] ?? 1
            ]
        ]);
    } else {
        echo json_encode(['status' => 'not_logged_in']);
    }
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}
?>