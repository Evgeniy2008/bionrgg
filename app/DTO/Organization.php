<?php

namespace App\DTO;

final class Organization
{
    /**
     * @param array<string, mixed>|null $designConfig
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly ?string $description,
        public readonly ?string $contactEmail,
        public readonly ?string $contactPhone,
        public readonly ?string $address,
        public readonly ?string $logoPath,
        public readonly string $inviteCode,
        public readonly string $designTheme,
        public readonly ?array $designConfig,
        public readonly string $status,
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
            (string)$row['name'],
            (string)$row['slug'],
            $row['description'] !== null ? (string)$row['description'] : null,
            $row['contact_email'] !== null ? (string)$row['contact_email'] : null,
            $row['contact_phone'] !== null ? (string)$row['contact_phone'] : null,
            $row['address'] !== null ? (string)$row['address'] : null,
            $row['logo_path'] !== null ? (string)$row['logo_path'] : null,
            (string)$row['invite_code'],
            (string)$row['design_theme'],
            $row['design_config'] ? json_decode((string)$row['design_config'], true) ?: null : null,
            (string)$row['status'],
            (string)$row['created_at'],
            (string)$row['updated_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'address' => $this->address,
            'logo_path' => $this->logoPath,
            'invite_code' => $this->inviteCode,
            'design_theme' => $this->designTheme,
            'design_config' => $this->designConfig,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}






