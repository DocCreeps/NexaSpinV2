<?php

namespace App\Domain\Dice\ValueObjects;

/**
 * Représente l'état des dés issus d'un lancer.
 */
final readonly class DiceRoll
{
    /**
     * @param array<int> $values
     */
    public function __construct(
        public array $values,
    ) {}

    /**
     * Retourne les valeurs des dés triées par ordre croissant.
     *
     * @return array<int>
     */
    public function sorted(): array
    {
        $sorted = $this->values;
        sort($sorted);

        return $sorted;
    }
}
