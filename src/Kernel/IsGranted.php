<?php
declare(strict_types=1);

namespace App\Kernel;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class IsGranted
{
    /**
     * @param string $role 'PUBLIC' | 'USER' | 'ADMIN'
     */
    public function __construct(public string $role = 'USER') {}
}
