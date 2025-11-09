<?php
// Simple color debug test
header('Content-Type: text/plain');

echo "=== COLOR DEBUG TEST ===\n\n";

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "POST request received\n";
    echo "Content-Type: " . $_SERVER['CONTENT_TYPE'] . "\n\n";
    
    // Check all color-related POST data
    $colorFields = ['profileColor', 'textColor', 'socialBgColor', 'socialTextColor'];
    
    foreach ($colorFields as $field) {
        if (isset($_POST[$field])) {
            echo "POST[$field]: " . $_POST[$field] . "\n";
        } else {
            echo "POST[$field]: NOT SET\n";
        }
    }
    
    // Check all POST data
    echo "\nAll POST data:\n";
    foreach ($_POST as $key => $value) {
        if (in_array($key, $colorFields)) {
            echo "  $key: $value\n";
        }
    }
    
} else {
    echo "GET request - showing form\n";
    ?>
    <form method="POST">
        <h3>Test Color Form</h3>
        <div>
            <label>Profile Color:</label>
            <input type="color" name="profileColor" value="#ffffff">
        </div>
        <div>
            <label>Text Color:</label>
            <input type="color" name="textColor" value="#000000">
        </div>
        <div>
            <label>Social BG Color:</label>
            <input type="color" name="socialBgColor" value="#000000">
        </div>
        <div>
            <label>Social Text Color:</label>
            <input type="color" name="socialTextColor" value="#ffffff">
        </div>
        <div>
            <input type="submit" value="Test Submit">
        </div>
    </form>
    <?php
}

echo "\n=== END TEST ===\n";
?>