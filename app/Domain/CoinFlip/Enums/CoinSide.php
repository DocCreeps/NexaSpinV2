<?php

namespace App\Domain\CoinFlip\Enums;

/**
 * Représente les deux faces d'une pièce.
 */
enum CoinSide: string
{
    case PILE = 'pile';
    case FACE = 'face';

    /**
     * Retourne le libellé d'affichage de la face.
     */
    public function label(): string
    {
        return match ($this) {
            self::PILE => 'Pile',
            self::FACE => 'Face',
        };
    }
}
