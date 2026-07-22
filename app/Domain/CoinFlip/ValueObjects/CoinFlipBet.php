<?php

namespace App\Domain\CoinFlip\ValueObjects;

use App\Domain\CoinFlip\Enums\CoinSide;

/**
 * Représente le résultat immuable d'un pari placé sur un tirage "Pile ou Face".
 *
 * Un pari associe la face choisie par le joueur au résultat effectif du
 * tirage. La règle métier (gagné/perdu) est portée ici, dans le Domaine,
 * plutôt que dans le composant Livewire, afin de rester cohérent avec le
 * reste du contexte CoinFlip (cf. CoinFlipResult, RandomCoinFlipStrategy).
 */
final readonly class CoinFlipBet
{
    public function __construct(
        public CoinSide $chosen,
        public CoinFlipResult $result,
    ) {}

    /**
     * Indique si la face choisie correspond à la face tirée.
     */
    public function won(): bool
    {
        return $this->chosen === $this->result->side;
    }
}
