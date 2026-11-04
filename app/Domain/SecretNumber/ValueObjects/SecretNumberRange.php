<?php

namespace App\Domain\SecretNumber\ValueObjects;

use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;

final readonly class SecretNumberRange
{
    public const LOWER_LIMIT = 0;

    public const UPPER_LIMIT = 1_000_000;

    public function __construct(public int $minimum = 0, public int $maximum = 1000)
    {
        if ($minimum < self::LOWER_LIMIT || $maximum > self::UPPER_LIMIT || $minimum >= $maximum) {
            throw new InvalidSecretNumberGameException('La plage doit être comprise entre 0 et 1 000 000 et sa borne minimale doit être inférieure à sa borne maximale.');
        }
    }

    /** @return array<string, array{label: string, minimum: int, maximum: int}> */
    public static function presets(): array
    {
        return [
            '0-100' => ['label' => '0 à 100', 'minimum' => 0, 'maximum' => 100],
            '0-1000' => ['label' => '0 à 1 000', 'minimum' => 0, 'maximum' => 1000],
            '0-10000' => ['label' => '0 à 10 000', 'minimum' => 0, 'maximum' => 10000],
        ];
    }

    public static function fromPreset(string $preset): ?self
    {
        $definition = self::presets()[$preset] ?? null;

        return $definition === null ? null : new self($definition['minimum'], $definition['maximum']);
    }

    public function contains(int $number): bool
    {
        return $number >= $this->minimum && $number <= $this->maximum;
    }
}
