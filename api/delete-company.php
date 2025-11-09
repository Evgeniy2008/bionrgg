<?php
require_once 'config.php';
require_once 'company-utils.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Invalid input data');
    }

    $username = trim((string)($input['username'] ?? ''));
    $password = (string)($input['password'] ?? '');

    if ($username === '' || $password === '') {
        throw new Exception('Username and password are required');
    }

    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);
    authenticateUser($conn, $username, $password);

    $stmt = $conn->prepare("
        SELECT c.id, c.company_name
        FROM companies c
        WHERE c.owner_username = ?
        LIMIT 1
    ");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company) {
        throw new Exception('You are not the owner of any company');
    }

    $companyId = (int)$company['id'];
    $companyName = $company['company_name'];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("DELETE FROM company_members WHERE company_id = ?");
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM company_designs WHERE company_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $companyId);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("UPDATE users_info SET company_id = NULL, profile_type = 'personal' WHERE company_id = ?");
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->bind_param('i', $companyId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();

        sendJSONResponse([
            'success' => true,
            'message' => "Компанія '{$companyName}' успішно видалена. Усі учасники тепер мають особисті профілі."
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