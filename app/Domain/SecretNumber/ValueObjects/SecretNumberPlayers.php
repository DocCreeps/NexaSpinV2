<?php

namespace App\Domain\SecretNumber\ValueObjects;

use App\Domain\SecretNumber\Exceptions\InvalidSecretNumberGameException;

/** Joueurs d'une partie : effectif borné et noms normalisés (source unique de ces règles). */
final readonly class SecretNumberPlayers
{
    public const MIN_PLAYERS = 2;

    public const MAX_PLAYERS = 10;

    public const MAX_NAME_LENGTH = 24;

    /** @param array<int, string> $names Noms indexés à partir de 1. */
    private function __construct(public int $count, public array $names) {}

    /** @param array<int|string, mixed> $names */
    public static function fromInput(int $count, array $names = []): self
    {
        if ($count < self::MIN_PLAYERS || $count > self::MAX_PLAYERS) {
            throw new InvalidSecretNumberGameException(sprintf('Le nombre de joueurs doit être compris entre %d et %d.', self::MIN_PLAYERS, self::MAX_PLAYERS));
        }

        $normalized = [];
        for ($player = 1; $player <= $count; $player++) {
            $name = $names[$player] ?? null;
            $name = is_string($name) ? mb_substr(trim($name), 0, self::MAX_NAME_LENGTH) : '';
            $normalized[$player] = $name !== '' ? $name : self::defaultName($player);
        }

        return new self($count, $normalized);
    }

    public static function defaultName(int $player): string
    {
        return 'Joueur '.$player;
    }

    public function name(int $player): string
    {
        return $this->names[$player] ?? self::defaultName($player);
    }
}
