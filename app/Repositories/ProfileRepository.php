<?php

namespace App\Repositories;

use App\DTO\Profile;
use App\Support\Slugger;
use mysqli;
use RuntimeException;

class ProfileRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function createDefault(int $userId, string $fullName, string $email): Profile
    {
        [$firstName, $lastName] = $this->splitName($fullName);
        $baseSlug = Slugger::slug($firstName !== '' ? $firstName . '-' . $lastName : explode('@', $email)[0]);
        $slug = $this->ensureUniqueSlug($baseSlug);

        $stmt = $this->db->prepare("
            INSERT INTO user_profiles (user_id, username_slug, first_name, last_name)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare profile insert.');
        }
        $stmt->bind_param('isss', $userId, $slug, $firstName, $lastName);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to create profile: ' . $stmt->error);
        }
        $stmt->close();

        $profile = $this->findByUserId($userId);
        if ($profile) {
            return $profile;
        }

        throw new RuntimeException('Failed to load newly created profile.');
    }

    /**
     * @param array<string, string|null> $fields
     */
    public function updateMedia(int $profileId, array $fields): Profile
    {
        $allowed = [
            'avatar_path',
            'background_path',
            'qr_svg_path',
            'pdf_path',
        ];

        $setParts = [];
        $params = [];
        $types = '';

        foreach ($allowed as $column) {
            if (array_key_exists($column, $fields)) {
                $setParts[] = "{$column} = ?";
                $value = $fields[$column];
                $params[] = $value;
                $types .= 's';
            }
        }

        if (empty($setParts)) {
            $profile = $this->findById($profileId);
            if (!$profile) {
                throw new RuntimeException('Profile not found.');
            }
            return $profile;
        }

        $sql = 'UPDATE user_profiles SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = $profileId;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare media update.');
        }

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $index => $param) {
            $bindParams[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to update media: ' . $stmt->error);
        }
        $stmt->close();

        $profile = $this->findById($profileId);
        if ($profile) {
            return $profile;
        }

        throw new RuntimeException('Profile not found after media update.');
    }

    public function findByUserId(int $userId): ?Profile
    {
        $stmt = $this->db->prepare('SELECT * FROM user_profiles WHERE user_id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $profile = Profile::fromArray($row);
        $profile->socialLinks = [];

        return $profile;
    }

    public function findBySlug(string $slug): ?Profile
    {
        $stmt = $this->db->prepare('SELECT * FROM user_profiles WHERE username_slug = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? Profile::fromArray($row) : null;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function update(int $profileId, array $fields): Profile
    {
        $allowed = [
            'first_name',
            'last_name',
            'position_title',
            'bio',
            'phone',
            'email_public',
            'address',
            'design_theme',
            'language',
        ];

        $setParts = [];
        $params = [];
        $types = '';

        foreach ($allowed as $column) {
            if (array_key_exists($column, $fields)) {
                $setParts[] = "{$column} = ?";
                $value = $fields[$column];
                $params[] = $value;
                $types .= 's';
            }
        }

        if (empty($setParts)) {
            $profile = $this->findById($profileId);
            if (!$profile) {
                throw new RuntimeException('Profile not found.');
            }
            return $profile;
        }

        $sql = 'UPDATE user_profiles SET ' . implode(', ', $setParts) . ' WHERE id = ?';
        $types .= 'i';
        $params[] = $profileId;

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare profile update.');
        }

        $bindParams = [];
        $bindParams[] = &$types;
        foreach ($params as $index => $param) {
            $bindParams[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bindParams);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to update profile: ' . $stmt->error);
        }
        $stmt->close();

        $profile = $this->findById($profileId);
        if ($profile) {
            return $profile;
        }

        throw new RuntimeException('Profile not found after update.');
    }

    public function findById(int $id): ?Profile
    {
        $stmt = $this->db->prepare('SELECT * FROM user_profiles WHERE id = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        return $row ? Profile::fromArray($row) : null;
    }

    private function splitName(string $fullName): array
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/u', $fullName) ?: [];
        $first = array_shift($parts) ?? '';
        $last = implode(' ', $parts);

        return [$first, $last];
    }

    private function ensureUniqueSlug(string $baseSlug): string
    {
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
        $stmt = $this->db->prepare('SELECT 1 FROM user_profiles WHERE username_slug = ? LIMIT 1');
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $exists = $stmt->get_result()?->num_rows > 0;
        $stmt->close();

        return $exists;
    }
}


