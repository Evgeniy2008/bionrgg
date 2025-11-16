<?php
require_once 'config.php';
require_once 'company-utils.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

function sanitizeColor(string $color, string $fallback = '#FFFFFF'): string {
    $color = trim($color);
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        return strtoupper($fallback);
    }
    return strtoupper($color);
}

try {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $description = trim((string)($_POST['description'] ?? ''));
    $profileType = strtolower(trim((string)($_POST['profileType'] ?? 'personal'))) === 'company' ? 'company' : 'personal';
    $companyName = trim((string)($_POST['companyName'] ?? ''));
    $companyKeyInput = strtoupper(trim((string)($_POST['companyKey'] ?? '')));

    $instagram = trim((string)($_POST['instagram'] ?? ''));
    $discord = trim((string)($_POST['discord'] ?? ''));
    $facebook = trim((string)($_POST['facebook'] ?? ''));
    $steam = trim((string)($_POST['steam'] ?? ''));
    $twitch = trim((string)($_POST['twitch'] ?? ''));
    $tiktok = trim((string)($_POST['tiktok'] ?? ''));
    $telegram = trim((string)($_POST['telegram'] ?? ''));
    $youtube = trim((string)($_POST['youtube'] ?? ''));

    $profileColor = sanitizeColor((string)($_POST['profileColor'] ?? '#c27eef'), '#C27EEF');
    $textColor = sanitizeColor((string)($_POST['textColor'] ?? '#ffffff'), '#FFFFFF');

    if ($username === '' || $password === '') {
        throw new Exception('Username and password are required');
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        throw new Exception('Username must be between 3 and 50 characters');
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception('Username can only contain letters, numbers and underscores');
    }

    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    if ($profileType === 'company' && $companyName === '') {
        throw new Exception('Company name is required for company profiles');
    }

    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);

    // Check username uniqueness
    $checkStmt = $conn->prepare("SELECT 1 FROM users WHERE username = ?");
    $checkStmt->bind_param('s', $username);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        $checkStmt->close();
        throw new Exception('Username already exists');
    }
    $checkStmt->close();

    // If creating a company profile, ensure company name is unique
    if ($profileType === 'company' && $companyName !== '') {
        $companyCheckStmt = $conn->prepare("SELECT 1 FROM companies WHERE company_name = ?");
        $companyCheckStmt->bind_param('s', $companyName);
        $companyCheckStmt->execute();
        if ($companyCheckStmt->get_result()->num_rows > 0) {
            $companyCheckStmt->close();
            throw new Exception('Company with this name already exists');
        }
        $companyCheckStmt->close();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        throw new Exception('Failed to hash password');
    }

    $conn->begin_transaction();

    try {
        $userStmt = $conn->prepare("INSERT INTO users (username, password_hash, profile_type) VALUES (?, ?, ?)");
        $initialProfileType = $profileType === 'company' ? 'company' : 'personal';
        $userStmt->bind_param('sss', $username, $passwordHash, $initialProfileType);
        $userStmt->execute();
        $userId = (int)$userStmt->insert_id;
        $userStmt->close();

        $infoStmt = $conn->prepare("
            INSERT INTO users_info (
                user_id,
                username,
                descr,
                inst,
                discord,
                fb,
                facebook,
                steam,
                twitch,
                tiktok,
                tg,
                youtube,
                color,
                colorText,
                views,
                profile_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
        ");
        $infoStmt->bind_param(
            'issssssssssssss',
            $userId,
            $username,
            $description,
            $instagram,
            $discord,
            $facebook,
            $facebook,
            $steam,
            $twitch,
            $tiktok,
            $telegram,
            $youtube,
            $profileColor,
            $textColor,
            $initialProfileType
        );
        $infoStmt->execute();
        $infoStmt->close();

        $fileMappings = [
            'avatar' => 'avatar',
            'background' => 'bg',
            'blockImage' => 'blockImage'
        ];

        foreach ($fileMappings as $inputName => $column) {
            if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            $contents = file_get_contents($_FILES[$inputName]['tmp_name']);
            if ($contents === false) {
                throw new Exception("Failed to read uploaded file: {$inputName}");
            }

            $encoded = base64_encode($contents);
            $mediaStmt = $conn->prepare("UPDATE users_info SET {$column} = ? WHERE username = ?");
            $mediaStmt->bind_param('ss', $encoded, $username);
            $mediaStmt->execute();
            $mediaStmt->close();
        }

        $companyResponse = null;

        if ($profileType === 'company') {
            $companyKey = generateUniqueCompanyKey($conn);
            $companyStmt = $conn->prepare("
                INSERT INTO companies (company_key, company_name, owner_username, owner_user_id, unified_design_enabled)
                VALUES (?, ?, ?, ?, 0)
            ");
            $companyStmt->bind_param('sssi', $companyKey, $companyName, $username, $userId);
            $companyStmt->execute();
            $companyId = (int)$companyStmt->insert_id;
            $companyStmt->close();

            $memberStmt = $conn->prepare("
                INSERT INTO company_members (company_id, user_id, username, role)
                VALUES (?, ?, ?, 'owner')
            ");
            $memberStmt->bind_param('iis', $companyId, $userId, $username);
            $memberStmt->execute();
            $memberStmt->close();

            $updateProfileStmt = $conn->prepare("
                UPDATE users_info
                SET company_id = ?, profile_type = 'company'
                WHERE username = ?
            ");
            $updateProfileStmt->bind_param('is', $companyId, $username);
            $updateProfileStmt->execute();
            $updateProfileStmt->close();

            $companyResponse = [
                'company_id' => $companyId,
                'company_key' => $companyKey,
                'company_name' => $companyName,
                'role' => 'owner'
            ];
        } elseif ($profileType === 'personal' && $companyKeyInput !== '') {
            $joinedCompany = joinCompanyByKey($conn, $username, $companyKeyInput);
            $companyResponse = [
                'company_id' => $joinedCompany['id'],
                'company_key' => $joinedCompany['company_key'],
                'company_name' => $joinedCompany['company_name'],
                'role' => 'member'
            ];
        }

        $conn->commit();

        sendJSONResponse([
            'success' => true,
            'message' => 'Profile created successfully',
            'user' => [
                'id' => $userId,
                'username' => $username,
                'description' => $description
            ],
            'company' => $companyResponse
        ]);
    } catch (Throwable $transactionError) {
        $conn->rollback();
        throw $transactionError;
    } finally {
        $conn->close();
    }
} catch (Throwable $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}

