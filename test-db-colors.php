<?php
// Test script to check color values in database
header('Content-Type: text/plain');

try {
    // Database connection
    $connectDB = new mysqli("localhost", "u743896667_bionrgg", "Godzila#9145", "u743896667_bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }

    echo "=== DATABASE COLOR TEST ===\n\n";

    // Check table structure
    echo "1. Checking table structure:\n";
    $result = $connectDB->query("DESCRIBE users_info");
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['Field'], ['color', 'colorText', 'profileOpacity', 'textOpacity', 'socialBgColor', 'socialTextColor', 'socialOpacity'])) {
            echo "   {$row['Field']}: {$row['Type']} (Default: {$row['Default']})\n";
        }
    }

    // Check current values for a test user
    echo "\n2. Checking current values:\n";
    $testUsername = 'testuser'; // Change this to your test username
    $query = "SELECT username, color, colorText, profileOpacity, textOpacity, socialBgColor, socialTextColor, socialOpacity FROM users_info WHERE username = ?";
    $stmt = $connectDB->prepare($query);
    $stmt->bind_param("s", $testUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "   Username: {$row['username']}\n";
        echo "   color: {$row['color']}\n";
        echo "   colorText: {$row['colorText']}\n";
        echo "   profileOpacity: {$row['profileOpacity']}\n";
        echo "   textOpacity: {$row['textOpacity']}\n";
        echo "   socialBgColor: {$row['socialBgColor']}\n";
        echo "   socialTextColor: {$row['socialTextColor']}\n";
        echo "   socialOpacity: {$row['socialOpacity']}\n";
    } else {
        echo "   No user found with username: $testUsername\n";
    }

    // Test update
    echo "\n3. Testing color update:\n";
    $testColor = '#ffffff';
    $testTextColor = '#000000';
    $updateQuery = "UPDATE users_info SET color = ?, colorText = ? WHERE username = ?";
    $stmt = $connectDB->prepare($updateQuery);
    $stmt->bind_param("sss", $testColor, $testTextColor, $testUsername);
    
    if ($stmt->execute()) {
        echo "   Update successful!\n";
        echo "   Affected rows: " . $stmt->affected_rows . "\n";
        
        // Verify update
        $verifyQuery = "SELECT color, colorText FROM users_info WHERE username = ?";
        $stmt = $connectDB->prepare($verifyQuery);
        $stmt->bind_param("s", $testUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "   Verified - color: {$row['color']}\n";
            echo "   Verified - colorText: {$row['colorText']}\n";
        }
    } else {
        echo "   Update failed: " . $stmt->error . "\n";
    }

    $connectDB->close();
    echo "\n=== TEST COMPLETED ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>