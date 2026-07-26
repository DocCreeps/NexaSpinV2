<?php

namespace App\Application\Home\DTOs;

use App\Application\Home\Enums\GameModeCategory;

/**
 * Petit Read-Model immuable dédié à la présentation sur la Home.
 */
final readonly class GameMode
{
    public function __construct(
        public string $icon,
        public string $title,
        public string $description,
        public ?string $route,
        public bool $available,
        public string $color,
        public string $shadow,
        public GameModeCategory $category,
        public ?int $minParticipants = null,

        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
    ) {}
}
