<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Repositories\CompanyDesignRepository;
use App\Repositories\CompanyMemberRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserInfoRepository;
use App\Repositories\UserRepository;
use PDO;

final class CompanyService
{
    private const KEY_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private readonly CompanyRepository $companies;
    private readonly CompanyMemberRepository $members;
    private readonly CompanyDesignRepository $designs;
    private readonly UserRepository $users;
    private readonly UserInfoRepository $userInfo;
    private readonly DesignService $designService;

    public function __construct(private readonly PDO $pdo)
    {
        $this->companies = new CompanyRepository($pdo);
        $this->members = new CompanyMemberRepository($pdo);
        $this->designs = new CompanyDesignRepository($pdo);
        $this->users = new UserRepository($pdo);
        $this->userInfo = new UserInfoRepository($pdo);
        $this->designService = new DesignService($pdo);
    }

    public function createCompany(array $ownerUser, string $companyName, array $designDefaults = []): array
    {
        $companyKey = $this->generateUniqueKey();
        $ownerId = (int)$ownerUser['id'];
        $ownerUsername = $ownerUser['username'];

        $companyId = $this->companies->create($companyKey, $companyName, $ownerUsername, $ownerId);
        $this->members->addMember($companyId, $ownerId, $ownerUsername, 'owner');
        $this->userInfo->setCompany($ownerUsername, $companyId);

        $this->designService->ensureDesign($companyId, $designDefaults);

        return [
            'company_id' => $companyId,
            'company_key' => $companyKey,
            'company_name' => $companyName,
            'role' => 'owner',
        ];
    }

    public function joinCompany(string $username, string $companyKey): array
    {
        $company = $this->companies->findByKey(strtoupper($companyKey));
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        $user = $this->users->findByUsername($username);
        if (!$user) {
            throw HttpException::notFound('User not found');
        }

        $existingMember = $this->members->findMember((int)$company['id'], $username);
        if ($existingMember) {
            throw HttpException::conflict('User already belongs to this company');
        }

        $this->members->addMember((int)$company['id'], (int)$user['id'], $username, 'member');
        $this->userInfo->setCompany($username, (int)$company['id']);

        if ((int)$company['unified_design_enabled'] === 1) {
            $this->designService->applyToMember((int)$company['id'], $username);
        }

        return [
            'company_id' => (int)$company['id'],
            'company_key' => $company['company_key'],
            'company_name' => $company['company_name'],
            'role' => 'member',
        ];
    }

    public function getCompanyForUser(int $companyId, string $username): array
    {
        $company = $this->companies->findById($companyId);
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        $member = $this->members->findMember($companyId, $username);
        if (!$member) {
            throw HttpException::forbidden('User is not part of this company');
        }

        $design = null;
        if ((int)$company['unified_design_enabled'] === 1 || $company['owner_username'] === $username) {
            $design = $this->designs->findByCompanyId($companyId);
        }

        return [
            'id' => $companyId,
            'company_key' => $company['company_key'],
            'company_name' => $company['company_name'],
            'owner_username' => $company['owner_username'],
            'unified_design_enabled' => (bool)$company['unified_design_enabled'],
            'role' => $member['role'],
            'members_count' => $this->members->membersCount($companyId),
            'design' => $design,
            'members' => $this->members->listMembers($companyId),
        ];
    }

    public function updateSettings(int $companyId, string $username, array $settings): void
    {
        $company = $this->companies->findById($companyId);
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        if ($company['owner_username'] !== $username) {
            throw HttpException::forbidden('Only the owner can update company settings');
        }

        $this->companies->updateSettings($companyId, $settings);

        if (isset($settings['unified_design_enabled']) && (int)$settings['unified_design_enabled'] === 1) {
            $this->designService->syncToCompanyMembers($companyId);
        }
    }

    public function updateDesign(int $companyId, string $username, array $designData): void
    {
        $company = $this->companies->findById($companyId);
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        if ($company['owner_username'] !== $username) {
            throw HttpException::forbidden('Only the owner can update the company design');
        }

        $this->designService->ensureDesign($companyId);
        $this->designService->updateDesign($companyId, $designData);

        if ((int)$company['unified_design_enabled'] === 1) {
            $this->designService->syncToCompanyMembers($companyId);
        }
    }

    public function deleteCompany(int $companyId, string $username): void
    {
        $company = $this->companies->findById($companyId);
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        if ($company['owner_username'] !== $username) {
            throw HttpException::forbidden('Only the owner can delete the company');
        }

        foreach ($this->members->listMembers($companyId) as $member) {
            $this->userInfo->clearCompany($member['username']);
        }

        $this->companies->delete($companyId);
    }

    public function removeMember(int $companyId, string $ownerUsername, string $memberUsername): void
    {
        $company = $this->companies->findById($companyId);
        if (!$company) {
            throw HttpException::notFound('Company not found');
        }

        if ($company['owner_username'] !== $ownerUsername) {
            throw HttpException::forbidden('Only the owner can remove members');
        }

        if ($memberUsername === $ownerUsername) {
            throw HttpException::badRequest('Owner cannot remove themselves. Delete the company instead.');
        }

        $member = $this->members->findMember($companyId, $memberUsername);
        if (!$member) {
            throw HttpException::notFound('Member not found in this company');
        }

        $this->members->removeMember($companyId, $memberUsername);
        $this->userInfo->clearCompany($memberUsername);
    }

    private function generateUniqueKey(): string
    {
        $characters = self::KEY_CHARSET;
        $length = 8;
        $maxIndex = strlen($characters) - 1;

        do {
            $key = '';
            for ($i = 0; $i < $length; $i++) {
                $key .= $characters[random_int(0, $maxIndex)];
            }

            $exists = $this->companies->findByKey($key) !== null;
        } while ($exists);

        return $key;
    }
}

