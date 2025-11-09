<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database\ConnectionFactory;
use App\Exceptions\HttpException;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Services\AuthService;
use App\Services\CompanyService;
use PDO;
use Throwable;

final class CompanyController
{
    private readonly PDO $pdo;
    private readonly AuthService $auth;
    private readonly CompanyService $companies;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConnectionFactory::make();
        $this->auth = new AuthService($this->pdo);
        $this->companies = new CompanyService($this->pdo);
    }

    public function create(Request $request): JsonResponse
    {
        [$user, $body] = $this->authenticate($request);
        $companyName = trim((string)($body['company_name'] ?? ''));

        if ($companyName === '') {
            throw HttpException::badRequest('Company name is required');
        }

        $this->pdo->beginTransaction();
        try {
            $response = $this->companies->createCompany($user, $companyName, []);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Company created successfully',
            'company' => $response,
        ], 201);
    }

    public function join(Request $request): JsonResponse
    {
        [$user, $body] = $this->authenticate($request);
        $companyKey = trim((string)($body['company_key'] ?? ''));
        if ($companyKey === '') {
            throw HttpException::badRequest('Company key is required');
        }

        $this->pdo->beginTransaction();
        try {
            $response = $this->companies->joinCompany($user['username'], $companyKey);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Joined company successfully',
            'company' => $response,
        ]);
    }

    public function show(Request $request, array $params): JsonResponse
    {
        $companyId = (int)($params['id'] ?? 0);
        if ($companyId <= 0) {
            throw HttpException::badRequest('Invalid company id');
        }

        $username = (string)$request->query('username', '');
        $password = (string)$request->query('password', '');
        if ($username === '' || $password === '') {
            throw HttpException::unauthorized('Username and password are required');
        }

        $user = $this->auth->authenticate($username, $password);
        $company = $this->companies->getCompanyForUser($companyId, $user['username']);

        return JsonResponse::success([
            'company' => $company,
        ]);
    }

    public function updateSettings(Request $request, array $params): JsonResponse
    {
        [$user, $body] = $this->authenticate($request);
        $companyId = (int)($params['id'] ?? 0);
        if ($companyId <= 0) {
            throw HttpException::badRequest('Invalid company id');
        }

        $settings = [];
        if (array_key_exists('unified_design_enabled', $body)) {
            $settings['unified_design_enabled'] = (int)$body['unified_design_enabled'] ? 1 : 0;
        }
        if (array_key_exists('company_name', $body)) {
            $name = trim((string)$body['company_name']);
            if ($name === '') {
                throw HttpException::badRequest('Company name cannot be empty');
            }
            $settings['company_name'] = $name;
        }

        if ($settings === []) {
            throw HttpException::badRequest('No settings provided');
        }

        $this->pdo->beginTransaction();
        try {
            $this->companies->updateSettings($companyId, $user['username'], $settings);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Company settings updated successfully',
        ]);
    }

    public function updateDesign(Request $request, array $params): JsonResponse
    {
        [$user, $body] = $this->authenticate($request);
        $companyId = (int)($params['id'] ?? 0);
        if ($companyId <= 0) {
            throw HttpException::badRequest('Invalid company id');
        }

        $design = $this->filterDesignPayload($body);
        if ($design === []) {
            throw HttpException::badRequest('No design fields provided');
        }

        $this->pdo->beginTransaction();
        try {
            $this->companies->updateDesign($companyId, $user['username'], $design);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Company design updated successfully',
        ]);
    }

    public function destroy(Request $request, array $params): JsonResponse
    {
        [$user] = $this->authenticate($request);
        $companyId = (int)($params['id'] ?? 0);
        if ($companyId <= 0) {
            throw HttpException::badRequest('Invalid company id');
        }

        $this->pdo->beginTransaction();
        try {
            $this->companies->deleteCompany($companyId, $user['username']);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Company deleted successfully',
        ]);
    }

    public function removeMember(Request $request, array $params): JsonResponse
    {
        [$user] = $this->authenticate($request);
        $companyId = (int)($params['id'] ?? 0);
        $memberUsername = (string)($params['member'] ?? '');

        if ($companyId <= 0 || $memberUsername === '') {
            throw HttpException::badRequest('Invalid company or member');
        }

        $this->pdo->beginTransaction();
        try {
            $this->companies->removeMember($companyId, $user['username'], $memberUsername);
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }

        return JsonResponse::success([
            'message' => 'Member removed successfully',
        ]);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function authenticate(Request $request): array
    {
        $payload = $request->body();
        $username = isset($payload['username']) ? (string)$payload['username'] : '';
        $password = isset($payload['password']) ? (string)$payload['password'] : '';

        if ($username === '' || $password === '') {
            throw HttpException::unauthorized('Username and password are required');
        }

        $user = $this->auth->authenticate($username, $password);
        return [$user, $payload];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function filterDesignPayload(array $body): array
    {
        $filtered = [];

        if (array_key_exists('profileColor', $body)) {
            $filtered['profileColor'] = $this->sanitizeColor($body['profileColor'], '#2572ad');
        }
        if (array_key_exists('textColor', $body)) {
            $filtered['textColor'] = $this->sanitizeColor($body['textColor'], '#ffffff');
        }
        if (array_key_exists('textBgColor', $body)) {
            $filtered['textBgColor'] = $this->sanitizeColor($body['textBgColor'], '', true);
        }
        if (array_key_exists('socialBgColor', $body)) {
            $filtered['socialBgColor'] = $this->sanitizeColor($body['socialBgColor'], '#000000');
        }
        if (array_key_exists('socialTextColor', $body)) {
            $filtered['socialTextColor'] = $this->sanitizeColor($body['socialTextColor'], '#ffffff');
        }

        foreach (['profileOpacity', 'textOpacity', 'textBgOpacity', 'socialOpacity'] as $opacityField) {
            if (array_key_exists($opacityField, $body)) {
                $value = (int)$body[$opacityField];
                $filtered[$opacityField] = max(0, min(100, $value));
            }
        }

        foreach (['profileBgType', 'socialBgType'] as $typeField) {
            if (array_key_exists($typeField, $body)) {
                $filtered[$typeField] = $this->sanitizeBgType($body[$typeField]);
            }
        }

        foreach (['bg', 'blockImage', 'socialBgImage', 'avatar'] as $mediaField) {
            if (array_key_exists($mediaField, $body)) {
                $value = $body[$mediaField];
                if ($value === '' || $value === null) {
                    $filtered[$mediaField] = null;
                } elseif (is_string($value)) {
                    $filtered[$mediaField] = $value;
                }
            }
        }

        if (array_key_exists('display_name', $body)) {
            $filtered['display_name'] = $this->sanitizeText($body['display_name']);
        }
        if (array_key_exists('tagline', $body)) {
            $filtered['tagline'] = $this->sanitizeText($body['tagline']);
        }

        if (array_key_exists('show_logo', $body)) {
            $filtered['show_logo'] = (int)$body['show_logo'] ? 1 : 0;
        }
        if (array_key_exists('show_name', $body)) {
            $filtered['show_name'] = (int)$body['show_name'] ? 1 : 0;
        }

        return $filtered;
    }

    private function sanitizeColor(mixed $value, string $fallback, bool $allowEmpty = false): string
    {
        if (!is_string($value)) {
            return $allowEmpty ? '' : $fallback;
        }

        $value = trim($value);
        if ($allowEmpty && $value === '') {
            return '';
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            return $fallback;
        }

        return strtoupper($value);
    }

    private function sanitizeBgType(mixed $value): string
    {
        $type = is_string($value) ? strtolower(trim($value)) : 'color';
        return in_array($type, ['color', 'image'], true) ? $type : 'color';
    }

    private function sanitizeText(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $text = trim($value);
        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, 255);
    }
}

