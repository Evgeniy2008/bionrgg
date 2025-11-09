<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetchColumn();
    }

    public function create(string $username, string $passwordHash, string $profileType): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (username, password_hash, profile_type)
            VALUES (:username, :password_hash, :profile_type)
        ');
        $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
            'profile_type' => $profileType,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([
            'hash' => $passwordHash,
            'id' => $userId,
        ]);
    }
}














