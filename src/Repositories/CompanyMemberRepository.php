<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CompanyMemberRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function addMember(int $companyId, int $userId, string $username, string $role): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO company_members (company_id, user_id, username, role)
            VALUES (:company_id, :user_id, :username, :role)
        ');

        $stmt->execute([
            'company_id' => $companyId,
            'user_id' => $userId,
            'username' => $username,
            'role' => $role,
        ]);
    }

    public function findMember(int $companyId, string $username): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM company_members
            WHERE company_id = :company_id AND username = :username
            LIMIT 1
        ');
        $stmt->execute([
            'company_id' => $companyId,
            'username' => $username,
        ]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function membersCount(int $companyId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM company_members WHERE company_id = :company_id');
        $stmt->execute(['company_id' => $companyId]);
        return (int)$stmt->fetchColumn();
    }

    public function listMembers(int $companyId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT cm.*, ui.company_tagline
            FROM company_members cm
            LEFT JOIN users_info ui ON ui.username = cm.username
            WHERE cm.company_id = :company_id
            ORDER BY CASE WHEN cm.role = \'owner\' THEN 0 ELSE 1 END, cm.username
        ');
        $stmt->execute(['company_id' => $companyId]);

        return $stmt->fetchAll();
    }

    public function removeMember(int $companyId, string $username): void
    {
        $stmt = $this->pdo->prepare('
            DELETE FROM company_members
            WHERE company_id = :company_id AND username = :username
        ');
        $stmt->execute([
            'company_id' => $companyId,
            'username' => $username,
        ]);
    }
}














