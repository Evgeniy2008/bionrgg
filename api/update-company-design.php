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

    if ($username === '' || $password === '') {
        throw new Exception("Username and password are required");
    }

    authenticateUser($conn, $username, $password);

    $companyQuery = "SELECT c.id, c.owner_username FROM users_info ui JOIN companies c ON ui.company_id = c.id WHERE ui.username = ?";
    $stmt = $conn->prepare($companyQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company) {
        throw new Exception("User is not a member of any company");
    }

    if ($company['owner_username'] !== $username) {
        throw new Exception("Only company owner can update company design");
    }

    $conn->close();

    sendJSONResponse([
        'success' => true,
        'message' => 'Company-wide design customization is disabled. Personal profiles remain unchanged.'
    ]);

} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}