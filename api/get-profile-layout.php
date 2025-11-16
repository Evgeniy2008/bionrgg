<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Connect to database
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }
    
    $connectDB->set_charset("utf8mb4");
    
    // Get user ID from session or use test user
    $userId = 1; // For now, use test user ID
    
    // Get layout data
    $getLayout = $connectDB->prepare("SELECT layout_data FROM profile_layouts WHERE user_id = ?");
    $getLayout->bind_param("i", $userId);
    $getLayout->execute();
    $result = $getLayout->get_result();
    
    if ($result->num_rows > 0) {
        $layoutRow = $result->fetch_assoc();
        $layoutData = json_decode($layoutRow['layout_data'], true);
        
        if ($layoutData) {
            echo json_encode([
                'success' => true,
                'layout' => $layoutData,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('Invalid layout data');
        }
    } else {
        // Return default layout
        echo json_encode([
            'success' => true,
            'layout' => [
                'elements' => [],
                'background' => 'gradient',
                'theme' => 'dark',
                'language' => 'en'
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    $connectDB->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>