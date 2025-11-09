<?php

namespace App\Services;

use App\DTO\Organization;
use App\Repositories\OrganizationMemberRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\UserRepository;
use App\Support\MediaStorage;
use RuntimeException;

class OrganizationService
{
    public function __construct(
        private OrganizationRepository $organizations,
        private OrganizationMemberRepository $members,
        private UserRepository $users,
        private MediaStorage $storage
    ) {
    }

    public function listForUser(int $userId): array
    {
        $organizations = $this->organizations->listForUser($userId);
        return array_map(fn(Organization $org) => $org->toArray(), $organizations);
    }

    public function create(int $ownerId, string $name, ?string $description, ?string $contactEmail, ?string $contactPhone, ?string $address): array
    {
        $owner = $this->users->findById($ownerId);
        if (!$owner) {
            throw new RuntimeException('User not found.');
        }

        $organization = $this->organizations->create($ownerId, $name, $description, $contactEmail, $contactPhone, $address);
        $this->members->addMember($organization->id, $ownerId, 'owner');

        return $organization->toArray();
    }

    public function getDetails(int $organizationId, int $userId): array
    {
        $organization = $this->organizations->findById($organizationId);
        if (!$organization) {
            throw new RuntimeException('Organization not found.');
        }

        $member = $this->members->findByOrganizationAndUser($organizationId, $userId);
        if (!$member) {
            throw new RuntimeException('Access denied.');
        }

        return [
            'organization' => $organization->toArray(),
            'member' => $member->toArray(),
            'members' => array_map(
                fn($m) => $m->toArray(),
                $this->members->listMembers($organizationId)
            ),
        ];
    }

    public function refreshInvite(int $organizationId, int $userId): array
    {
        $this->assertOwner($organizationId, $userId);

        $organization = $this->organizations->refreshInviteCode($organizationId);
        return $organization->toArray();
    }

    public function update(int $organizationId, int $userId, array $data): array
    {
        $member = $this->members->findByOrganizationAndUser($organizationId, $userId);
        if (!$member || !in_array($member->role, ['owner', 'admin'], true)) {
            throw new RuntimeException('Access denied.');
        }

        $fields = array_intersect_key($data, array_flip([
            'name',
            'description',
            'contact_email',
            'contact_phone',
            'address',
            'design_theme',
            'design_config',
        ]));

        $organization = $this->organizations->update($organizationId, $fields);
        return $organization->toArray();
    }

    public function updateLogo(int $organizationId, int $userId, array $file): array
    {
        $member = $this->members->findByOrganizationAndUser($organizationId, $userId);
        if (!$member || !in_array($member->role, ['owner', 'admin'], true)) {
            throw new RuntimeException('Access denied.');
        }

        $organization = $this->organizations->findById($organizationId);
        if (!$organization) {
            throw new RuntimeException('Organization not found.');
        }

        $newPath = $this->storage->storeImage($file, 'organization-logos');
        if ($organization->logoPath) {
            $this->storage->delete($organization->logoPath);
        }

        $organization = $this->organizations->update($organizationId, ['logo_path' => $newPath]);
        return $organization->toArray();
    }

    public function joinByInvite(int $userId, string $inviteCode): array
    {
        $organization = $this->organizations->findByInviteCode($inviteCode);
        if (!$organization) {
            throw new RuntimeException('Organization not found.');
        }

        $member = $this->members->findByOrganizationAndUser($organization->id, $userId);
        if ($member) {
            throw new RuntimeException('Already a member.');
        }

        $user = $this->users->findById($userId);
        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $this->members->addMember($organization->id, $userId, 'member');

        return $organization->toArray();
    }

    public function listMembers(int $organizationId, int $userId): array
    {
        $member = $this->members->findByOrganizationAndUser($organizationId, $userId);
        if (!$member) {
            throw new RuntimeException('Access denied.');
        }

        return array_map(fn($m) => $m->toArray(), $this->members->listMembers($organizationId));
    }

    public function updateMemberRole(int $organizationId, int $currentUserId, int $memberId, string $role): array
    {
        $this->assertOwner($organizationId, $currentUserId);

        $member = $this->members->findById($memberId);
        if (!$member || $member->organizationId !== $organizationId) {
            throw new RuntimeException('Member not found.');
        }

        if ($member->role === 'owner') {
            throw new RuntimeException('Cannot change owner role.');
        }

        $this->members->updateRole($memberId, $role);

        return array_map(fn($m) => $m->toArray(), $this->members->listMembers($organizationId));
    }

    public function removeMember(int $organizationId, int $currentUserId, int $memberId): array
    {
        $this->assertOwner($organizationId, $currentUserId);

        $member = $this->members->findById($memberId);
        if (!$member || $member->organizationId !== $organizationId) {
            throw new RuntimeException('Member not found.');
        }

        if ($member->role === 'owner') {
            throw new RuntimeException('Cannot remove owner.');
        }

        $this->members->removeMember($memberId);

        return array_map(fn($m) => $m->toArray(), $this->members->listMembers($organizationId));
    }

    private function assertOwner(int $organizationId, int $userId): void
    {
        $member = $this->members->findByOrganizationAndUser($organizationId, $userId);
        if (!$member || $member->role !== 'owner') {
            throw new RuntimeException('Only owner can perform this action.');
        }
    }
}


