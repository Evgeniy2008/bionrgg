<?php
declare(strict_types=1);

namespace App\Services;

use App\Database\ConnectionFactory;
use App\Exceptions\HttpException;
use App\Repositories\UserRepository;
use PDO;

final class AuthService
{
    private readonly PDO $pdo;
    private readonly UserRepository $users;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConnectionFactory::make();
        $this->users = new UserRepository($this->pdo);
    }

    public function authenticate(string $username, string $password): array
    {
        $user = $this->users->findByUsername($username);
        if (!$user) {
            throw HttpException::unauthorized('Invalid credentials');
        }

        $hash = (string)$user['password_hash'];
        if ($hash === '' || !password_verify($password, $hash)) {
            throw HttpException::unauthorized('Invalid credentials');
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $this->users->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
        }

        return $user;
    }
}






















