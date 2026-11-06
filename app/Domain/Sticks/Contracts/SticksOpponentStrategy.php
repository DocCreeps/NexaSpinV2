<?php

namespace App\Domain\Sticks\Contracts;

use App\Domain\Sticks\Entities\SticksGame;

/**
 * Contrat (Pattern Strategy) d'un adversaire pour le jeu des bâtonnets.
 */
interface SticksOpponentStrategy
{
    /**
     * Retourne le nombre de bâtonnets (1 à 3) que l'adversaire choisit de retirer.
     * Le choix des bâtonnets précis reste à la charge de l'Application.
     */
    public function choose(SticksGame $game): int;
}
