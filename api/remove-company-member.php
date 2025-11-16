<?php
require_once 'config.php';
require_once 'company-utils.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new Exception('Invalid JSON input');
    }

    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');
    $memberUsername = trim($input['memberUsername'] ?? '');

    if ($username === '' || $password === '' || $memberUsername === '') {
        throw new Exception('Username, password and memberUsername are required');
    }

    if (strcasecmp($username, $memberUsername) === 0) {
        throw new Exception('Неможливо видалити власний обліковий запис');
    }

    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);

    authenticateUser($conn, $username, $password);

    $ownerStmt = $conn->prepare("SELECT ui.company_id, c.owner_username FROM users_info ui JOIN companies c ON ui.company_id = c.id WHERE ui.username = ?");
    if (!$ownerStmt) {
        throw new Exception('Failed to prepare owner lookup: ' . $conn->error);
    }
    $ownerStmt->bind_param('s', $username);
    $ownerStmt->execute();
    $ownerResult = $ownerStmt->get_result()->fetch_assoc();
    $ownerStmt->close();

    if (!$ownerResult || !$ownerResult['company_id']) {
        throw new Exception('Користувач не прив’язаний до компанії');
    }

    if ($ownerResult['owner_username'] !== $username) {
        throw new Exception('Тільки власник компанії може видаляти учасників');
    }

    $companyId = (int)$ownerResult['company_id'];

    $memberStmt = $conn->prepare("SELECT cm.id FROM company_members cm WHERE cm.company_id = ? AND cm.username = ?");
    if (!$memberStmt) {
        throw new Exception('Failed to prepare member lookup: ' . $conn->error);
    }
    $memberStmt->bind_param('is', $companyId, $memberUsername);
    $memberStmt->execute();
    $memberExists = $memberStmt->get_result()->fetch_assoc();
    $memberStmt->close();

    if (!$memberExists) {
        throw new Exception('Користувач не є учасником цієї компанії');
    }

    $conn->begin_transaction();

    $deleteStmt = $conn->prepare("DELETE FROM company_members WHERE company_id = ? AND username = ?");
    if (!$deleteStmt) {
        throw new Exception('Failed to prepare member removal: ' . $conn->error);
    }
    $deleteStmt->bind_param('is', $companyId, $memberUsername);
    if (!$deleteStmt->execute()) {
        throw new Exception('Не вдалося видалити учасника: ' . $deleteStmt->error);
    }
    $deleteStmt->close();

    $updateUserStmt = $conn->prepare("UPDATE users_info SET company_id = NULL, profile_type = 'personal', company_display_name = NULL, company_logo = NULL, company_show_logo = 0, company_show_name = 0, company_tagline = NULL WHERE username = ?");
    if (!$updateUserStmt) {
        throw new Exception('Failed to prepare user update: ' . $conn->error);
    }
    $updateUserStmt->bind_param('s', $memberUsername);
    if (!$updateUserStmt->execute()) {
        throw new Exception('Не вдалося оновити профіль користувача: ' . $updateUserStmt->error);
    }
    $updateUserStmt->close();

    $conn->commit();
    $conn->close();

    sendJSONResponse([
        'success' => true,
        'message' => 'Учасника видалено з компанії'
    ]);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->rollback();
        $conn->close();
    }

    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}

















