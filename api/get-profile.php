<?php
require_once 'config.php';
require_once 'company-utils.php';

handlePreflight();

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $username = trim($_GET['username'] ?? '');
    
    if (empty($username)) {
        throw new Exception("Username is required");
    }
    
    $conn = getDBConnection();
    ensureCoreSchema($conn);
    ensureCompanySchema($conn);
    
    $stmt = $conn->prepare("SELECT * FROM users_info WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Profile not found");
    }
    
    $profile = $result->fetch_assoc();
    $stmt->close();

    $companyData = null;
    $companyDisplayName = $profile['company_display_name'] ?? '';
    $companyTagline = $profile['company_tagline'] ?? '';
    $companyLogo = null;
    $companyShowLogo = 0;
    $companyShowName = 1;
    $companyName = null;
    $companyRole = null;
    
    if (!empty($profile['company_id'])) {
        $companyId = (int)$profile['company_id'];
        $companyStmt = $conn->prepare("SELECT company_name, company_key FROM companies WHERE id = ?");
        if ($companyStmt) {
            $companyStmt->bind_param('i', $companyId);
            $companyStmt->execute();
            $companyRow = $companyStmt->get_result()->fetch_assoc();
            $companyStmt->close();

            if ($companyRow) {
                $companyName = $companyRow['company_name'] ?? '';
                $roleStmt = $conn->prepare("SELECT role FROM company_members WHERE company_id = ? AND username = ?");
                if ($roleStmt) {
                    $roleStmt->bind_param('is', $companyId, $username);
                    $roleStmt->execute();
                    $roleResult = $roleStmt->get_result()->fetch_assoc();
                    $roleStmt->close();
                    if ($roleResult && isset($roleResult['role'])) {
                        $companyRole = $roleResult['role'];
                    }
                }
                $companyData = [
                    'id' => $companyId,
                    'name' => $companyName,
                    'role' => $companyRole
                ];
            }
        }
    }

    $conn->close();
    $formattedCompanyLogo = $companyLogo ? loadMediaContent($companyLogo) : null;

    $customLogoStoredPath = $profile['customLogo'] ?? '';

    $profileExtraLinks = [];
    if (!empty($profile['extraLinks'])) {
        $decodedExtraLinks = json_decode($profile['extraLinks'], true);
        if (is_array($decodedExtraLinks)) {
            $profileExtraLinks = $decodedExtraLinks;
        }
    }

    if (!$companyDisplayName) {
        $companyDisplayName = $companyName ?? '';
    }
    
    // Формируем ответ
    $response = [
        'success' => true,
        'profile' => [
            'username' => $profile['username'],
            'descr' => $profile['descr'] ?? '',
            'inst' => $profile['inst'] ?? '',
            'instagram' => $profile['instagram'] ?? ($profile['inst'] ?? ''),
            'youtube' => $profile['youtube'] ?? '',
            'youtubeMusic' => $profile['youtubeMusic'] ?? '',
            'tiktok' => $profile['tiktok'] ?? '',
            'fb' => $profile['fb'] ?? '',
            'facebook' => $profile['facebook'] ?? ($profile['fb'] ?? ''),
            'x' => $profile['x'] ?? '',
            'linkedin' => $profile['linkedin'] ?? '',
            'twitch' => $profile['twitch'] ?? '',
            'steam' => $profile['steam'] ?? '',
            'discord' => $profile['discord'] ?? '',
            'tg' => $profile['tg'] ?? '',
            'telegram' => $profile['telegram'] ?? ($profile['tg'] ?? ''),
            'spotify' => $profile['spotify'] ?? '',
            'soundcloud' => $profile['soundcloud'] ?? '',
            'github' => $profile['github'] ?? '',
            'site' => $profile['site'] ?? '',
            'googleDocs' => $profile['googleDocs'] ?? '',
            'googleSheets' => $profile['googleSheets'] ?? '',
            'fileUpload' => $profile['fileUpload'] ?? '',
            'upwork' => $profile['upwork'] ?? '',
            'fiverr' => $profile['fiverr'] ?? '',
            'djinni' => $profile['djinni'] ?? '',
            'reddit' => $profile['reddit'] ?? '',
            'whatsapp' => $profile['whatsapp'] ?? '',
            'viber' => $profile['viber'] ?? '',
            'dou' => $profile['dou'] ?? '',
            'olx' => $profile['olx'] ?? '',
            'amazon' => $profile['amazon'] ?? '',
            'prom' => $profile['prom'] ?? '',
            'fhunt' => $profile['fhunt'] ?? '',
            'dj' => $profile['dj'] ?? '',
            'privatBank' => $profile['privatBank'] ?? '',
            'monoBank' => $profile['monoBank'] ?? '',
            'alfaBank' => $profile['alfaBank'] ?? '',
            'abank' => $profile['abank'] ?? '',
            'pumbBank' => $profile['pumbBank'] ?? '',
            'raiffeisenBank' => $profile['raiffeisenBank'] ?? '',
            'senseBank' => $profile['senseBank'] ?? '',
            'binance' => $profile['binance'] ?? '',
            'trustWallet' => $profile['trustWallet'] ?? '',
            'views' => (int)($profile['views'] ?? 0),
            'avatar' => $profile['avatar'] ?? null,
            'bg' => $profile['bg'] ?? null,
            'blockImage' => $profile['blockImage'] ?? null,
            'socialBgImage' => $profile['socialBgImage'] ?? null,
            'color' => $profile['color'] ?? '#c27eef',
            'profileColor' => $profile['color'] ?? '#c27eef',
            'colorText' => $profile['colorText'] ?? '#ffffff',
            'textColor' => $profile['colorText'] ?? '#ffffff',
            'textBgColor' => $profile['textBgColor'] ?? '',
            'profileOpacity' => (int)($profile['profileOpacity'] ?? 100),
            'textOpacity' => (int)($profile['textOpacity'] ?? 100),
            'textBgOpacity' => (int)($profile['textBgOpacity'] ?? 100),
            'socialBgColor' => $profile['socialBgColor'] ?? '#000000',
            'socialTextColor' => $profile['socialTextColor'] ?? '#ffffff',
            'socialOpacity' => (int)($profile['socialOpacity'] ?? 90),
            'profileBgType' => $profile['profileBgType'] ?? 'color',
            'socialBgType' => $profile['socialBgType'] ?? 'color',
            'customLogo' => $profile['customLogo'] ?? null,
            'customLogoPosition' => $profile['customLogoPosition'] ?? 'none',
            'customLogoSize' => isset($profile['customLogoSize']) ? (int)$profile['customLogoSize'] : 90,
            'customLogoPath' => $customLogoStoredPath,
            'companyId' => isset($profile['company_id']) ? (int)$profile['company_id'] : null,
            'companyDisplayName' => $companyDisplayName ?? '',
            'companyTagline' => $companyTagline ?? '',
            'companyLogo' => $formattedCompanyLogo,
            'companyShowLogo' => $companyShowLogo !== null ? (int)$companyShowLogo : 1,
            'companyShowName' => $companyShowName !== null ? (int)$companyShowName : 1,
            'companyName' => $companyName ?? '',
            'companyRole' => $companyRole,
            'extraLinks' => $profileExtraLinks
        ]
    ];
    
    $mediaFields = ['avatar', 'bg', 'blockImage', 'socialBgImage', 'customLogo'];
    foreach ($mediaFields as $field) {
        if (isset($response['profile'][$field])) {
            $response['profile'][$field] = loadMediaContent($response['profile'][$field]);
        }
    }
    $response['profile']['background'] = $response['profile']['bg'] ?? null;
    $response['company'] = $companyData;

    sendJSONResponse($response);
    
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 404);
}
?>

