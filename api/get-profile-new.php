<?php
require_once 'config.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $username = trim($_GET['username'] ?? '');
    
    if (empty($username)) {
        throw new Exception("Username is required");
    }
    
    $conn = getDBConnection();
    
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
    $conn->close();
    
    $profileExtraLinks = [];
    if (!empty($profile['extraLinks'])) {
        $decodedExtraLinks = json_decode($profile['extraLinks'], true);
        if (is_array($decodedExtraLinks)) {
            $profileExtraLinks = $decodedExtraLinks;
        }
    }
    
    // Формируем ответ
    $response = [
        'success' => true,
        'profile' => [
            'username' => $profile['username'],
            'descr' => $profile['descr'] ?? '',
            'inst' => $profile['inst'] ?? '',
            'youtube' => $profile['youtube'] ?? '',
            'youtubeMusic' => $profile['youtubeMusic'] ?? '',
            'tiktok' => $profile['tiktok'] ?? '',
            'fb' => $profile['fb'] ?? '',
            'facebook' => $profile['facebook'] ?? '',
            'x' => $profile['x'] ?? '',
            'linkedin' => $profile['linkedin'] ?? '',
            'twitch' => $profile['twitch'] ?? '',
            'steam' => $profile['steam'] ?? '',
            'discord' => $profile['discord'] ?? '',
            'tg' => $profile['tg'] ?? '',
            'telegram' => $profile['telegram'] ?? '',
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
            'customLogoPath' => $profile['customLogo'] ?? '',
            'extraLinks' => $profileExtraLinks
        ]
    ];
    
    sendJSONResponse($response);
    
} catch (Exception $e) {
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 404);
}
?>


