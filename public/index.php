<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Kernel\Router;
use App\Kernel\Config;

session_start(); 

$router = new Router();

// Routes publiques
$router->get('/',            [\App\Controller\HomeController::class, 'index']);
$router->get('/shows',       [\App\Controller\ShowController::class, 'list']);
$router->get('/shows/{id}',  [\App\Controller\ShowController::class, 'detail']);

// Auth
$router->post('/login',      [\App\Controller\AuthController::class, 'login']);
$router->post('/refresh',    [\App\Controller\AuthController::class, 'refresh']);
$router->post('/logout',     [\App\Controller\AuthController::class, 'logout']);

// Utilisateurs identifiés
$router->post('/reserve/{id}', [\App\Controller\BookingController::class, 'reserve']);
$router->get('/profile',       [\App\Controller\ProfileController::class, 'me']);

// Admin
$router->post('/admin/shows',  [\App\Controller\AdminController::class, 'addShow']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
