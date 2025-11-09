<?php
require_once __DIR__ . '/config.php';

/**
 * Ensure that all tables, columns and indexes required for the company feature exist.
 *
 * @throws Exception when a schema operation fails.
 */
function ensureCompanySchema($conn) {
    static $schemaChecked = false;
    if ($schemaChecked) {
        return;
    }

    $tablesSql = [
        "CREATE TABLE IF NOT EXISTS `companies` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `company_key` VARCHAR(20) NOT NULL,
            `company_name` VARCHAR(255) NOT NULL,
            `owner_username` VARCHAR(50) NOT NULL,
            `owner_user_id` INT(11) NOT NULL,
            `unified_design_enabled` TINYINT(1) DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `company_key` (`company_key`),
            KEY `idx_owner` (`owner_username`),
            KEY `idx_owner_user_id` (`owner_user_id`),
            CONSTRAINT `fk_companies_owner_user`
                FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

        "CREATE TABLE IF NOT EXISTS `company_members` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `company_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `username` VARCHAR(50) NOT NULL,
            `role` ENUM('owner', 'member') DEFAULT 'member',
            `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_member` (`company_id`, `username`),
            KEY `idx_company_id` (`company_id`),
            KEY `idx_username` (`username`),
            KEY `idx_member_user_id` (`user_id`),
            CONSTRAINT `fk_company_members_company`
                FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_company_members_user`
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    ];

    foreach ($tablesSql as $sql) {
        if (!$conn->query($sql)) {
            throw new Exception('Failed to ensure company schema: ' . $conn->error);
        }
    }

    // Ensure required columns exist in users_info
    ensureColumnExists($conn, 'users_info', 'profile_type', "ALTER TABLE `users_info` ADD COLUMN `profile_type` ENUM('personal', 'company') DEFAULT 'personal'");
    ensureColumnExists($conn, 'users_info', 'company_id', "ALTER TABLE `users_info` ADD COLUMN `company_id` INT(11) DEFAULT NULL");
    ensureColumnExists($conn, 'users_info', 'profileBgType', "ALTER TABLE `users_info` ADD COLUMN `profileBgType` VARCHAR(10) DEFAULT 'color'");
    ensureColumnExists($conn, 'users_info', 'socialBgType', "ALTER TABLE `users_info` ADD COLUMN `socialBgType` VARCHAR(10) DEFAULT 'color'");
    ensureColumnExists($conn, 'users_info', 'user_id', "ALTER TABLE `users_info` ADD COLUMN `user_id` INT(11) DEFAULT NULL");
    ensureColumnExists($conn, 'users_info', 'instagram', "ALTER TABLE `users_info` ADD COLUMN `instagram` VARCHAR(255) DEFAULT '' AFTER `inst`");
    ensureColumnExists($conn, 'users_info', 'facebook', "ALTER TABLE `users_info` ADD COLUMN `facebook` VARCHAR(255) DEFAULT '' AFTER `fb`");
    ensureColumnExists($conn, 'users_info', 'telegram', "ALTER TABLE `users_info` ADD COLUMN `telegram` VARCHAR(255) DEFAULT '' AFTER `tg`");
    ensureColumnExists($conn, 'users_info', 'company_display_name', "ALTER TABLE `users_info` ADD COLUMN `company_display_name` VARCHAR(255) DEFAULT NULL AFTER `company_id`");
    ensureColumnExists($conn, 'users_info', 'company_tagline', "ALTER TABLE `users_info` ADD COLUMN `company_tagline` VARCHAR(255) DEFAULT NULL AFTER `company_display_name`");
    ensureColumnExists($conn, 'users_info', 'company_logo', "ALTER TABLE `users_info` ADD COLUMN `company_logo` LONGTEXT DEFAULT NULL AFTER `company_tagline`");
    ensureColumnExists($conn, 'users_info', 'company_show_logo', "ALTER TABLE `users_info` ADD COLUMN `company_show_logo` TINYINT(1) DEFAULT 1 AFTER `company_logo`");
    ensureColumnExists($conn, 'users_info', 'company_show_name', "ALTER TABLE `users_info` ADD COLUMN `company_show_name` TINYINT(1) DEFAULT 1 AFTER `company_show_logo`");

    // Ensure indexes
    ensureIndexExists($conn, 'users_info', 'idx_company_id', "ALTER TABLE `users_info` ADD KEY `idx_company_id` (`company_id`)");
    ensureIndexExists($conn, 'users_info', 'idx_profile_type', "ALTER TABLE `users_info` ADD KEY `idx_profile_type` (`profile_type`)");
    ensureColumnExists($conn, 'companies', 'owner_user_id', "ALTER TABLE `companies` ADD COLUMN `owner_user_id` INT(11) DEFAULT NULL AFTER `owner_username`");
    ensureColumnExists($conn, 'company_members', 'user_id', "ALTER TABLE `company_members` ADD COLUMN `user_id` INT(11) DEFAULT NULL AFTER `company_id`");
    ensureIndexExists($conn, 'company_members', 'idx_member_user_id', "ALTER TABLE `company_members` ADD KEY `idx_member_user_id` (`user_id`)");

    // Backfill user_id references where possible
    $conn->query("
        UPDATE users_info ui
        JOIN users u ON ui.username = u.username
        SET ui.user_id = u.id
        WHERE ui.user_id IS NULL
    ");

    $conn->query("
        UPDATE companies c
        JOIN users u ON c.owner_username = u.username
        SET c.owner_user_id = u.id
        WHERE c.owner_user_id IS NULL
    ");

    $conn->query("
        UPDATE company_members cm
        JOIN users u ON cm.username = u.username
        SET cm.user_id = u.id
        WHERE cm.user_id IS NULL
    ");

    $schemaChecked = true;
}

function ensureColumnExists($conn, $table, $column, $alterSql) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    if (!$stmt) {
        throw new Exception('Failed to prepare schema check: ' . $conn->error);
    }
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($result['total'])) {
        if (!$conn->query($alterSql)) {
            throw new Exception('Failed to alter table `' . $table . '` add column `' . $column . '`: ' . $conn->error);
        }
    }
}

function ensureIndexExists($conn, $table, $index, $alterSql) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");
    if (!$stmt) {
        throw new Exception('Failed to prepare index check: ' . $conn->error);
    }
    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($result['total'])) {
        if (!$conn->query($alterSql)) {
            throw new Exception('Failed to alter table `' . $table . '` add index `' . $index . '`: ' . $conn->error);
        }
    }
}

function generateUniqueCompanyKey($conn, $length = 8) {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $maxIndex = strlen($characters) - 1;

    do {
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $characters[random_int(0, $maxIndex)];
        }

        $stmt = $conn->prepare("SELECT id FROM companies WHERE company_key = ?");
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $key;
}


/**
 * Attach a user to a company by company key (used for registration and API join).
 *
 * @throws Exception when company not found or membership fails.
 */
function joinCompanyByKey($conn, $username, $companyKey, $role = 'member') {
    ensureCompanySchema($conn);

    $companyKey = strtoupper(trim($companyKey));
    if ($companyKey === '' || strlen($companyKey) < 6) {
        throw new Exception('Invalid company key');
    }

    $user = fetchUserByUsername($conn, $username);
    if (!$user) {
        throw new Exception('User not found');
    }

    $stmt = $conn->prepare("SELECT * FROM companies WHERE company_key = ?");
    $stmt->bind_param('s', $companyKey);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$company) {
        throw new Exception('Company not found');
    }

    // Check existing membership
    $stmt = $conn->prepare("SELECT id FROM company_members WHERE company_id = ? AND username = ?");
    $stmt->bind_param('is', $company['id'], $username);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($exists) {
        throw new Exception('User is already a member of this company');
    }

    $stmt = $conn->prepare("INSERT INTO company_members (company_id, user_id, username, role) VALUES (?, ?, ?, ?)");
    $userId = (int)$user['id'];
    $stmt->bind_param('iiss', $company['id'], $userId, $username, $role);
    if (!$stmt->execute()) {
        throw new Exception('Failed to join company: ' . $stmt->error);
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE users_info SET company_id = ?, profile_type = 'company' WHERE username = ?");
    $stmt->bind_param('is', $company['id'], $username);
    $stmt->execute();
    $stmt->close();

    return [
        'id' => (int)$company['id'],
        'company_key' => $company['company_key'],
        'company_name' => $company['company_name'],
        'owner_username' => $company['owner_username'],
        'unified_design_enabled' => (bool)$company['unified_design_enabled']
    ];
}

