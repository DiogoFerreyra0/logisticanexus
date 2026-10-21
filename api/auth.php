<?php
// /api/auth.php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

// Capturar los datos enviados por JSON o por POST normal
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
        // Buscar el usuario en la base de datos (manejando posibles diferencias de nombres de columnas)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar contraseña (soporta texto plano o encriptada si ya usaban hash)
            $dbPass = $user['password'] ?? $user['clave'] ?? '';
            
            if ($password === $dbPass || password_verify($password, $dbPass)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'] ?? 'operario';
                $_SESSION['depot_id'] = $user['depot_id'] ?? 1;

                echo json_encode([
                    'status' => 'success', 
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'role' => $user['role'] ?? 'operario',
                        'depot_id' => $user['depot_id'] ?? 1
                    ]
                ]);
                exit;
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos: ' . $e->getMessage()]);
    }
} 
elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'logged_in', 
            'user' => [
                'id' => $_SESSION['user_id'], 
                'role' => $_SESSION['role'] ?? 'operario', 
                'depot_id' => $_SESSION['depot_id'] ?? 1
            ]
        ]);
    } else {
        echo json_encode(['status' => 'not_logged_in']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}
?>