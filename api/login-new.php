<?php
require_once 'config.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception("Invalid JSON input");
    }
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        throw new Exception("Username and password are required");
    }
    
    $conn = getDBConnection();
    $user = authenticateUser($conn, $username, $password);
    $conn->close();
    
    sendJSONResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username']
        ]
    ]);
    
} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 401);
}
?>


