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
    
    if (empty($username)) {
        throw new Exception("Username is required");
    }
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("UPDATE users_info SET views = views + 1 WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update views");
    }
    
    $stmt->close();
    $conn->close();
    
    sendJSONResponse([
        'success' => true,
        'message' => 'View tracked successfully'
    ]);
    
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}
?>


