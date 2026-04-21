<?php

declare(strict_types=1);

final class Auth
{
    private static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443) {
            return true;
        }
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            return true;
        }
        return false;
    }

    private static function signPayload(string $payload): string
    {
        $key = defined('APP_KEY') ? APP_KEY : 'change-this-app-key';
        return hash_hmac('sha256', $payload, $key);
    }

    private static function setAuthCookies(array $user): void
    {
        $expires = time() + 7 * 86400;
        $secure = self::isHttps();
        $payload = implode('|', [(string)$user['id'], (string)$user['username'], (string)$user['role']]);
        $sig = self::signPayload($payload);

        setcookie('auth_uid', (string)$user['id'], [
            'expires' => $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        setcookie('auth_sig', $sig, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearAuthCookies(): void
    {
        $secure = self::isHttps();
        setcookie('auth_uid', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        setcookie('auth_sig', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function restoreUserFromCookies(): ?array
    {
        $uid = isset($_COOKIE['auth_uid']) ? (int)$_COOKIE['auth_uid'] : 0;
        $sig = (string)($_COOKIE['auth_sig'] ?? '');
        if ($uid <= 0 || $sig === '') {
            return null;
        }

        $dbUser = Database::one(
            'SELECT id, username, email, role FROM users WHERE id = ? AND is_active = 1',
            [$uid]
        );
        if (!$dbUser) {
            return null;
        }

        $payload = implode('|', [(string)$dbUser['id'], (string)$dbUser['username'], (string)$dbUser['role']]);
        $expected = self::signPayload($payload);
        if (!hash_equals($expected, $sig)) {
            return null;
        }

        $_SESSION['auth_user'] = $dbUser;
        return $dbUser;
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function user(): ?array
    {
        self::start();
        if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
            return $_SESSION['auth_user'];
        }
        return self::restoreUserFromCookies();
    }

    public static function login(string $username, string $password): ?array
    {
        $user = Database::one('SELECT id, username, email, role, password_hash FROM users WHERE username = ?', [$username]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        unset($user['password_hash']);
        self::start();
        session_regenerate_id(true);
        $_SESSION['auth_user'] = $user;
        self::setAuthCookies($user);
        return $user;
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['auth_user']);
        self::clearAuthCookies();
        if (session_id() !== '') {
            session_regenerate_id(true);
        }
    }

    public static function requireAuth(): array
    {
        $user = self::user();
        if (!$user) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if (str_starts_with($path, '/api/')) {
                jsonResponse(['error' => 'Unauthorized'], 401);
            }
            redirectTo('/admin/login');
        }
        return $user;
    }

    public static function requireSuperAdmin(): array
    {
        $user = self::requireAuth();
        if (($user['role'] ?? 'admin') !== 'super_admin') {
            jsonResponse(['error' => 'Super admin access required'], 403);
        }
        return $user;
    }
}
