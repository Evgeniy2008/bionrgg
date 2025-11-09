<?php
require_once 'config.php';
require_once 'company-utils.php';

function isStoredFilePath(?string $value): bool {
    if (!$value || !is_string($value)) {
        return false;
    }
    $normalized = ltrim($value, "/\\");
    return (strpos($normalized, 'uploads/profile/') === 0) || (strpos($normalized, 'uploads\\profile\\') === 0) ||
           (strpos($normalized, 'uploads/company/') === 0) || (strpos($normalized, 'uploads\\company\\') === 0);
}

function loadMediaContent(?string $value): ?string {
    if (!$value) {
        return null;
    }

    if (isStoredFilePath($value)) {
        $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value);
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            return null;
        }

        $binary = file_get_contents($absolutePath);
        if ($binary === false) {
            return null;
        }

        return base64_encode($binary);
    }

    return $value;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $username = trim((string)($_GET['username'] ?? ''));
    $password = (string)($_GET['password'] ?? '');

    if ($username === '' || $password === '') {
        throw new Exception('Username and password are required');
    }

    $conn = getDBConnection();
    ensureCompanySchema($conn);
    $user = authenticateUser($conn, $username, $password);

    $stmt = $conn->prepare("SELECT company_id, profile_type FROM users_info WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$profile || !$profile['company_id']) {
        $conn->close();
        sendJSONResponse([
            'success' => true,
            'company' => null
        ]);
    }

    $companyQuery = "
        SELECT c.id,
               c.company_key,
               c.company_name,
               c.owner_username,
               c.unified_design_enabled,
               cm.role
        FROM companies c
        JOIN company_members cm ON c.id = cm.company_id
        WHERE c.id = ? AND cm.username = ?
    ";
    $stmt = $conn->prepare($companyQuery);
    $stmt->bind_param('is', $profile['company_id'], $username);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company) {
        $conn->close();
        sendJSONResponse([
            'success' => true,
            'company' => null
        ]);
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM company_members WHERE company_id = ?");
    $stmt->bind_param('i', $profile['company_id']);
    $stmt->execute();
    $membersCount = (int)$stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    $members = [];
    $membersStmt = $conn->prepare("
        SELECT cm.username, cm.role, ui.company_tagline
        FROM company_members cm
        LEFT JOIN users_info ui ON ui.username = cm.username
        WHERE cm.company_id = ?
        ORDER BY CASE WHEN cm.role = 'owner' THEN 0 ELSE 1 END, cm.username
    ");
    if ($membersStmt) {
        $membersStmt->bind_param('i', $profile['company_id']);
        $membersStmt->execute();
        $membersResult = $membersStmt->get_result();
        while ($memberRow = $membersResult->fetch_assoc()) {
            $members[] = [
                'username' => $memberRow['username'],
                'role' => $memberRow['role'],
                'company_tagline' => $memberRow['company_tagline']
            ];
        }
        $membersStmt->close();
    }

    $conn->close();

    sendJSONResponse([
        'success' => true,
        'company' => [
            'id' => (int)$company['id'],
            'company_key' => $company['company_key'],
            'company_name' => $company['company_name'],
            'owner_username' => $company['owner_username'],
            'unified_design_enabled' => false,
            'role' => $company['role'],
            'members_count' => $membersCount,
            'design' => null,
            'members' => $members
        ]
    ]);
} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}