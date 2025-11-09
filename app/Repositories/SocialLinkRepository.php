<?php

namespace App\Repositories;

use App\DTO\SocialLink;
use mysqli;
use RuntimeException;

class SocialLinkRepository
{
    public function __construct(private mysqli $db)
    {
    }

    /**
     * @return list<SocialLink>
     */
    public function listByProfile(int $profileId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM social_links WHERE profile_id = ? ORDER BY sort_order ASC, id ASC');
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $profileId);
        $stmt->execute();
        $result = $stmt->get_result();

        $links = [];
        while ($row = $result?->fetch_assoc()) {
            $links[] = SocialLink::fromArray($row);
        }

        $stmt->close();

        return $links;
    }

    public function findById(int $id): ?SocialLink
    {
        $stmt = $this->db->prepare('SELECT * FROM social_links WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? SocialLink::fromArray($row) : null;
    }

    public function create(int $profileId, string $platform, ?string $label, string $url, int $sortOrder): SocialLink
    {
        $stmt = $this->db->prepare('INSERT INTO social_links (profile_id, platform, label, url, sort_order) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare social link insert.');
        }
        $stmt->bind_param('isssi', $profileId, $platform, $label, $url, $sortOrder);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to create social link: ' . $stmt->error);
        }
        $id = (int)$stmt->insert_id;
        $stmt->close();

        $link = $this->findById($id);
        if (!$link) {
            throw new RuntimeException('Failed to load created social link.');
        }

        return $link;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function update(int $id, array $fields): SocialLink
    {
        $allowed = ['platform', 'label', 'url', 'sort_order'];
        $setParts = [];
        $params = [];
        $types = '';

        foreach ($allowed as $column) {
            if (array_key_exists($column, $fields)) {
                $setParts[] = "{$column} = ?";
                $value = $fields[$column];
                $params[] = $value;
                $types .= $column === 'sort_order' ? 'i' : 's';
            }
        }

        if (empty($setParts)) {
            $link = $this->findById($id);
            if (!$link) {
                throw new RuntimeException('Social link not found.');
            }
            return $link;
        }

        $sql = 'UPDATE social_links SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare social link update.');
        }

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $index => $param) {
            $bindParams[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to update social link: ' . $stmt->error);
        }
        $stmt->close();

        $link = $this->findById($id);
        if (!$link) {
            throw new RuntimeException('Social link not found after update.');
        }
        return $link;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM social_links WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}




