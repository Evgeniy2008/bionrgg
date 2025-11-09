<?php
// Check database values
header('Content-Type: text/plain');

try {
    $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
    
    if ($connectDB->connect_error) {
        throw new Exception("Database connection failed: " . $connectDB->connect_error);
    }

    echo "=== DATABASE CHECK ===\n\n";

    // Get all users and their color values
    $query = "SELECT username, color, colorText, profileOpacity, textOpacity, socialBgColor, socialTextColor, socialOpacity FROM users_info ORDER BY username";
    $result = $connectDB->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "Found " . $result->num_rows . " users:\n\n";
        
        while ($row = $result->fetch_assoc()) {
            echo "Username: " . $row['username'] . "\n";
            echo "  color: " . $row['color'] . "\n";
            echo "  colorText: " . $row['colorText'] . "\n";
            echo "  profileOpacity: " . $row['profileOpacity'] . "\n";
            echo "  textOpacity: " . $row['textOpacity'] . "\n";
            echo "  socialBgColor: " . $row['socialBgColor'] . "\n";
            echo "  socialTextColor: " . $row['socialTextColor'] . "\n";
            echo "  socialOpacity: " . $row['socialOpacity'] . "\n";
            echo "  ---\n";
        }
    } else {
        echo "No users found in database\n";
    }

    $connectDB->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>