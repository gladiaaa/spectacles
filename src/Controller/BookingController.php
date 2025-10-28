<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\IsGranted;
use App\Kernel\Response;
use App\Repository\ShowRepository;

final class BookingController
{
    #[IsGranted('USER')]
    public function reserve(string $id, array $claims): void
    {
        $repo = new ShowRepository();
        $show = $repo->find((int) $id);
        if (!$show) {
            Response::error('Spectacle introuvable', 404);
            return;
        }

        $user = $claims['sub'];
        $_SESSION['bookings'][$user] = $_SESSION['bookings'][$user] ?? [];
        $_SESSION['bookings'][$user][] = [
            'show_id' => $show['id'],
            'title'   => $show['title'],
            'city'    => $show['city'],
            'price'   => $show['price'],
            'when'    => date('c'),
        ];

        Response::json(['ok' => true, 'message' => 'Réservation confirmée', 'ticket' => end($_SESSION['bookings'][$user])], 201);
    }
}
