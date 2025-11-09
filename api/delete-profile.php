<?php
require_once 'config.php';
require_once 'company-utils.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        throw new Exception('Invalid JSON input');
    }

    $username = trim((string)($payload['username'] ?? ''));
    $password = (string)($payload['password'] ?? '');

    if ($username === '' || $password === '') {
        throw new Exception('Username and password are required');
    }

    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);

    $user = authenticateUser($conn, $username, $password);

    $conn->begin_transaction();

    try {
        // Remove any layout data explicitly (safety for legacy databases)
        $layoutStmt = $conn->prepare("DELETE FROM profile_layouts WHERE user_id = ?");
        if ($layoutStmt) {
            $userId = (int)$user['id'];
            $layoutStmt->bind_param('i', $userId);
            $layoutStmt->execute();
            $layoutStmt->close();
        }

        // Delete main user record (cascades handle users_info, company_members, etc.)
        $deleteUserStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $userId = (int)$user['id'];
        $deleteUserStmt->bind_param('i', $userId);

        if (!$deleteUserStmt->execute()) {
            throw new Exception('Failed to delete user account');
        }
        $deleteUserStmt->close();

        $conn->commit();
        $conn->close();

        sendJSONResponse([
            'success' => true,
            'message' => 'Profile deleted successfully'
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