<?php

namespace App\DTO;

final class SocialLink
{
    public function __construct(
        public readonly int $id,
        public readonly int $profileId,
        public readonly string $platform,
        public readonly ?string $label,
        public readonly string $url,
        public readonly int $sortOrder,
        public readonly string $createdAt
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['profile_id'],
            (string)$row['platform'],
            $row['label'] !== null ? (string)$row['label'] : null,
            (string)$row['url'],
            (int)$row['sort_order'],
            (string)$row['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'label' => $this->label,
            'url' => $this->url,
            'sort_order' => $this->sortOrder,
        ];
    }
}








