<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('PropertyCard')]
final class PropertyCard
{
    public function __construct(
        public string $title = '',
        public int $price = 0,
        public string $status = 'active',
        public ?string $imageUrl = null,
        public ?int $id = null,
    ) {
    }
}
