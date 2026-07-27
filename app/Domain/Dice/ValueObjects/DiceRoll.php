<?php

namespace App\Domain\Dice\ValueObjects;

/**
 * Représente les valeurs d'un lancer de dés, quel que soit le nombre de dés
 * (3 pour le 421, potentiellement 5 pour un futur jeu type Yahtzee...).
 * Ne porte aucune notion de combinaison : c'est DiceCombinationEvaluator qui
 * s'en charge, séparément.
 */
final readonly class DiceRoll
{
    /**
     * @param array<int> $values Valeurs des dés, dans l'ordre
     */
    public function __construct(
        public array $values,
    ) {}

    /**
     * @return array<int>
     */
    public function sorted(): array
    {
        $sorted = $this->values;
        sort($sorted);

        return $sorted;
    }
}
