<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanyRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(string $companyKey, string $companyName, string $ownerUsername, int $ownerUserId): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO companies (
                company_key,
                company_name,
                owner_username,
                owner_user_id,
                unified_design_enabled
            ) VALUES (
                :company_key,
                :company_name,
                :owner_username,
                :owner_user_id,
                0
            )
        ');

        $stmt->execute([
            'company_key' => $companyKey,
            'company_name' => $companyName,
            'owner_username' => $ownerUsername,
            'owner_user_id' => $ownerUserId,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function findByKey(string $companyKey): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM companies WHERE company_key = :company_key LIMIT 1');
        $stmt->execute(['company_key' => $companyKey]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM companies WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function updateSettings(int $companyId, array $settings): void
    {
        if ($settings === []) {
            return;
        }

        $parts = [];
        $params = ['id' => $companyId];
        foreach ($settings as $column => $value) {
            $parts[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $sql = 'UPDATE companies SET ' . implode(', ', $parts) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function delete(int $companyId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM companies WHERE id = :id');
        $stmt->execute(['id' => $companyId]);
    }
}














