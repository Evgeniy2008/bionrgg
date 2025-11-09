<?php

namespace App\Services;

use mysqli;
use RuntimeException;

class SessionService
{
    public function __construct(
        private mysqli $db,
        private int $ttlSeconds = 604800 // 7 days
    ) {
    }

    public function createSession(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTimeImmutable("+{$this->ttlSeconds} seconds"))->format('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

        $stmt = $this->db->prepare('INSERT INTO sessions (user_id, session_token, user_agent, ip_address, expires_at) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare session insert.');
        }
        $stmt->bind_param('issss', $userId, $token, $userAgent, $ipAddress, $expiresAt);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException('Failed to persist session: ' . $stmt->error);
        }
        $stmt->close();

        setcookie('bion_session', $token, [
            'expires' => time() + $this->ttlSeconds,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        return $token;
    }

    public function invalidateSession(int $userId, string $token): void
    {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE user_id = ? AND session_token = ?');
        if ($stmt) {
            $stmt->bind_param('is', $userId, $token);
            $stmt->execute();
            $stmt->close();
        }

        setcookie('bion_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
    }

    public function invalidateToken(string $token): void
    {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE session_token = ?');
        if ($stmt) {
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $stmt->close();
        }

        setcookie('bion_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
        ]);
    }

    public function resolveUserId(string $token): ?int
    {
        $stmt = $this->db->prepare('SELECT user_id, expires_at FROM sessions WHERE session_token = ? LIMIT 1');
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result?->fetch_assoc();
        $stmt->close();

        if (!$row) {
            return null;
        }

        $expiresAt = strtotime($row['expires_at']);
        if ($expiresAt !== false && $expiresAt < time()) {
            $this->invalidateSession((int)$row['user_id'], $token);
            return null;
        }

        return (int)$row['user_id'];
    }
}


