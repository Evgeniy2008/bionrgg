<?php

namespace App\Repositories;

use App\DTO\AdminLog;
use mysqli;

class AdminLogRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function record(int $adminId, string $action, string $targetType, ?int $targetId = null, ?array $meta = null): void
    {
        $stmt = $this->db->prepare('INSERT INTO admin_logs (admin_id, action, target_type, target_id, meta) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            return;
        }
        $metaJson = $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        $stmt->bind_param('issis', $adminId, $action, $targetType, $targetId, $metaJson);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * @return list<AdminLog>
     */
    public function list(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare('SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT ? OFFSET ?');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($row = $result?->fetch_assoc()) {
            $logs[] = AdminLog::fromArray($row);
        }
        $stmt->close();

        return $logs;
    }
}








