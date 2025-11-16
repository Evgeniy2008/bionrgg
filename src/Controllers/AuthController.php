<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Services\AuthService;
use App\Services\RegistrationService;

final class AuthController
{
    public function __construct(
        private readonly RegistrationService $registrationService = new RegistrationService(),
        private readonly AuthService $authService = new AuthService()
    ) {
    }

    public function register(Request $request): JsonResponse
    {
        $result = $this->registrationService->register($request->body());

        return JsonResponse::success([
            'message' => 'Registration successful',
            'user' => $result['user'],
            'company' => $result['company'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $request->body();
        $username = (string)($payload['username'] ?? '');
        $password = (string)($payload['password'] ?? '');

        if ($username === '' || $password === '') {
            return JsonResponse::error('Username and password are required', 400);
        }

        $user = $this->authService->authenticate($username, $password);

        return JsonResponse::success([
            'message' => 'Authenticated successfully',
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'profile_type' => $user['profile_type'],
            ],
        ]);
    }
}
















