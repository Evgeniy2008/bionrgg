<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\ConnectionFactory;
use App\Exceptions\HttpException;
use App\Repositories\UserInfoRepository;
use App\Repositories\UserRepository;
use App\Validation\Validator;
use PDO;
use Throwable;

final class RegistrationService
{
    private readonly PDO $pdo;
    private readonly UserRepository $users;
    private readonly UserInfoRepository $userInfo;
    private readonly CompanyService $companies;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConnectionFactory::make();
        $this->users = new UserRepository($this->pdo);
        $this->userInfo = new UserInfoRepository($this->pdo);
        $this->companies = new CompanyService($this->pdo);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function register(array $payload): array
    {
        $validated = Validator::validate($payload, [
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:^[A-Za-z0-9_]+$'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'profile_type' => ['string', 'in:personal,company'],
            'company_name' => ['string', 'max:255'],
            'company_key' => ['string', 'max:12'],
            'description' => ['string', 'max:2000'],
            'profile_color' => ['string'],
            'text_color' => ['string'],
            'text_bg_color' => ['string'],
        ]);

        $username = trim($validated['username']);
        $profileType = $validated['profile_type'] ?? 'personal';

        if ($this->users->usernameExists($username)) {
            throw HttpException::conflict('Username already exists');
        }

        $passwordHash = password_hash($validated['password'], PASSWORD_DEFAULT);
        if ($passwordHash === false) {
            throw HttpException::badRequest('Failed to hash password');
        }

        $profileDefaults = $this->extractProfileDefaults($validated);

        $this->pdo->beginTransaction();

        try {
            $userId = $this->users->create($username, $passwordHash, $profileType);

            $this->userInfo->create([
                'user_id' => $userId,
                'username' => $username,
                'descr' => $validated['description'] ?? '',
                'profile_type' => $profileType,
                'company_id' => null,
                'color' => $profileDefaults['color'],
                'colorText' => $profileDefaults['colorText'],
                'textBgColor' => $profileDefaults['textBgColor'],
                'profileOpacity' => $profileDefaults['profileOpacity'],
                'textOpacity' => $profileDefaults['textOpacity'],
                'textBgOpacity' => $profileDefaults['textBgOpacity'],
                'socialBgColor' => $profileDefaults['socialBgColor'],
                'socialTextColor' => $profileDefaults['socialTextColor'],
                'socialOpacity' => $profileDefaults['socialOpacity'],
                'profileBgType' => $profileDefaults['profileBgType'],
                'socialBgType' => $profileDefaults['socialBgType'],
            ]);

            $media = $payload['media'] ?? [];
            if (is_array($media)) {
                $this->userInfo->updateMedia($username, $this->filterMedia($media));
            }

            $companyResponse = null;

            if ($profileType === 'company') {
                $companyName = $validated['company_name'] ?? null;
                if (!$companyName) {
                    throw HttpException::badRequest('Company name is required for company profiles');
                }

                $companyResponse = $this->companies->createCompany(
                    ['id' => $userId, 'username' => $username],
                    $companyName,
                    [
                        'profileColor' => $profileDefaults['color'],
                        'textColor' => $profileDefaults['colorText'],
                    ]
                );
            } elseif (!empty($validated['company_key'])) {
                $companyResponse = $this->companies->joinCompany($username, $validated['company_key']);
            }

            $this->pdo->commit();

            return [
                'user' => [
                    'id' => $userId,
                    'username' => $username,
                    'profile_type' => $profileType,
                ],
                'company' => $companyResponse,
            ];
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }
    }

    /**
     * @param array<string, mixed> $media
     * @return array<string, mixed>
     */
    private function filterMedia(array $media): array
    {
        $allowed = ['avatar', 'bg', 'blockImage'];
        $filtered = [];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $media)) {
                continue;
            }

            $value = $media[$field];
            if ($value === null || $value === '') {
                $filtered[$field] = null;
            } elseif (is_string($value)) {
                $filtered[$field] = $value;
            }
        }

        return $filtered;
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function extractProfileDefaults(array $validated): array
    {
        return [
            'color' => $this->sanitizeColor($validated['profile_color'] ?? '#c27eef', '#C27EEF'),
            'colorText' => $this->sanitizeColor($validated['text_color'] ?? '#ffffff', '#FFFFFF'),
            'textBgColor' => $this->sanitizeColor($validated['text_bg_color'] ?? '', ''),
            'profileOpacity' => 100,
            'textOpacity' => 100,
            'textBgOpacity' => 100,
            'socialBgColor' => '#000000',
            'socialTextColor' => '#ffffff',
            'socialOpacity' => 90,
            'profileBgType' => 'color',
            'socialBgType' => 'color',
        ];
    }

    private function sanitizeColor(string $color, string $fallback): string
    {
        $color = trim($color);
        if ($color === '') {
            return $fallback;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $fallback;
        }
        return strtoupper($color);
    }
}

