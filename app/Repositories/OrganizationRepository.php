<?php

namespace App\Repositories;

use App\DTO\Organization;
use App\Support\InviteCode;
use App\Support\Slugger;
use mysqli;
use RuntimeException;

class OrganizationRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function create(int $ownerId, string $name, ?string $description, ?string $contactEmail, ?string $contactPhone, ?string $address): Organization
    {
        $slug = $this->generateUniqueSlug($name);
        $inviteCode = $this->generateUniqueInviteCode();

        $stmt = $this->db->prepare("
            INSERT INTO organizations (name, slug, description, contact_email, contact_phone, address, invite_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare organization insert.');
        }
        $stmt->bind_param('sssssss', $name, $slug, $description, $contactEmail, $contactPhone, $address, $inviteCode);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to create organization: ' . $stmt->error);
        }
        $orgId = (int)$stmt->insert_id;
        $stmt->close();

        $organization = $this->findById($orgId);
        if (!$organization) {
            throw new RuntimeException('Failed to load created organization.');
        }

        return $organization;
    }

    public function findById(int $id): ?Organization
    {
        $stmt = $this->db->prepare('SELECT * FROM organizations WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? Organization::fromArray($row) : null;
    }

    public function findByInviteCode(string $code): ?Organization
    {
        $stmt = $this->db->prepare('SELECT * FROM organizations WHERE invite_code = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? Organization::fromArray($row) : null;
    }

    /**
     * @return list<Organization>
     */
    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*
            FROM organizations o
            JOIN organization_members m ON m.organization_id = o.id
            WHERE m.user_id = ?
            ORDER BY o.created_at DESC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $organizations = [];
        while ($row = $result?->fetch_assoc()) {
            $organizations[] = Organization::fromArray($row);
        }
        $stmt->close();

        return $organizations;
    }

    /**
     * @return list<Organization>
     */
    public function listAll(?string $query = null, int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM organizations';
        $params = [];
        $types = '';

        if ($query !== null && $query !== '') {
            $sql .= ' WHERE name LIKE ? OR slug LIKE ?';
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

        $organizations = [];
        while ($row = $result?->fetch_assoc()) {
            $organizations[] = Organization::fromArray($row);
        }
        $stmt->close();

        return $organizations;
    }

    public function update(int $id, array $fields): Organization
    {
        $allowed = [
            'name',
            'description',
            'contact_email',
            'contact_phone',
            'address',
            'logo_path',
            'design_theme',
            'design_config',
            'status',
        ];

        $setParts = [];
        $params = [];
        $types = '';

        foreach ($allowed as $column) {
            if (array_key_exists($column, $fields)) {
                $setParts[] = "{$column} = ?";
                $value = $fields[$column];
                if ($column === 'design_config' && is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                $params[] = $value;
                $types .= 's';
            }
        }

        if (empty($setParts)) {
            $organization = $this->findById($id);
            if (!$organization) {
                throw new RuntimeException('Organization not found.');
            }
            return $organization;
        }

        $sql = 'UPDATE organizations SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare organization update.');
        }

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $index => $param) {
            $bindParams[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to update organization: ' . $stmt->error);
        }
        $stmt->close();

        $organization = $this->findById($id);
        if (!$organization) {
            throw new RuntimeException('Organization not found after update.');
        }

        return $organization;
    }

    public function refreshInviteCode(int $id): Organization
    {
        $inviteCode = $this->generateUniqueInviteCode();
        $stmt = $this->db->prepare('UPDATE organizations SET invite_code = ? WHERE id = ?');
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare invite refresh.');
        }
        $stmt->bind_param('si', $inviteCode, $id);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to refresh invite: ' . $stmt->error);
        }
        $stmt->close();

        $organization = $this->findById($id);
        if (!$organization) {
            throw new RuntimeException('Organization not found after refreshing invite.');
        }

        return $organization;
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Slugger::slug($name);
        $slug = $baseSlug;
        $suffix = 1;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM organizations WHERE slug = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $exists = $stmt->get_result()?->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    private function generateUniqueInviteCode(): string
    {
        do {
            $code = InviteCode::generate();
            $stmt = $this->db->prepare('SELECT 1 FROM organizations WHERE invite_code = ? LIMIT 1');
            if (!$stmt) {
                break;
            }
            $stmt->bind_param('s', $code);
            $stmt->execute();
            $exists = $stmt->get_result()?->num_rows > 0;
            $stmt->close();
        } while ($exists ?? false);

        return $code ?? InviteCode::generate();
    }
}


