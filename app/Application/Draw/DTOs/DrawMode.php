<?php

namespace App\Application\Draw\DTOs;

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
        public ?int $minParticipants = null,
    ) {}
}
