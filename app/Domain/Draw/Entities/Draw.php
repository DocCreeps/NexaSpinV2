<?php

namespace App\Domain\Draw\Entities;

use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\DrawResult;
use App\Domain\Draw\Collections\Participants;

/**
 * Représente un tirage métier.
 *
 * Cet objet garantit qu'un tirage est valide
 * et délègue la sélection du gagnant à une stratégie.
 */
final class Draw
{
    public function __construct(
        private readonly Participants $participants,
    ) {
        $this->validate();
    }

    public function execute(
        DrawStrategy $strategy,
    ): DrawResult {

        return $strategy->draw(
            $this->participants
        );
    }

    private function validate(): void
    {
        if ($this->participants->count() < 2) {
            throw new InvalidDrawException(
                'A draw requires at least two participants.'
            );
        }
    }

    public function participants(): Participants
    {
        return $this->participants;
    }
}
