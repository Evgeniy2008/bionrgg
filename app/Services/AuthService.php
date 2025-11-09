<?php

namespace App\Services;

use App\DTO\User;
use App\Repositories\UserRepository;
use App\Repositories\ProfileRepository;
use App\Support\Hasher;
use RuntimeException;

class AuthService
{
    public function __construct(
        private UserRepository $users,
        private ProfileRepository $profiles,
        private SessionService $sessions,
        private Hasher $hasher
    ) {
    }

    public function register(string $email, string $password, string $fullName): User
    {
        if ($this->users->findByEmail($email)) {
            throw new RuntimeException('Email already registered.');
        }

        $hash = $this->hasher->hash($password);
        $user = $this->users->create($email, $hash, $fullName);
        $this->profiles->createDefault($user->id, $fullName, $email);
        $this->sessions->createSession($user->id);

        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !$this->hasher->check($password, $user->passwordHash)) {
            throw new RuntimeException('Invalid credentials.');
        }

        $this->sessions->createSession($user->id);

        return $user;
    }

    public function logout(int $userId, string $token): void
    {
        $this->sessions->invalidateSession($userId, $token);
    }

    public function logoutByToken(string $token): void
    {
        $this->sessions->invalidateToken($token);
    }
}


