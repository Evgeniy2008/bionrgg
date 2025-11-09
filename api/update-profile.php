<?php
// Включаем отображение ошибок для отладки (убрать в продакшене)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Начинаем буферизацию вывода
ob_start();

// Подключаем config.php
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Configuration file not found']);
    exit();
}
require_once $configPath;
require_once __DIR__ . '/company-utils.php';

handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

function getUploadsBaseDir(): string {
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile';
}

function ensureDirectory(string $path): void {
    if (!is_dir($path)) {
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new Exception("Failed to create directory: {$path}");
        }
    }
}

function sanitizeExtension(string $filename): string {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg',
        'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx',
        'csv', 'txt', 'rtf', 'odt', 'ods', 'odp',
        'zip', 'rar', '7z'
    ];
    if (!in_array($ext, $allowed, true)) {
        return 'bin';
    }
    return $ext;
}

function isStoredFilePath(?string $value): bool {
    if (!$value || !is_string($value)) {
        return false;
    }
    return (strpos($value, 'uploads/profile/') === 0) || (strpos($value, 'uploads\\profile\\') === 0);
}

function deleteStoredFile(?string $value): void {
    if (!isStoredFilePath($value)) {
        return;
    }

    $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value);
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

function saveProfileUpload(array $file, string $username, string $type, ?string $previousValue = null): string {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new Exception("Invalid file upload for {$type}");
    }

    deleteStoredFile($previousValue);

    $baseDir = getUploadsBaseDir();
    $userDir = $baseDir . DIRECTORY_SEPARATOR . $username;
    ensureDirectory($userDir);

    $extension = sanitizeExtension($file['name']);
    $filename = $type . '-' . uniqid() . '.' . $extension;
    $destination = $userDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to store uploaded file for {$type}");
    }

    return 'uploads/profile/' . $username . '/' . $filename;
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

// Увеличиваем лимиты для загрузки файлов
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '50M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

