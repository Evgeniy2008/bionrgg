<?php
require_once 'config.php';
require_once 'company-utils.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Invalid JSON input');
    }
    
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $companyName = isset($input['company_name']) ? mb_substr(trim($input['company_name']), 0, 255) : null;

    if ($username === '' || $password === '') {
        throw new Exception("Username and password are required");
    }

    authenticateUser($conn, $username, $password);

    $userQuery = "SELECT company_id FROM users_info WHERE username = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !$user['company_id']) {
        throw new Exception("User is not a member of any company");
    }

    $companyQuery = "SELECT owner_username FROM companies WHERE id = ?";
    $stmt = $conn->prepare($companyQuery);
    $stmt->bind_param("i", $user['company_id']);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company || $company['owner_username'] !== $username) {
        throw new Exception("Only company owner can update settings");
    }

    if ($companyName !== null && $companyName !== '') {
        $updateNameStmt = $conn->prepare("UPDATE companies SET company_name = ? WHERE id = ?");
        if (!$updateNameStmt) {
            throw new Exception("Failed to prepare company name update: " . $conn->error);
        }
        $updateNameStmt->bind_param('si', $companyName, $user['company_id']);
        if (!$updateNameStmt->execute()) {
            throw new Exception("Failed to update company name: " . $updateNameStmt->error);
        }
        $updateNameStmt->close();
    }

    // Unified design is deprecated; force it to stay disabled.
    $disableDesignStmt = $conn->prepare("UPDATE companies SET unified_design_enabled = 0 WHERE id = ? AND unified_design_enabled <> 0");
    if ($disableDesignStmt) {
        $disableDesignStmt->bind_param('i', $user['company_id']);
        $disableDesignStmt->execute();
        $disableDesignStmt->close();
    }

    $conn->close();

    sendJSONResponse([
        'success' => true,
        'message' => 'Company settings updated successfully'
    ]);

} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}