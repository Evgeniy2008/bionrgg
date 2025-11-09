<?php
require_once 'config.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $conn = getDBConnection();
    ensureCoreSchema($conn);
    
    $stmt = $conn->prepare("SELECT username, descr, views, avatar, color, colorText FROM users_info ORDER BY views DESC LIMIT 5");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $profiles = [];
    while ($row = $result->fetch_assoc()) {
        $profiles[] = [
            'username' => $row['username'],
            'descr' => $row['descr'] ?? '',
            'views' => (int)$row['views'],
            'avatar' => $row['avatar'] ?? null,
            'color' => $row['color'] ?? '#c27eef',
            'colorText' => $row['colorText'] ?? '#ffffff'
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    sendJSONResponse([
        'success' => true,
        'profiles' => $profiles
    ]);
    
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
?>

