<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanyDesignRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByCompanyId(int $companyId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM company_designs WHERE company_id = :company_id LIMIT 1');
        $stmt->execute(['company_id' => $companyId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function create(int $companyId, array $defaults): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO company_designs (
                company_id,
                profileColor,
                textColor,
                textBgColor,
                profileOpacity,
                textOpacity,
                textBgOpacity,
                socialBgColor,
                socialTextColor,
                socialOpacity,
                avatar,
                bg,
                blockImage,
                socialBgImage,
                profileBgType,
                socialBgType,
                display_name,
                tagline,
                show_logo,
                show_name
            ) VALUES (
                :company_id,
                :profileColor,
                :textColor,
                :textBgColor,
                :profileOpacity,
                :textOpacity,
                :textBgOpacity,
                :socialBgColor,
                :socialTextColor,
                :socialOpacity,
                :avatar,
                :bg,
                :blockImage,
                :socialBgImage,
                :profileBgType,
                :socialBgType,
                :display_name,
                :tagline,
                :show_logo,
                :show_name
            )
        ');

        $stmt->execute([
            'company_id' => $companyId,
            'profileColor' => $defaults['profileColor'],
            'textColor' => $defaults['textColor'],
            'textBgColor' => $defaults['textBgColor'],
            'profileOpacity' => $defaults['profileOpacity'],
            'textOpacity' => $defaults['textOpacity'],
            'textBgOpacity' => $defaults['textBgOpacity'],
            'socialBgColor' => $defaults['socialBgColor'],
            'socialTextColor' => $defaults['socialTextColor'],
            'socialOpacity' => $defaults['socialOpacity'],
            'avatar' => $defaults['avatar'],
            'bg' => $defaults['bg'],
            'blockImage' => $defaults['blockImage'],
            'socialBgImage' => $defaults['socialBgImage'],
            'profileBgType' => $defaults['profileBgType'],
            'socialBgType' => $defaults['socialBgType'],
            'display_name' => $defaults['display_name'],
            'tagline' => $defaults['tagline'],
            'show_logo' => $defaults['show_logo'],
            'show_name' => $defaults['show_name'],
        ]);
    }

    public function update(int $companyId, array $data): void
    {
        if ($data === []) {
            return;
        }

        $parts = [];
        $params = ['company_id' => $companyId];

        foreach ($data as $column => $value) {
            $parts[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = 'UPDATE company_designs SET ' . implode(', ', $parts) . ' WHERE company_id = :company_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}






















