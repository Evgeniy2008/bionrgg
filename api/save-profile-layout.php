<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (!isset($input['elements']) || !isset($input['background']) || !isset($input['theme'])) {
        throw new Exception('Missing required fields');
    }
    
    // Connect to database
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }
    
    $connectDB->set_charset("utf8mb4");
    
    // Get user ID from session or URL parameter
    $userId = null;
    
    // Check if user is logged in via session or localStorage (passed via header)
    if (isset($_SERVER['HTTP_X_USER_ID'])) {
        $userId = $_SERVER['HTTP_X_USER_ID'];
    } elseif (isset($_GET['user'])) {
        $userId = $_GET['user'];
    } else {
        throw new Exception('User not authenticated');
    }
    
    // Check if user exists
    $checkUser = $connectDB->prepare("SELECT username FROM users WHERE username = ?");
    $checkUser->bind_param("s", $userId);
    $checkUser->execute();
    $userResult = $checkUser->get_result();
    
    if ($userResult->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    // Prepare layout data
    $layoutData = json_encode([
        'elements' => $input['elements'],
        'background' => $input['background'],
        'theme' => $input['theme'],
        'language' => $input['language'] ?? 'en',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Check if layout exists
    $checkLayout = $connectDB->prepare("SELECT id FROM profile_layouts WHERE user_id = ?");
    $checkLayout->bind_param("i", $userId);
    $checkLayout->execute();
    $layoutResult = $checkLayout->get_result();
    
    if ($layoutResult->num_rows > 0) {
        // Update existing layout
        $updateLayout = $connectDB->prepare("UPDATE profile_layouts SET layout_data = ?, updated_at = NOW() WHERE user_id = ?");
        $updateLayout->bind_param("si", $layoutData, $userId);
        
        if (!$updateLayout->execute()) {
            throw new Exception('Failed to update layout: ' . $updateLayout->error);
        }
    } else {
        // Create new layout
        $createLayout = $connectDB->prepare("INSERT INTO profile_layouts (user_id, layout_data, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $createLayout->bind_param("is", $userId, $layoutData);
        
        if (!$createLayout->execute()) {
            throw new Exception('Failed to create layout: ' . $createLayout->error);
        }
    }
    
    $connectDB->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile layout saved successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>