try {
    $conn = getDBConnection();
    ensureCoreSchema($conn);
    
    // Получаем данные формы
    $username = trim($_POST['username'] ?? '');
    $userID = trim($_POST['userID'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $companyPosition = mb_substr(trim($_POST['companyPosition'] ?? ''), 0, 150);
    
    // Проверка авторизации
    if (empty($username) || empty($password)) {
        throw new Exception("Authentication required");
    }
    
    authenticateUser($conn, $username, $password);

    $restrictDesignUpdates = false;

    $mediaStmt = $conn->prepare("SELECT avatar, bg, blockImage, socialBgImage, fileUpload, customLogo, extraLinks FROM users_info WHERE username = ?");
    if (!$mediaStmt) {
        throw new Exception("Failed to prepare media lookup: " . $conn->error);
    }
    $mediaStmt->bind_param("s", $username);
    $mediaStmt->execute();
    $mediaResult = $mediaStmt->get_result();
    $existingMedia = $mediaResult->fetch_assoc() ?: [
        'avatar' => null,
        'bg' => null,
        'blockImage' => null,
        'socialBgImage' => null,
        'customLogo' => null,
        'fileUpload' => null,
        'extraLinks' => null
    ];
    $mediaStmt->close();

    $existingExtraLinks = [];
    if (!empty($existingMedia['extraLinks'])) {
        $decodedExtra = json_decode($existingMedia['extraLinks'], true);
        if (is_array($decodedExtra)) {
            $existingExtraLinks = $decodedExtra;
        }
    }

    $existingExtraLinksMap = [];
    foreach ($existingExtraLinks as $extraEntry) {
        if (is_array($extraEntry) && isset($extraEntry['id'])) {
            $existingExtraLinksMap[$extraEntry['id']] = $extraEntry;
        }
    }
    
    // Социальные сети - получаем данные из формы
    $socialData = [
        'inst' => trim($_POST['instagram'] ?? ''),
        'discord' => trim($_POST['discord'] ?? ''),
        'fb' => trim($_POST['facebook'] ?? ''),
        'steam' => trim($_POST['steam'] ?? ''),
        'twitch' => trim($_POST['twitch'] ?? ''),
        'tiktok' => trim($_POST['tiktok'] ?? ''),
        'tg' => trim($_POST['telegram'] ?? ''),
        'youtube' => trim($_POST['youtube'] ?? ''),
        'youtubeMusic' => trim($_POST['youtubeMusic'] ?? ''),
        'x' => trim($_POST['x'] ?? ''),
        'linkedin' => trim($_POST['linkedin'] ?? ''),
        'spotify' => trim($_POST['spotify'] ?? ''),
        'soundcloud' => trim($_POST['soundcloud'] ?? ''),
        'github' => trim($_POST['github'] ?? ''),
        'site' => trim($_POST['site'] ?? ''),
        'googleDocs' => trim($_POST['googleDocs'] ?? ''),
        'googleSheets' => trim($_POST['googleSheets'] ?? ''),
        'fileUpload' => trim($_POST['fileUpload'] ?? ''),
        'upwork' => trim($_POST['upwork'] ?? ''),
        'fiverr' => trim($_POST['fiverr'] ?? ''),
        'djinni' => trim($_POST['djinni'] ?? ''),
        'reddit' => trim($_POST['reddit'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'viber' => trim($_POST['viber'] ?? ''),
        'dou' => trim($_POST['dou'] ?? ''),
        'olx' => trim($_POST['olx'] ?? ''),
        'amazon' => trim($_POST['amazon'] ?? ''),
        'prom' => trim($_POST['prom'] ?? ''),
        'fhunt' => trim($_POST['fhunt'] ?? ''),
        'dj' => trim($_POST['dj'] ?? ''),
        'privatBank' => trim($_POST['privatBank'] ?? ''),
        'monoBank' => trim($_POST['monoBank'] ?? ''),
        'alfaBank' => trim($_POST['alfaBank'] ?? ''),
        'abank' => trim($_POST['abank'] ?? ''),
        'pumbBank' => trim($_POST['pumbBank'] ?? ''),
        'raiffeisenBank' => trim($_POST['raiffeisenBank'] ?? ''),
        'senseBank' => trim($_POST['senseBank'] ?? ''),
        'binance' => trim($_POST['binance'] ?? ''),
        'trustWallet' => trim($_POST['trustWallet'] ?? '')
    ];
    $socialData['instagram'] = $socialData['inst'];
    $socialData['facebook'] = $socialData['fb'];
    $socialData['telegram'] = $socialData['tg'];
    
    $submittedExtraLinksRaw = $_POST['extraLinks'] ?? '[]';
    $submittedExtraLinks = json_decode($submittedExtraLinksRaw, true);
    if (!is_array($submittedExtraLinks)) {
        $submittedExtraLinks = [];
    }

    $processedExtraLinks = [];
    $extraFileUploads = [];

    $customLogoPosition = trim($_POST['customLogoPosition'] ?? 'none');
    $allowedLogoPositions = [
        'none',
        'top-left',
        'top-center',
        'top-right',
        'middle-left',
        'middle-center',
        'middle-right',
        'bottom-left',
        'bottom-center',
        'bottom-right'
    ];
    if (!in_array($customLogoPosition, $allowedLogoPositions, true)) {
        $customLogoPosition = 'none';
    }

    $customLogoSize = (int)($_POST['customLogoSize'] ?? 90);
    if ($customLogoSize < 40) {
        $customLogoSize = 40;
    } elseif ($customLogoSize > 200) {
        $customLogoSize = 200;
    }

    // Дизайн
    $profileColor = trim($_POST['profileColor'] ?? '#c27eef');
    $textColor = trim($_POST['textColor'] ?? '#ffffff');
    $textBgColor = trim($_POST['textBgColor'] ?? '');
    $profileOpacity = (int)($_POST['profileOpacity'] ?? 100);
    $textOpacity = (int)($_POST['textOpacity'] ?? 100);
    $textBgOpacity = (int)($_POST['textBgOpacity'] ?? 100);
    $socialBgColor = trim($_POST['socialBgColor'] ?? '#000000');
    $socialTextColor = trim($_POST['socialTextColor'] ?? '#ffffff');
    $socialOpacity = (int)($_POST['socialOpacity'] ?? 90);

    // Валидация цветов
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $profileColor)) {
        $profileColor = '#c27eef';
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $textColor)) {
        $textColor = '#ffffff';
    }
    if ($textBgColor && !preg_match('/^#[0-9A-Fa-f]{6}$/', $textBgColor)) {
        $textBgColor = '';
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $socialBgColor)) {
        $socialBgColor = '#000000';
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $socialTextColor)) {
        $socialTextColor = '#ffffff';
    }
    
    // Начало транзакции
    $conn->begin_transaction();
    
    try {
        $stringFields = [
            'descr' => $description,
            'inst' => $socialData['inst'],
            'instagram' => $socialData['instagram'],
            'discord' => $socialData['discord'],
            'fb' => $socialData['fb'],
            'facebook' => $socialData['facebook'],
            'steam' => $socialData['steam'],
            'twitch' => $socialData['twitch'],
            'tiktok' => $socialData['tiktok'],
            'tg' => $socialData['tg'],
            'telegram' => $socialData['telegram'],
            'youtube' => $socialData['youtube'],
            'youtubeMusic' => $socialData['youtubeMusic'],
            'x' => $socialData['x'],
            'linkedin' => $socialData['linkedin'],
            'spotify' => $socialData['spotify'],
            'soundcloud' => $socialData['soundcloud'],
            'github' => $socialData['github'],
            'site' => $socialData['site'],
            'googleDocs' => $socialData['googleDocs'],
            'googleSheets' => $socialData['googleSheets'],
            'fileUpload' => $socialData['fileUpload'],
            'upwork' => $socialData['upwork'],
            'fiverr' => $socialData['fiverr'],
            'djinni' => $socialData['djinni'],
            'reddit' => $socialData['reddit'],
            'whatsapp' => $socialData['whatsapp'],
            'viber' => $socialData['viber'],
            'dou' => $socialData['dou'],
            'olx' => $socialData['olx'],
            'amazon' => $socialData['amazon'],
            'prom' => $socialData['prom'],
            'fhunt' => $socialData['fhunt'],
            'dj' => $socialData['dj'],
            'privatBank' => $socialData['privatBank'],
            'monoBank' => $socialData['monoBank'],
            'alfaBank' => $socialData['alfaBank'],
            'abank' => $socialData['abank'],
            'pumbBank' => $socialData['pumbBank'],
            'raiffeisenBank' => $socialData['raiffeisenBank'],
            'senseBank' => $socialData['senseBank'],
            'binance' => $socialData['binance'],
            'trustWallet' => $socialData['trustWallet'],
            'color' => $profileColor,
            'colorText' => $textColor,
            'textBgColor' => $textBgColor,
            'socialBgColor' => $socialBgColor,
            'socialTextColor' => $socialTextColor,
            'company_tagline' => $companyPosition,
            'customLogoPosition' => $customLogoPosition
        ];

        $intFields = [
            'profileOpacity' => $profileOpacity,
            'textOpacity' => $textOpacity,
            'textBgOpacity' => $textBgOpacity,
            'socialOpacity' => $socialOpacity,
            'customLogoSize' => $customLogoSize
        ];

    $extraLinkOrderCounter = 0;
    foreach ($submittedExtraLinks as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        $type = isset($entry['type']) && $entry['type'] === 'file' ? 'file' : 'link';
        $platform = isset($entry['platform']) ? trim((string)$entry['platform']) : '';
        $label = isset($entry['label']) ? trim((string)$entry['label']) : '';
        $platform = mb_substr($platform, 0, 100);
        $label = mb_substr($label, 0, 150);

        $id = isset($entry['id']) ? trim((string)$entry['id']) : '';
        if ($id === '') {
            $id = 'extra_' . uniqid();
        }

        $order = isset($entry['order']) ? (int)$entry['order'] : $extraLinkOrderCounter++;

        $sanitizedEntry = [
            'id' => $id,
            'platform' => $platform,
            'label' => $label,
            'type' => $type,
            'order' => $order
        ];

        if ($type === 'link') {
            $url = isset($entry['url']) ? trim((string)$entry['url']) : '';
            if ($url === '') {
                continue;
            }
            $url = mb_substr($url, 0, 2048);
            $sanitizedEntry['url'] = $url;
            $processedExtraLinks[] = $sanitizedEntry;
            continue;
        }

        // File type
        $storedFilePath = isset($entry['storedFilePath']) ? trim((string)$entry['storedFilePath']) : '';
        $originalName = isset($entry['originalName']) ? trim((string)$entry['originalName']) : '';
        $size = isset($entry['size']) ? (int)$entry['size'] : null;
        $fileField = isset($entry['fileField']) ? trim((string)$entry['fileField']) : '';

        if ($storedFilePath === '' && isset($existingExtraLinksMap[$id]['storedFilePath'])) {
            $storedFilePath = trim((string)$existingExtraLinksMap[$id]['storedFilePath']);
        }

        if ($originalName === '' && isset($existingExtraLinksMap[$id]['originalName'])) {
            $originalName = trim((string)$existingExtraLinksMap[$id]['originalName']);
        }

        if ($size === null && isset($existingExtraLinksMap[$id]['size'])) {
            $size = (int)$existingExtraLinksMap[$id]['size'];
        }

        $sanitizedEntry['url'] = '';
        $sanitizedEntry['storedFilePath'] = $storedFilePath;
        if ($originalName !== '') {
            $sanitizedEntry['originalName'] = mb_substr($originalName, 0, 255);
        }
        if ($size !== null && $size > 0) {
            $sanitizedEntry['size'] = $size;
        }

        $entryIndex = count($processedExtraLinks);
        $processedExtraLinks[] = $sanitizedEntry;

        if ($fileField !== '') {
            $extraFileUploads[] = [
                'entryIndex' => $entryIndex,
                'fieldName' => $fileField,
                'previousPath' => $storedFilePath
            ];
        } elseif ($storedFilePath === '') {
            // Nothing to keep for this entry, remove it
            array_pop($processedExtraLinks);
        }
    }

        if ($restrictDesignUpdates) {
            $designStringKeys = ['color', 'colorText', 'textBgColor', 'socialBgColor', 'socialTextColor'];
            foreach ($designStringKeys as $key) {
                unset($stringFields[$key]);
            }

            $designIntKeys = ['profileOpacity', 'textOpacity', 'textBgOpacity', 'socialOpacity'];
            foreach ($designIntKeys as $key) {
                unset($intFields[$key]);
            }
        }

        $setClauses = [];
        $types = '';
        $params = [];

        foreach ($stringFields as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $types .= 's';
            $params[] = $value;
        }

        foreach ($intFields as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $types .= 'i';
            $params[] = $value;
        }

        $setSql = implode(",\n            ", $setClauses);

        $updateStmt = $conn->prepare("UPDATE users_info SET 
            {$setSql}
            WHERE username = ?");

        if (!$updateStmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }

        $types .= 's';
        $params[] = $username;

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array([$updateStmt, 'bind_param'], $bindParams);

        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update profile: " . $updateStmt->error);
        }
        $updateStmt->close();
        
        // Обработка загрузки файлов
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = saveProfileUpload($_FILES['avatar'], $username, 'avatar', $existingMedia['avatar']);
            $existingMedia['avatar'] = $avatarPath;
            $avatarStmt = $conn->prepare("UPDATE users_info SET avatar = ? WHERE username = ?");
            if ($avatarStmt) {
                $avatarStmt->bind_param("ss", $avatarPath, $username);
                $avatarStmt->execute();
                $avatarStmt->close();
            }
        }

        if (!$restrictDesignUpdates) {
            if (isset($_FILES['background']) && $_FILES['background']['error'] === UPLOAD_ERR_OK) {
                $bgPath = saveProfileUpload($_FILES['background'], $username, 'background', $existingMedia['bg']);
                $existingMedia['bg'] = $bgPath;
                $bgStmt = $conn->prepare("UPDATE users_info SET bg = ? WHERE username = ?");
                if ($bgStmt) {
                    $bgStmt->bind_param("ss", $bgPath, $username);
                    $bgStmt->execute();
                    $bgStmt->close();
                }
            }

            if (isset($_FILES['blockImage']) && $_FILES['blockImage']['error'] === UPLOAD_ERR_OK) {
                $blockPath = saveProfileUpload($_FILES['blockImage'], $username, 'block', $existingMedia['blockImage']);
                $existingMedia['blockImage'] = $blockPath;
                $blockStmt = $conn->prepare("UPDATE users_info SET blockImage = ? WHERE username = ?");
                if ($blockStmt) {
                    $blockStmt->bind_param("ss", $blockPath, $username);
                    $blockStmt->execute();
                    $blockStmt->close();
                }
            }

            if (isset($_FILES['blockImage2']) && $_FILES['blockImage2']['error'] === UPLOAD_ERR_OK) {
                $blockPath = saveProfileUpload($_FILES['blockImage2'], $username, 'block', $existingMedia['blockImage']);
                $existingMedia['blockImage'] = $blockPath;
                $blockStmt = $conn->prepare("UPDATE users_info SET blockImage = ? WHERE username = ?");
                if ($blockStmt) {
                    $blockStmt->bind_param("ss", $blockPath, $username);
                    $blockStmt->execute();
                    $blockStmt->close();
                }
            }

            if (isset($_FILES['socialBgImage']) && $_FILES['socialBgImage']['error'] === UPLOAD_ERR_OK) {
                $socialPath = saveProfileUpload($_FILES['socialBgImage'], $username, 'social', $existingMedia['socialBgImage']);
                $existingMedia['socialBgImage'] = $socialPath;
                $socialBgStmt = $conn->prepare("UPDATE users_info SET socialBgImage = ? WHERE username = ?");
                if ($socialBgStmt) {
                    $socialBgStmt->bind_param("ss", $socialPath, $username);
                    $socialBgStmt->execute();
                    $socialBgStmt->close();
                }
            }
        }

        if (isset($_FILES['customLogo']) && $_FILES['customLogo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['customLogo']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['customLogo']['size'] > 10 * 1024 * 1024) {
                    throw new Exception('Custom logo file is too large (maximum 10MB).');
                }
                $logoPath = saveProfileUpload($_FILES['customLogo'], $username, 'custom-logo', $existingMedia['customLogo'] ?? null);
                $existingMedia['customLogo'] = $logoPath;
                $logoStmt = $conn->prepare("UPDATE users_info SET customLogo = ? WHERE username = ?");
                if ($logoStmt) {
                    $logoStmt->bind_param("ss", $logoPath, $username);
                    $logoStmt->execute();
                    $logoStmt->close();
                }
            } else {
                throw new Exception("Failed to upload custom logo (error code: {$_FILES['customLogo']['error']})");
            }
        }

        if (isset($_FILES['fileUploadInput']) && $_FILES['fileUploadInput']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['fileUploadInput']['error'] === UPLOAD_ERR_OK) {
                $filePath = saveProfileUpload($_FILES['fileUploadInput'], $username, 'file', $existingMedia['fileUpload'] ?? null);
                $existingMedia['fileUpload'] = $filePath;

                $fileStmt = $conn->prepare("UPDATE users_info SET fileUpload = ? WHERE username = ?");
                if ($fileStmt) {
                    $fileStmt->bind_param("ss", $filePath, $username);
                    $fileStmt->execute();
                    $fileStmt->close();
                }
            } else {
                throw new Exception("Failed to upload attached file (error code: {$_FILES['fileUploadInput']['error']})");
            }
        } else {
            $submittedFileLink = trim($_POST['fileUpload'] ?? '');
            if ($submittedFileLink === '' && !empty($existingMedia['fileUpload'])) {
                deleteStoredFile($existingMedia['fileUpload']);
                $existingMedia['fileUpload'] = null;

                $clearFileStmt = $conn->prepare("UPDATE users_info SET fileUpload = '' WHERE username = ?");
                if ($clearFileStmt) {
                    $clearFileStmt->bind_param("s", $username);
                    $clearFileStmt->execute();
                    $clearFileStmt->close();
                }
            }
        }

        foreach ($extraFileUploads as $uploadInfo) {
            $fieldName = $uploadInfo['fieldName'];
            if (!isset($_FILES[$fieldName])) {
                continue;
            }

            $fileData = $_FILES[$fieldName];
            if ($fileData['error'] === UPLOAD_ERR_OK) {
                $previousPath = $uploadInfo['previousPath'] ?? null;
                $newPath = saveProfileUpload($fileData, $username, 'extra', $previousPath);
                $processedExtraLinks[$uploadInfo['entryIndex']]['storedFilePath'] = $newPath;
                $processedExtraLinks[$uploadInfo['entryIndex']]['originalName'] = $fileData['name'];
                $processedExtraLinks[$uploadInfo['entryIndex']]['size'] = (int)$fileData['size'];
            } elseif ($fileData['error'] !== UPLOAD_ERR_NO_FILE) {
                throw new Exception("Failed to upload attached extra file (field: {$fieldName}, error code: {$fileData['error']})");
            }
        }

        $processedExtraLinks = array_values(array_filter($processedExtraLinks, function ($entry) {
            if (!is_array($entry)) {
                return false;
            }
            $type = $entry['type'] ?? 'link';
            if ($type === 'file') {
                return !empty($entry['storedFilePath']);
            }
            if ($type === 'link') {
                return !empty($entry['url']);
            }
            return false;
        }));

        usort($processedExtraLinks, function ($a, $b) {
            return ($a['order'] ?? 0) <=> ($b['order'] ?? 0);
        });

        foreach ($processedExtraLinks as $idx => &$extraEntry) {
            $extraEntry['order'] = $idx;
            if (($extraEntry['type'] ?? 'link') === 'file') {
                $extraEntry['url'] = '';
                if (empty($extraEntry['originalName']) && !empty($extraEntry['storedFilePath'])) {
                    $extraEntry['originalName'] = basename(str_replace(['\\'], '/', $extraEntry['storedFilePath']));
                }
            } else {
                $extraEntry['url'] = isset($extraEntry['url']) ? $extraEntry['url'] : '';
                $extraEntry['storedFilePath'] = '';
            }
            if (isset($extraEntry['fileField'])) {
                unset($extraEntry['fileField']);
            }
        }
        unset($extraEntry);

        $existingFilePaths = array_filter(array_map(function ($entry) {
            return isset($entry['storedFilePath']) ? trim((string)$entry['storedFilePath']) : '';
        }, $existingExtraLinks));

        $newFilePaths = array_filter(array_map(function ($entry) {
            return isset($entry['storedFilePath']) ? trim((string)$entry['storedFilePath']) : '';
        }, $processedExtraLinks));

        $pathsToRemove = array_diff($existingFilePaths, $newFilePaths);
        foreach ($pathsToRemove as $pathToRemove) {
            deleteStoredFile($pathToRemove);
        }

        $extraLinksJson = json_encode($processedExtraLinks, JSON_UNESCAPED_UNICODE);
        if ($extraLinksJson === false) {
            throw new Exception('Failed to encode extra links data.');
        }

        $extraLinksStmt = $conn->prepare("UPDATE users_info SET extraLinks = ? WHERE username = ?");
        if ($extraLinksStmt) {
            $extraLinksStmt->bind_param("ss", $extraLinksJson, $username);
            $extraLinksStmt->execute();
            $extraLinksStmt->close();
        }
        
        // Коммит транзакции
        $conn->commit();
        
        // Получаем обновленные данные профиля
        $getStmt = $conn->prepare("SELECT * FROM users_info WHERE username = ?");
        $getStmt->bind_param("s", $username);
        $getStmt->execute();
        $profileResult = $getStmt->get_result();
        $profile = $profileResult->fetch_assoc();
        $getStmt->close();

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

        if (!$companyDisplayName) {
            $companyDisplayName = $companyName ?? '';
        }

        $formattedCompanyLogo = $companyLogo ? loadMediaContent($companyLogo) : null;

        $profileExtraLinks = [];
        if (!empty($profile['extraLinks'])) {
            $decodedExtraLinks = json_decode($profile['extraLinks'], true);
            if (is_array($decodedExtraLinks)) {
                $profileExtraLinks = $decodedExtraLinks;
            }
        }
        
        // Очищаем буфер вывода перед отправкой JSON
        ob_clean();
        
        // Формируем ответ
        $customLogoStoredPath = $profile['customLogo'] ?? '';

        $response = [
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => [
                'username' => $profile['username'],
                'descr' => $profile['descr'] ?? '',
                'color' => $profile['color'] ?? '#c27eef',
                'colorText' => $profile['colorText'] ?? '#ffffff',
                'textBgColor' => $profile['textBgColor'] ?? '',
                'profileOpacity' => (int)($profile['profileOpacity'] ?? 100),
                'textOpacity' => (int)($profile['textOpacity'] ?? 100),
                'textBgOpacity' => (int)($profile['textBgOpacity'] ?? 100),
                'socialBgColor' => $profile['socialBgColor'] ?? '#000000',
                'socialTextColor' => $profile['socialTextColor'] ?? '#ffffff',
                'socialOpacity' => (int)($profile['socialOpacity'] ?? 90),
                'avatar' => $profile['avatar'] ?? null,
                'bg' => $profile['bg'] ?? null,
                'background' => $profile['bg'] ?? null,
                'blockImage' => $profile['blockImage'] ?? null,
                'socialBgImage' => $profile['socialBgImage'] ?? null,
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
            ],
            'company' => $companyData
        ];
        
        // Добавляем все социальные сети
        $response['profile']['instagram'] = $profile['instagram'] ?? ($profile['inst'] ?? '');
        $response['profile']['youtube'] = $profile['youtube'] ?? '';
        $response['profile']['youtubeMusic'] = $profile['youtubeMusic'] ?? '';
        $response['profile']['tiktok'] = $profile['tiktok'] ?? '';
        $response['profile']['facebook'] = $profile['facebook'] ?? ($profile['fb'] ?? '');
        $response['profile']['x'] = $profile['x'] ?? '';
        $response['profile']['linkedin'] = $profile['linkedin'] ?? '';
        $response['profile']['twitch'] = $profile['twitch'] ?? '';
        $response['profile']['steam'] = $profile['steam'] ?? '';
        $response['profile']['discord'] = $profile['discord'] ?? '';
        $response['profile']['telegram'] = $profile['telegram'] ?? ($profile['tg'] ?? '');
        $response['profile']['spotify'] = $profile['spotify'] ?? '';
        $response['profile']['soundcloud'] = $profile['soundcloud'] ?? '';
        $response['profile']['github'] = $profile['github'] ?? '';
        $response['profile']['site'] = $profile['site'] ?? '';
        $response['profile']['googleDocs'] = $profile['googleDocs'] ?? '';
        $response['profile']['googleSheets'] = $profile['googleSheets'] ?? '';
        $response['profile']['fileUpload'] = $profile['fileUpload'] ?? '';
        $response['profile']['upwork'] = $profile['upwork'] ?? '';
        $response['profile']['fiverr'] = $profile['fiverr'] ?? '';
        $response['profile']['djinni'] = $profile['djinni'] ?? '';
        $response['profile']['reddit'] = $profile['reddit'] ?? '';
        $response['profile']['whatsapp'] = $profile['whatsapp'] ?? '';
        $response['profile']['viber'] = $profile['viber'] ?? '';
        $response['profile']['dou'] = $profile['dou'] ?? '';
        $response['profile']['olx'] = $profile['olx'] ?? '';
        $response['profile']['amazon'] = $profile['amazon'] ?? '';
        $response['profile']['prom'] = $profile['prom'] ?? '';
        $response['profile']['fhunt'] = $profile['fhunt'] ?? '';
        $response['profile']['dj'] = $profile['dj'] ?? '';
        $response['profile']['privatBank'] = $profile['privatBank'] ?? '';
        $response['profile']['monoBank'] = $profile['monoBank'] ?? '';
        $response['profile']['alfaBank'] = $profile['alfaBank'] ?? '';
        $response['profile']['abank'] = $profile['abank'] ?? '';
        $response['profile']['pumbBank'] = $profile['pumbBank'] ?? '';
        $response['profile']['raiffeisenBank'] = $profile['raiffeisenBank'] ?? '';
        $response['profile']['senseBank'] = $profile['senseBank'] ?? '';
        $response['profile']['binance'] = $profile['binance'] ?? '';
        $response['profile']['trustWallet'] = $profile['trustWallet'] ?? '';

        $mediaFields = ['avatar', 'bg', 'blockImage', 'socialBgImage', 'customLogo'];
        foreach ($mediaFields as $field) {
            if (isset($response['profile'][$field])) {
                $response['profile'][$field] = loadMediaContent($response['profile'][$field]);
            }
        }
        $response['profile']['background'] = $response['profile']['bg'];
        
        sendJSONResponse($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    // Очищаем буфер вывода перед отправкой ошибки
    ob_clean();
    
    error_log("Update profile error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    sendJSONResponse([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ], 500);
}
?>
