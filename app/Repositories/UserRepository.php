<?php

namespace App\Repositories;

use mysqli;
use App\DTO\User;

class UserRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc() ?: null;
        $stmt->close();

        return $row ? User::fromArray($row) : null;
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc() ?: null;
        $stmt->close();

        return $row ? User::fromArray($row) : null;
    }

    public function create(string $email, string $passwordHash, string $fullName): User
    {
        $stmt = $this->db->prepare('INSERT INTO users (email, password_hash, full_name) VALUES (?, ?, ?)');
        if (!$stmt) {
            throw new \RuntimeException('Failed to prepare user insert statement.');
        }
        $stmt->bind_param('sss', $email, $passwordHash, $fullName);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new \RuntimeException('Failed to create user: ' . $stmt->error);
        }
        $id = (int)$stmt->insert_id;
        $stmt->close();

        $user = $this->findById($id);
        if (!$user) {
            throw new \RuntimeException('Failed to load newly created user.');
        }

        return $user;
    }

    /**
     * @return list<User>
     */
    public function listAll(?string $query = null, int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM users';
        $params = [];
        $types = '';

        if ($query !== null && $query !== '') {
            $sql .= ' WHERE email LIKE ? OR full_name LIKE ?';
            $wildcard = '%' . $query . '%';
            $params[] = $wildcard;
            $params[] = $wildcard;
            $types .= 'ss';
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $index => $param) {
            $bindParams[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result?->fetch_assoc()) {
            $users[] = User::fromArray($row);
        }
        $stmt->close();

        return $users;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}


