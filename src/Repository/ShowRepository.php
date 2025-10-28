<?php
declare(strict_types=1);

namespace App\Repository;

final class ShowRepository
{
    // simulation des spectacles en dur
    private array $shows = [
        ['id'=>1, 'title'=>'Molière – Le Tartuffe', 'city'=>'Paris',     'price'=>25],
        ['id'=>2, 'title'=>'Improvisation Night',    'city'=>'Lyon',      'price'=>18],
        ['id'=>3, 'title'=>'Stand-up “La Relève”',   'city'=>'Marseille', 'price'=>20],
    ];

    public function all(): array {
        return $this->shows;
    }

    public function find(int $id): ?array {
        foreach ($this->shows as $s) if ($s['id']===$id) return $s;
        return null;
    }

    public function add(array $show): array {
        $nextId = max(array_column($this->shows,'id')) + 1;
        $show['id'] = $nextId;
        $this->shows[] = $show;
        return $show;
    }
}
