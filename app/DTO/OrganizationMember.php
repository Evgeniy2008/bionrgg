<?php

namespace App\DTO;

final class OrganizationMember
{
    public function __construct(
        public readonly int $id,
        public readonly int $organizationId,
        public readonly int $userId,
        public readonly string $role,
        public readonly string $joinedAt,
        public readonly ?string $userEmail,
        public readonly ?string $userFullName,
        public readonly ?string $profileSlug
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['organization_id'],
            (int)$row['user_id'],
            (string)$row['role'],
            (string)$row['joined_at'],
            $row['user_email'] ?? null,
            $row['user_full_name'] ?? null,
            $row['profile_slug'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
            'role' => $this->role,
            'joined_at' => $this->joinedAt,
            'user_email' => $this->userEmail,
            'user_full_name' => $this->userFullName,
            'profile_slug' => $this->profileSlug,
        ];
    }
}



