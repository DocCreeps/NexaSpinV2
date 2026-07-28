<?php

namespace App\Domain\Dice\Contracts;

use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Contrat pour les règles d'un jeu de dés (Strategy Pattern).
 */
interface DiceGameStrategy
{
    /**
     * Nombre de dés utilisés par ce jeu.
     */
    public function diceCount(): int;

    /**
     * Nombre maximal de lancers autorisés avant la fin de la partie.
     */
    public function maxThrows(): int;

    /**
     * Détermine si le lancer courant satisfait l'objectif de ce jeu.
     */
    public function isWinningRoll(DiceRoll $roll): bool;
}
