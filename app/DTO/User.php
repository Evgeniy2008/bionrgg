<?php

namespace App\DTO;

final class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $fullName,
        public readonly string $role,
        public readonly bool $isVerified,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id'],
            (string)$row['email'],
            (string)$row['password_hash'],
            (string)$row['full_name'],
            (string)$row['role'],
            (bool)$row['is_verified'],
            (string)$row['created_at'],
            (string)$row['updated_at']
        );
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'full_name' => $this->fullName,
            'role' => $this->role,
            'is_verified' => $this->isVerified,
            'created_at' => $this->createdAt,
        ];
    }
}








