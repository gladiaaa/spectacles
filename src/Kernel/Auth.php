<?php
declare(strict_types=1);

namespace App\Kernel;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

final class Auth
{
    public static function issueAccessToken(string $username, string $role): string {
        $now = time();
        $payload = [
            'sub' => $username,
            'role'=> $role,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + Config::ACCESS_TTL,
            'typ' => 'access',
        ];
        return JWT::encode($payload, Config::JWT_SECRET, Config::JWT_ALGO);
    }

    public static function issueRefreshToken(string $username): string {
        $now = time();
        $payload = [
            'sub' => $username,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + Config::REFRESH_TTL,
            'typ' => 'refresh',
        ];
        return JWT::encode($payload, Config::JWT_SECRET, Config::JWT_ALGO);
    }

    public static function decode(string $token): array {
        $decoded = JWT::decode($token, new Key(Config::JWT_SECRET, Config::JWT_ALGO));
        return json_decode(json_encode($decoded), true);
    }

    public static function userFromAccessCookie(): ?array {
        $jwt = $_COOKIE[Config::ACCESS_COOKIE] ?? null;
        if (!$jwt) return null;
        try {
            $claims = self::decode($jwt);
            if (($claims['typ'] ?? '') !== 'access') return null;
            return $claims; // ['sub' => username, 'role' => ...]
        } catch (ExpiredException $e) { return null; }
          catch (\Throwable $e)       { return null; }
    }

    public static function setCookie(string $name, string $value, int $ttl): void {
        setcookie($name, $value, [
            'expires'  => time() + $ttl,
            'path'     => Config::COOKIE_PATH,
            'secure'   => Config::COOKIE_SECURE,
            'httponly' => Config::COOKIE_HTTPONLY,
            'samesite' => Config::COOKIE_SAMESITE,
        ]);
    }

    public static function clearCookie(string $name): void {
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => Config::COOKIE_PATH,
            'secure'   => Config::COOKIE_SECURE,
            'httponly' => Config::COOKIE_HTTPONLY,
            'samesite' => Config::COOKIE_SAMESITE,
        ]);
    }

    public static function ensureGranted(string $requiredRole): array {
        // PUBLIC : pas de contrôle
        if ($requiredRole === 'PUBLIC') {
            return self::userFromAccessCookie() ?? ['sub'=>null,'role'=>'PUBLIC'];
        }

        $claims = self::userFromAccessCookie();
        if (!$claims) {
            Response::error('Non identifié ou token expiré', 401);
            exit;
        }

        $role = $claims['role'] ?? 'USER';
        if ($requiredRole === 'USER') {
            return $claims;
        }
        if ($requiredRole === 'ADMIN' && $role === 'ADMIN') {
            return $claims;
        }
        Response::error('Accès refusé (rôle insuffisant)', 403);
        exit;
    }
}
