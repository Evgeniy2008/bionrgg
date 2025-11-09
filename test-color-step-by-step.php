<?php
// Step by step color update test
header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $connectDB = new mysqli("localhost", "root", "root", "bionrgg");
        
        if ($connectDB->connect_error) {
            throw new Exception("Database connection failed: " . $connectDB->connect_error);
        }

        echo "=== STEP BY STEP COLOR UPDATE ===\n\n";

        $username = $_POST['username'] ?? 'testuser';
        $profileColor = $_POST['profileColor'] ?? '#ffffff';
        $textColor = $_POST['textColor'] ?? '#000000';
        
        echo "Input values:\n";
        echo "  username: '$username'\n";
        echo "  profileColor: '$profileColor' (length: " . strlen($profileColor) . ")\n";
        echo "  textColor: '$textColor' (length: " . strlen($textColor) . ")\n\n";

        // Step 1: Check current values
        echo "Step 1: Current values in database:\n";
        $checkQuery = "SELECT color, colorText FROM users_info WHERE username = ?";
        $stmt = $connectDB->prepare($checkQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "  color: '{$row['color']}' (length: " . strlen($row['color']) . ")\n";
            echo "  colorText: '{$row['colorText']}' (length: " . strlen($row['colorText']) . ")\n";
        } else {
            echo "  User not found!\n";
            $connectDB->close();
            exit;
        }

        // Step 2: Update using the same query as in update-profile.php
        echo "\nStep 2: Updating with main query...\n";
        
        // Simulate the exact same query from update-profile.php
        $query = "UPDATE users_info SET 
            color = ?, colorText = ?
            WHERE username = ?";
        
        $stmt = $connectDB->prepare($query);
        if (!$stmt) {
            echo "  ERROR: Failed to prepare statement: " . $connectDB->error . "\n";
        } else {
            $result = $stmt->bind_param("sss", $profileColor, $textColor, $username);
            if (!$result) {
                echo "  ERROR: Failed to bind parameters: " . $stmt->error . "\n";
            } else {
                if ($stmt->execute()) {
                    echo "  SUCCESS: Query executed\n";
                    echo "  Affected rows: " . $stmt->affected_rows . "\n";
                } else {
                    echo "  ERROR: Query execution failed: " . $stmt->error . "\n";
                }
            }
        }

        // Step 3: Check values after update
        echo "\nStep 3: Values after update:\n";
        $checkQuery = "SELECT color, colorText FROM users_info WHERE username = ?";
        $stmt = $connectDB->prepare($checkQuery);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "  color: '{$row['color']}' (length: " . strlen($row['color']) . ")\n";
            echo "  colorText: '{$row['colorText']}' (length: " . strlen($row['colorText']) . ")\n";
            
            // Check if values match
            if ($row['color'] === $profileColor) {
                echo "  ✓ color matches input\n";
            } else {
                echo "  ✗ color does NOT match input!\n";
                echo "    Expected: '$profileColor'\n";
                echo "    Got: '{$row['color']}'\n";
            }
            
            if ($row['colorText'] === $textColor) {
                echo "  ✓ colorText matches input\n";
            } else {
                echo "  ✗ colorText does NOT match input!\n";
                echo "    Expected: '$textColor'\n";
                echo "    Got: '{$row['colorText']}'\n";
            }
        }

        $connectDB->close();

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    ?>
    <form method="POST">
        <h3>Test Color Update</h3>
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="testuser">
        </div>
        <div>
            <label>Profile Color:</label>
            <input type="color" name="profileColor" value="#ffffff">
        </div>
        <div>
            <label>Text Color:</label>
            <input type="color" name="textColor" value="#000000">
        </div>
        <div>
            <input type="submit" value="Test Update">
        </div>
    </form>
    <?php
}
?>