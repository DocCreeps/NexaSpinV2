<?php

namespace App\Application\CoinFlip\Actions;

use App\Domain\CoinFlip\Contracts\CoinFlipStrategy;
use App\Domain\CoinFlip\ValueObjects\CoinFlipResult;

/**
 * Action (Use Case) orchestrant le tirage "Pile ou Face".
 * Classe fermée à l'extension (final) possédant une unique responsabilité.
 *
 * Contrairement à RunDrawAction, cette Action ne dépend d'aucun DTO ni
 * d'aucune collection de participants : un tirage "Pile ou Face" n'a ni
 * liste à valider, ni minimum de joueurs. Elle reste donc entièrement
 * propre au Domaine CoinFlip.
 */
final class FlipCoinAction
{
    /**
     * Injection de dépendance via la promotion de propriété (PHP 8+).
     * Dépend du contrat (Interface) et non d'une implémentation concrète,
     * afin de permettre l'introduction future d'autres stratégies
     * (ex : pièce truquée) sans modifier cette Action.
     */
    public function __construct(
        private readonly CoinFlipStrategy $strategy
    ) {}

    /**
     * Point d'entrée de l'action.
     * Délègue l'exécution du tirage à la stratégie injectée.
     */
    public function execute(): CoinFlipResult
    {
        return $this->strategy->flip();
    }
}
