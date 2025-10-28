<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\IsGranted;
use App\Kernel\Response;

final class ProfileController
{
    #[IsGranted('USER')]
    public function me(array $claims): void
    {
        $user = $claims['sub'];
        $tickets = $_SESSION['bookings'][$user] ?? [];
        Response::json(['ok'=>true, 'user'=>$user, 'tickets'=>$tickets]);
    }
}
