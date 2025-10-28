<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\Config;
use App\Kernel\Response;
use App\Kernel\Auth;

final class AuthController
{
    public function login(): void
    {
        $u = trim($_POST['username'] ?? '');
        $p = trim($_POST['password'] ?? '');

        if ($u === '' || $p === '') {
            Response::error('Champs manquants', 400);
            return;
        }


        if (!isset(Config::USERS[$u]) || Config::USERS[$u][0] !== $p) {
            Response::error('Identifiants invalides', 401);
            return;
        }

        $role    = Config::USERS[$u][1];
        $access  = Auth::issueAccessToken($u, $role);
        $refresh = Auth::issueRefreshToken($u);

        Auth::setCookie(Config::ACCESS_COOKIE,  $access,  Config::ACCESS_TTL);
        Auth::setCookie(Config::REFRESH_COOKIE, $refresh, Config::REFRESH_TTL);

        header('Location: /');
        exit;
    }

    public function refresh(): void
    {
        $refresh = $_COOKIE[Config::REFRESH_COOKIE] ?? null;
        if (!$refresh) {
            Response::error('Aucun refresh token', 401);
            return;
        }

        try {
            $claims = Auth::decode($refresh);
            if (($claims['typ'] ?? '') !== 'refresh') {
                Response::error('Refresh token invalide', 401);
                return;
            }

            $username = $claims['sub'] ?? null;
            if (!$username || !isset(Config::USERS[$username])) {
                Response::error('Utilisateur inconnu', 401);
                return;
            }

            $role   = Config::USERS[$username][1];
            $access = Auth::issueAccessToken($username, $role);
            Auth::setCookie(Config::ACCESS_COOKIE, $access, Config::ACCESS_TTL);

            Response::json(['ok' => true, 'message' => 'Nouveau access token émis']);
        } catch (\Throwable $e) {
            Response::error('Refresh expiré ou invalide', 401);
            return;
        }
    }

    public function logout(): void
    {
        Auth::clearCookie(Config::ACCESS_COOKIE);
        Auth::clearCookie(Config::REFRESH_COOKIE);
        header('Location: /');
        exit;
    }
}
