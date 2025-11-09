<?php
// Debug colorText field specifically
header('Content-Type: text/plain');

try {
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }

    echo "=== DEBUG colorText FIELD ===\n\n";

    // Check table structure for colorText
    $query = "DESCRIBE users_info";
    $result = $connectDB->query($query);
    
    echo "1. Table structure for color fields:\n";
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['Field'], ['color', 'colorText'])) {
            echo "   {$row['Field']}: {$row['Type']} (Default: {$row['Default']})\n";
        }
    }

    // Check current values
    echo "\n2. Current values in database:\n";
    $query = "SELECT username, color, colorText FROM users_info LIMIT 5";
    $result = $connectDB->query($query);
    
    while ($row = $result->fetch_assoc()) {
        echo "   Username: {$row['username']}\n";
        echo "   color: '{$row['color']}' (length: " . strlen($row['color']) . ")\n";
        echo "   colorText: '{$row['colorText']}' (length: " . strlen($row['colorText']) . ")\n";
        echo "   ---\n";
    }

    // Test updating colorText specifically
    echo "\n3. Testing colorText update:\n";
    $testUsername = 'testuser';
    $testColorText = '#ffffff';
    
    echo "Trying to set colorText to: '$testColorText'\n";
    
    $updateQuery = "UPDATE users_info SET colorText = ? WHERE username = ?";
    $stmt = $connectDB->prepare($updateQuery);
    $stmt->bind_param("ss", $testColorText, $testUsername);
    
    if ($stmt->execute()) {
        echo "Update executed successfully\n";
        echo "Affected rows: " . $stmt->affected_rows . "\n";
        
        // Check what was actually stored
        $checkQuery = "SELECT colorText FROM users_info WHERE username = ?";
        $stmt = $connectDB->prepare($checkQuery);
        $stmt->bind_param("s", $testUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "Stored colorText: '{$row['colorText']}' (length: " . strlen($row['colorText']) . ")\n";
            
            if ($row['colorText'] !== $testColorText) {
                echo "ERROR: Stored value doesn't match input!\n";
                echo "Expected: '$testColorText'\n";
                echo "Got: '{$row['colorText']}'\n";
            } else {
                echo "SUCCESS: Values match!\n";
            }
        }
    } else {
        echo "Update failed: " . $stmt->error . "\n";
    }

    $connectDB->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>