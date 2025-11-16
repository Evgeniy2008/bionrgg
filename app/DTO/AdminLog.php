<?php

namespace App\DTO;

final class AdminLog
{
    public function __construct(
        public readonly int $id,
        public readonly int $adminId,
        public readonly string $action,
        public readonly string $targetType,
        public readonly ?int $targetId,
        public readonly ?array $meta,
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
            (int)$row['admin_id'],
            (string)$row['action'],
            (string)$row['target_type'],
            isset($row['target_id']) ? (int)$row['target_id'] : null,
            $row['meta'] ? json_decode((string)$row['meta'], true) : null,
            (string)$row['created_at']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'admin_id' => $this->adminId,
            'action' => $this->action,
            'target_type' => $this->targetType,
            'target_id' => $this->targetId,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
        ];
    }
}






