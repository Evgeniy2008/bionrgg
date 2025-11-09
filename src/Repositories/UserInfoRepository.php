<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserInfoRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users_info (
                user_id,
                username,
                descr,
                views,
                profile_type,
                company_id,
                color,
                colorText,
                textBgColor,
                profileOpacity,
                textOpacity,
                textBgOpacity,
                socialBgColor,
                socialTextColor,
                socialOpacity,
                profileBgType,
                socialBgType
            ) VALUES (
                :user_id,
                :username,
                :descr,
                0,
                :profile_type,
                :company_id,
                :color,
                :colorText,
                :textBgColor,
                :profileOpacity,
                :textOpacity,
                :textBgOpacity,
                :socialBgColor,
                :socialTextColor,
                :socialOpacity,
                :profileBgType,
                :socialBgType
            )
        ');

        $stmt->execute([
            'user_id' => $data['user_id'],
            'username' => $data['username'],
            'descr' => $data['descr'] ?? '',
            'profile_type' => $data['profile_type'],
            'company_id' => $data['company_id'] ?? null,
            'color' => $data['color'],
            'colorText' => $data['colorText'],
            'textBgColor' => $data['textBgColor'],
            'profileOpacity' => $data['profileOpacity'],
            'textOpacity' => $data['textOpacity'],
            'textBgOpacity' => $data['textBgOpacity'],
            'socialBgColor' => $data['socialBgColor'],
            'socialTextColor' => $data['socialTextColor'],
            'socialOpacity' => $data['socialOpacity'],
            'profileBgType' => $data['profileBgType'],
            'socialBgType' => $data['socialBgType'],
        ]);
    }

    public function updateMedia(string $username, array $media): void
    {
        if ($media === []) {
            return;
        }

        $parts = [];
        $params = ['username' => $username];
        foreach ($media as $column => $value) {
            $parts[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $sql = 'UPDATE users_info SET ' . implode(', ', $parts) . ' WHERE username = :username';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users_info WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function setCompany(string $username, int $companyId): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE users_info
            SET company_id = :company_id,
                profile_type = \'company\'
            WHERE username = :username
        ');
        $stmt->execute([
            'company_id' => $companyId,
            'username' => $username,
        ]);
    }

    public function clearCompany(string $username): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE users_info
            SET company_id = NULL,
                profile_type = \'personal\'
            WHERE username = :username
        ');
        $stmt->execute([
            'username' => $username,
        ]);
    }

    public function applyCompanyDesign(string $username, int $companyId, array $design): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE users_info SET
                color = :profileColor,
                colorText = :textColor,
                textBgColor = :textBgColor,
                profileOpacity = :profileOpacity,
                textOpacity = :textOpacity,
                textBgOpacity = :textBgOpacity,
                socialBgColor = :socialBgColor,
                socialTextColor = :socialTextColor,
                socialOpacity = :socialOpacity,
                profileBgType = :profileBgType,
                socialBgType = :socialBgType,
                bg = :bg,
                blockImage = :blockImage,
                socialBgImage = :socialBgImage,
                company_logo = :companyLogo,
                company_display_name = :companyDisplayName,
                company_show_logo = :companyShowLogo,
                company_show_name = :companyShowName,
                company_id = :companyId,
                profile_type = \'company\'
            WHERE username = :username
        ');

        $stmt->execute([
            'profileColor' => $design['profileColor'],
            'textColor' => $design['textColor'],
            'textBgColor' => $design['textBgColor'],
            'profileOpacity' => $design['profileOpacity'],
            'textOpacity' => $design['textOpacity'],
            'textBgOpacity' => $design['textBgOpacity'],
            'socialBgColor' => $design['socialBgColor'],
            'socialTextColor' => $design['socialTextColor'],
            'socialOpacity' => $design['socialOpacity'],
            'profileBgType' => $design['profileBgType'],
            'socialBgType' => $design['socialBgType'],
            'bg' => $design['bg'],
            'blockImage' => $design['blockImage'],
            'socialBgImage' => $design['socialBgImage'],
            'companyLogo' => $design['companyLogo'],
            'companyDisplayName' => $design['companyDisplayName'],
            'companyShowLogo' => $design['companyShowLogo'],
            'companyShowName' => $design['companyShowName'],
            'companyId' => $companyId,
            'username' => $username,
        ]);
    }
}

