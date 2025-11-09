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
    $companyKey = trim($input['company_key'] ?? '');

    if ($username === '' || $password === '' || $companyKey === '') {
        throw new Exception("Username, password and company key are required");
    }

    authenticateUser($conn, $username, $password);

    $company = joinCompanyByKey($conn, $username, $companyKey);
    $conn->close();

    sendJSONResponse([
        'success' => true,
        'message' => 'Successfully joined company',
        'company' => [
            'company_id' => $company['id'],
            'company_name' => $company['company_name'],
            'company_key' => $company['company_key']
        ]
    ]);

} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}
?>
