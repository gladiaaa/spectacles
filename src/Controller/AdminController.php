<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\IsGranted;
use App\Kernel\Response;
use App\Repository\ShowRepository;

final class AdminController
{
    #[IsGranted('ADMIN')]
    public function addShow(): void
    {
        $raw = file_get_contents('php://input') ?: '{}';
        $data = json_decode($raw, true) ?: [];
        $title = trim($data['title'] ?? '');
        $city  = trim($data['city'] ?? '');
        $price = (int)($data['price'] ?? 0);

        if ($title === '' || $city === '' || $price <= 0) {
            Response::error('title, city, price requis', 422);
            return;
        }

        $repo = new ShowRepository();
        $show = $repo->add(['title' => $title, 'city' => $city, 'price' => $price]);

        Response::json(['ok' => true, 'created' => $show], 201);
    }
}
