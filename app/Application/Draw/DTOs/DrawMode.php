<?php

namespace App\Application\Draw\DTOs;

use App\Application\Draw\Enums\DrawModeCategory;

/**
 * Petit Read-Model immuable dédié à la présentation sur la Home.
 */
final readonly class DrawMode
{
    public function __construct(
        public string $icon,
        public string $title,
        public string $description,
        public ?string $route,
        public bool $available,
        public string $color,
        public string $shadow,
        public DrawModeCategory $category,
        public ?int $minParticipants = null,

        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
    ) {}
}
