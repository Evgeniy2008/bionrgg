<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;
use App\Services\ProfileService;
use App\Support\Validator;
use InvalidArgumentException;
use RuntimeException;

class AuthController
{
    public function __construct(
        private AuthService $auth,
        private ProfileService $profiles,
        private Validator $validator
    ) {
    }

    public function register(Request $request, Response $response): void
    {
        try {
            $payload = $request->json();
            $this->validator->validate($payload, [
                'email' => Validator::email(),
                'password' => Validator::password(),
                'full_name' => Validator::requiredString(1, 255),
            ]);

            $user = $this->auth->register(
                trim($payload['email']),
                $payload['password'],
                trim($payload['full_name'])
            );

            $profile = $this->profiles->getOwnProfile($user->id);

            $response->status(201)->json([
                'success' => true,
                'user' => $user->toPublicArray(),
                'profile' => $profile->toArray(true),
            ]);
        } catch (InvalidArgumentException $e) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => json_decode($e->getMessage(), true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            $response->status(500)->json([
                'success' => false,
                'message' => 'Registration failed.',
            ]);
        }
    }

    public function login(Request $request, Response $response): void
    {
        try {
            $payload = $request->json();
            $this->validator->validate($payload, [
                'email' => Validator::email(),
                'password' => Validator::requiredString(1),
            ]);

            $user = $this->auth->login(
                trim($payload['email']),
                $payload['password']
            );

            $profile = $this->profiles->getOwnProfile($user->id);

            $response->json([
                'success' => true,
                'user' => $user->toPublicArray(),
                'profile' => $profile->toArray(true),
            ]);
        } catch (InvalidArgumentException $e) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => json_decode($e->getMessage(), true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(401)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            $response->status(500)->json([
                'success' => false,
                'message' => 'Login failed.',
            ]);
        }
    }

    public function logout(Request $request, Response $response): void
    {
        $token = $request->cookie('bion_session');
        if ($token) {
            $this->auth->logoutByToken($token);
        }
        $response->status(204)->send();
    }
}


