<?php

namespace App\Repositories;

use App\DTO\OrganizationMember;
use mysqli;
use RuntimeException;

class OrganizationMemberRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function addMember(int $organizationId, int $userId, string $role): OrganizationMember
    {
        $stmt = $this->db->prepare('INSERT INTO organization_members (organization_id, user_id, role) VALUES (?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare member insert.');
        }
        $stmt->bind_param('iis', $organizationId, $userId, $role);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to add member: ' . $stmt->error);
        }
        $id = (int)$stmt->insert_id;
        $stmt->close();

        $member = $this->findById($id);
        if (!$member) {
            throw new RuntimeException('Failed to load newly added member.');
        }

        return $member;
    }

    public function findById(int $id): ?OrganizationMember
    {
        $stmt = $this->db->prepare('
            SELECT m.*, u.email AS user_email,
                   CONCAT_WS(" ", p.first_name, p.last_name) AS user_full_name,
                   p.username_slug AS profile_slug
            FROM organization_members m
            LEFT JOIN users u ON u.id = m.user_id
            LEFT JOIN user_profiles p ON p.user_id = m.user_id
            WHERE m.id = ?
            LIMIT 1
        ');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? OrganizationMember::fromArray($row) : null;
    }

    public function findByOrganizationAndUser(int $organizationId, int $userId): ?OrganizationMember
    {
        $stmt = $this->db->prepare('
            SELECT m.*, u.email AS user_email,
                   CONCAT_WS(" ", p.first_name, p.last_name) AS user_full_name,
                   p.username_slug AS profile_slug
            FROM organization_members m
            LEFT JOIN users u ON u.id = m.user_id
            LEFT JOIN user_profiles p ON p.user_id = m.user_id
            WHERE m.organization_id = ? AND m.user_id = ?
            LIMIT 1
        ');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('ii', $organizationId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? OrganizationMember::fromArray($row) : null;
    }

    /**
     * @return list<OrganizationMember>
     */
    public function listMembers(int $organizationId): array
    {
        $stmt = $this->db->prepare('
            SELECT m.*, u.email AS user_email,
                   CONCAT_WS(" ", p.first_name, p.last_name) AS user_full_name,
                   p.username_slug AS profile_slug
            FROM organization_members m
            LEFT JOIN users u ON u.id = m.user_id
            LEFT JOIN user_profiles p ON p.user_id = m.user_id
            WHERE m.organization_id = ?
            ORDER BY FIELD(m.role, "owner", "admin", "member"), user_full_name ASC
        ');
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $organizationId);
        $stmt->execute();
        $result = $stmt->get_result();

        $members = [];
        while ($row = $result?->fetch_assoc()) {
            $members[] = OrganizationMember::fromArray($row);
        }
        $stmt->close();

        return $members;
    }

    public function updateRole(int $memberId, string $role): OrganizationMember
    {
        $stmt = $this->db->prepare('UPDATE organization_members SET role = ? WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare member role update.');
        }
        $stmt->bind_param('si', $role, $memberId);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to update member role: ' . $stmt->error);
        }
        $stmt->close();

        $member = $this->findById($memberId);
        if (!$member) {
            throw new RuntimeException('Member not found after role update.');
        }

        return $member;
    }

    public function removeMember(int $memberId): void
    {
        $stmt = $this->db->prepare('DELETE FROM organization_members WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $memberId);
            $stmt->execute();
            $stmt->close();
        }
    }
}


