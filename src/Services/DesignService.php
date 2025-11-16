<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CompanyDesignRepository;
use App\Repositories\CompanyMemberRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserInfoRepository;
use PDO;

final class DesignService
{
    private readonly CompanyDesignRepository $designs;
    private readonly CompanyRepository $companies;
    private readonly CompanyMemberRepository $members;
    private readonly UserInfoRepository $userInfo;

    public function __construct(private readonly PDO $pdo)
    {
        $this->designs = new CompanyDesignRepository($pdo);
        $this->companies = new CompanyRepository($pdo);
        $this->members = new CompanyMemberRepository($pdo);
        $this->userInfo = new UserInfoRepository($pdo);
    }

    public function ensureDesign(int $companyId, array $defaults = []): array
    {
        $design = $this->designs->findByCompanyId($companyId);
        if ($design) {
            return $design;
        }

        $defaultDesign = array_merge($this->defaultDesign(), $defaults);
        $this->designs->create($companyId, $defaultDesign);
        return $this->designs->findByCompanyId($companyId) ?? $defaultDesign;
    }

    public function updateDesign(int $companyId, array $data): void
    {
        $this->designs->update($companyId, $data);
    }

    public function syncToCompanyMembers(int $companyId): void
    {
        $design = $this->ensureDesign($companyId);
        $company = $this->companies->findById($companyId);
        if (!$company) {
            return;
        }

        $displayName = $design['display_name'] ?? $company['company_name'];
        $preparedDesign = $this->prepareDesignForUpdate($design, $displayName);

        foreach ($this->members->listMembers($companyId) as $member) {
            $this->userInfo->applyCompanyDesign($member['username'], $companyId, $preparedDesign);
        }
    }

    public function applyToMember(int $companyId, string $username): void
    {
        $design = $this->ensureDesign($companyId);
        $company = $this->companies->findById($companyId);
        if (!$company) {
            return;
        }

        $displayName = $design['display_name'] ?? $company['company_name'];
        $preparedDesign = $this->prepareDesignForUpdate($design, $displayName);
        $this->userInfo->applyCompanyDesign($username, $companyId, $preparedDesign);
    }

    private function defaultDesign(): array
    {
        return [
            'profileColor' => '#2572ad',
            'textColor' => '#ffffff',
            'textBgColor' => '',
            'profileOpacity' => 100,
            'textOpacity' => 100,
            'textBgOpacity' => 100,
            'socialBgColor' => '#000000',
            'socialTextColor' => '#ffffff',
            'socialOpacity' => 90,
            'avatar' => null,
            'bg' => null,
            'blockImage' => null,
            'socialBgImage' => null,
            'profileBgType' => 'color',
            'socialBgType' => 'color',
            'display_name' => null,
            'tagline' => null,
            'show_logo' => 1,
            'show_name' => 1,
        ];
    }

    private function prepareDesignForUpdate(array $design, ?string $displayName): array
    {
        return [
            'profileColor' => $design['profileColor'],
            'textColor' => $design['textColor'],
            'textBgColor' => $design['textBgColor'],
            'profileOpacity' => (int)$design['profileOpacity'],
            'textOpacity' => (int)$design['textOpacity'],
            'textBgOpacity' => (int)$design['textBgOpacity'],
            'socialBgColor' => $design['socialBgColor'],
            'socialTextColor' => $design['socialTextColor'],
            'socialOpacity' => (int)$design['socialOpacity'],
            'profileBgType' => $design['profileBgType'],
            'socialBgType' => $design['socialBgType'],
            'bg' => $design['bg'] ?? null,
            'blockImage' => $design['blockImage'] ?? null,
            'socialBgImage' => $design['socialBgImage'] ?? null,
            'companyLogo' => $design['avatar'] ?? null,
            'companyDisplayName' => $displayName,
            'companyShowLogo' => isset($design['show_logo']) ? (int)$design['show_logo'] : 1,
            'companyShowName' => isset($design['show_name']) ? (int)$design['show_name'] : 1,
        ];
    }
}






















