<?php

namespace App\Services;

use App\Repositories\AdminLogRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\UserRepository;
use RuntimeException;

class AdminService
{
    public function __construct(
        private UserRepository $users,
        private OrganizationRepository $organizations,
        private AdminLogRepository $logs
    ) {
    }

    private function ensureAdmin(int $userId): void
    {
        $user = $this->users->findById($userId);
        if (!$user || $user->role !== 'admin') {
            throw new RuntimeException('Admin privileges required.');
        }
    }

    public function listUsers(int $adminId, ?string $query = null, int $limit = 50, int $offset = 0): array
    {
        $this->ensureAdmin($adminId);
        $users = $this->users->listAll($query, $limit, $offset);

        return array_map(fn($user) => $user->toPublicArray() + ['role' => $user->role], $users);
    }

    public function listOrganizations(int $adminId, ?string $query = null, int $limit = 50, int $offset = 0): array
    {
        $this->ensureAdmin($adminId);
        $organizations = $this->organizations->listAll($query, $limit, $offset);

        return array_map(fn($org) => $org->toArray(), $organizations);
    }

    public function updateOrganizationStatus(int $adminId, int $organizationId, string $status): array
    {
        $this->ensureAdmin($adminId);

        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw new RuntimeException('Invalid status.');
        }

        $organization = $this->organizations->update($organizationId, ['status' => $status]);
        $this->logs->record($adminId, 'update_org_status', 'organization', $organizationId, ['status' => $status]);

        return $organization->toArray();
    }

    public function deleteUser(int $adminId, int $userId): void
    {
        $this->ensureAdmin($adminId);

        if ($adminId === $userId) {
            throw new RuntimeException('Cannot delete own account.');
        }

        $user = $this->users->findById($userId);
        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $this->users->delete($userId);
        $this->logs->record($adminId, 'delete_user', 'user', $userId, ['email' => $user->email]);
    }

    public function listLogs(int $adminId, int $limit = 100, int $offset = 0): array
    {
        $this->ensureAdmin($adminId);
        $logs = $this->logs->list($limit, $offset);

        return array_map(fn($log) => $log->toArray(), $logs);
    }
}




