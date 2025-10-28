<?php
declare(strict_types=1);

namespace App\Kernel;

final class Config
{
    // JWT
    public const JWT_SECRET      = 'change-me-very-strong-secret';
    public const JWT_ALGO        = 'HS256';
    public const ACCESS_TTL      = 10;        // 10s : court pour la démo
    public const REFRESH_TTL     = 1800;      

    // Cookies
    public const ACCESS_COOKIE   = 'access_token';
    public const REFRESH_COOKIE  = 'refresh_token';
    public const COOKIE_PATH     = '/';
    public const COOKIE_SECURE   = false;     // true pour la prod HTTPS /!\ réflechir si a mettre en https
    public const COOKIE_HTTPONLY = true;
    public const COOKIE_SAMESITE = 'Lax';

    public const USERS = [
        'ryan'   => ['secret123', 'USER'],
        'test' => ['passw0rd',  'USER'],
        'admin'  => ['admin123',  'ADMIN'],
    ];
}
