<?php
// Check column sizes in database
header('Content-Type: text/plain');

try {
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }

    echo "=== COLUMN SIZE CHECK ===\n\n";

    // Check the structure of users_info table
    $query = "DESCRIBE users_info";
    $result = $connectDB->query($query);
    
    if ($result) {
        echo "Table structure:\n";
        while ($row = $result->fetch_assoc()) {
            if (in_array($row['Field'], ['color', 'colorText', 'socialBgColor', 'socialTextColor'])) {
                echo "  {$row['Field']}: {$row['Type']} (Default: {$row['Default']})\n";
            }
        }
    }

    // Test inserting a long color value
    echo "\n=== TESTING COLOR INSERT ===\n";
    
    $testUsername = 'testuser';
    $testColor = '#ffffff'; // 7 characters
    $testLongColor = '#1234567'; // 8 characters - should be truncated
    
    echo "Testing with color: $testColor (length: " . strlen($testColor) . ")\n";
    echo "Testing with long color: $testLongColor (length: " . strlen($testLongColor) . ")\n";
    
    // Try to update with the test color
    $updateQuery = "UPDATE users_info SET color = ? WHERE username = ?";
    $stmt = $connectDB->prepare($updateQuery);
    $stmt->bind_param("ss", $testColor, $testUsername);
    
    if ($stmt->execute()) {
        echo "Update successful\n";
        
        // Check what was actually stored
        $checkQuery = "SELECT color FROM users_info WHERE username = ?";
        $stmt = $connectDB->prepare($checkQuery);
        $stmt->bind_param("s", $testUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "Stored color: '{$row['color']}' (length: " . strlen($row['color']) . ")\n";
        }
    } else {
        echo "Update failed: " . $stmt->error . "\n";
    }

    $connectDB->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>