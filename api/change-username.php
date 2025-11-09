<?php
require_once 'config.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $conn = getDBConnection();
    ensureCoreSchema($conn);

    $userID = trim($_POST['userID'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $newUsername = trim($_POST['newUsername'] ?? '');
    $oldUsername = trim($_POST['oldUsername'] ?? '');

    if ($userID === '' || $password === '' || $newUsername === '' || $oldUsername === '') {
        throw new Exception("All fields are required");
    }

    if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
        throw new Exception("Username must be between 3 and 50 characters");
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
        throw new Exception("Username can only contain letters, numbers, and underscores");
    }

    authenticateUser($conn, $oldUsername, $password);

    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->bind_param("s", $newUsername);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        throw new Exception("Username already exists");
    }
    $stmt->close();

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
        $stmt->bind_param("ss", $newUsername, $oldUsername);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users_info SET username = ? WHERE username = ?");
        $stmt->bind_param("ss", $newUsername, $oldUsername);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE company_members SET username = ? WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $newUsername, $oldUsername);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("UPDATE companies SET owner_username = ? WHERE owner_username = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $newUsername, $oldUsername);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $conn->close();

        sendJSONResponse([
            'success' => true,
            'message' => 'Username changed successfully',
            'newUsername' => $newUsername
        ]);
    } catch (Throwable $transactionError) {
        $conn->rollback();
        $conn->close();
        throw $transactionError;
    }
} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}