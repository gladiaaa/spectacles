<?php
declare(strict_types=1);

namespace App\Controller;

use App\Kernel\IsGranted;
use App\Kernel\Response;
use App\Repository\ShowRepository;

final class ShowController
{
    #[IsGranted('PUBLIC')]
    public function list(): void
    {
        $repo = new ShowRepository();
        Response::json(['ok' => true, 'shows' => $repo->all()]);
    }

    #[IsGranted('PUBLIC')]
    public function detail(string $id): void
    {
        $repo = new ShowRepository();
        $show = $repo->find((int) $id);
        if (!$show) {
            Response::error('Spectacle introuvable', 404);
            return;
        }
        Response::json(['ok' => true, 'show' => $show]);
    }
}
