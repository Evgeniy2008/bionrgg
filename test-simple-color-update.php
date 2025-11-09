<?php
// Simple test for color update only
header('Content-Type: text/plain');

try {
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }

    echo "=== SIMPLE COLOR UPDATE TEST ===\n\n";

    $testUsername = 'testuser';
    $testColor = '#ffffff';
    $testColorText = '#000000';
    
    echo "Testing with:\n";
    echo "  color: '$testColor'\n";
    echo "  colorText: '$testColorText'\n";
    echo "  username: '$testUsername'\n\n";

    // Test 1: Update only color field
    echo "1. Testing color field only:\n";
    $query1 = "UPDATE users_info SET color = ? WHERE username = ?";
    $stmt1 = $connectDB->prepare($query1);
    $stmt1->bind_param("ss", $testColor, $testUsername);
    
    if ($stmt1->execute()) {
        echo "   color update: SUCCESS\n";
    } else {
        echo "   color update: FAILED - " . $stmt1->error . "\n";
    }

    // Test 2: Update only colorText field
    echo "\n2. Testing colorText field only:\n";
    $query2 = "UPDATE users_info SET colorText = ? WHERE username = ?";
    $stmt2 = $connectDB->prepare($query2);
    $stmt2->bind_param("ss", $testColorText, $testUsername);
    
    if ($stmt2->execute()) {
        echo "   colorText update: SUCCESS\n";
    } else {
        echo "   colorText update: FAILED - " . $stmt2->error . "\n";
    }

    // Test 3: Update both fields together
    echo "\n3. Testing both fields together:\n";
    $query3 = "UPDATE users_info SET color = ?, colorText = ? WHERE username = ?";
    $stmt3 = $connectDB->prepare($query3);
    $stmt3->bind_param("sss", $testColor, $testColorText, $testUsername);
    
    if ($stmt3->execute()) {
        echo "   both fields update: SUCCESS\n";
    } else {
        echo "   both fields update: FAILED - " . $stmt3->error . "\n";
    }

    // Check final values
    echo "\n4. Final values in database:\n";
    $checkQuery = "SELECT color, colorText FROM users_info WHERE username = ?";
    $stmt = $connectDB->prepare($checkQuery);
    $stmt->bind_param("s", $testUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo "   color: '{$row['color']}' (length: " . strlen($row['color']) . ")\n";
        echo "   colorText: '{$row['colorText']}' (length: " . strlen($row['colorText']) . ")\n";
    }

    $connectDB->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>