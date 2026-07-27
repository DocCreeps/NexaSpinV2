<?php

namespace App\Domain\Dice\Contracts;

use App\Domain\Dice\ValueObjects\DiceRoll;

/**
 * Contrat pour les règles d'un jeu de dés (Strategy Pattern).
 *
 * Volontairement neutre : aucune méthode ne fait référence au 421, à un
 * nombre de dés fixe ou à une combinaison précise. Chaque implémentation
 * concrète (FourTwoOneStrategy, et demain d'autres jeux de dés : Zanzibar,
 * un brelan simple, un Yahtzee-like...) définit elle-même :
 * - combien de dés elle utilise (diceCount),
 * - combien de lancers elle autorise (maxThrows),
 * - ce qui constitue une victoire (isWinningRoll).
 *
 * RollDiceAction ne dépend que de ce contrat : ajouter un nouveau jeu de dés
 * ne nécessite ni modification de l'Action, ni du contrat lui-même.
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
