<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\IsGranted;
use App\Kernel\Response;
use App\Kernel\Auth;

final class HomeController
{
    #[IsGranted('PUBLIC')]
    public function index(): void
    {
        $claims = Auth::userFromAccessCookie();
        $username = $claims['sub'] ?? null;

        $html = <<<HTML
<!doctype html><html lang="fr"><meta charset="utf-8">
<title>Accueil — Spectacles</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;max-width:760px;margin:32px auto;padding:0 16px}
nav a{margin-right:12px}
.card{border:1px solid #eee;border-radius:10px;padding:16px;box-shadow:0 8px 20px rgba(0,0,0,.06);margin:16px 0}
input,button{padding:10px;font-size:16px}
</style>
<body>
  <nav>
    <a href="/">Accueil</a>
    <a href="/shows">Spectacles</a>
    <a href="/profile">Mon profil</a>
  </nav>

  <h1>Bienvenue sur le site des spectacles</h1>
  <p>Explore et réserve des places en quelques clics.</p>
HTML;

        if ($username) {
            $html .= "<p>👋 Connecté en tant que <strong>".htmlspecialchars($username)."</strong></p>";
        } else {
            $html .= <<<HTML
<div class="card">
  <h2>Connexion</h2>
  <form method="post" action="/login">
    <label>Identifiant <input name="username" required></label><br>
    <label>Mot de passe <input name="password" type="password" required></label><br>
    <button type="submit">Se connecter</button>
  </form>
  <p>Comptes démo : <code>ryan/secret123</code>, <code>admin/admin123</code></p>
</div>
HTML;
        }

        $html .= <<<HTML
<div class="card">
  <h2>Tokens</h2>
  <form method="post" action="/refresh" style="display:inline"><button>Refresh access token</button></form>
  <form method="post" action="/logout"  style="display:inline;margin-left:8px"><button>Logout</button></form>
</div>
</body></html>
HTML;

        Response::html($html);
    }
}
