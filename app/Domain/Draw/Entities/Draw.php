<?php

namespace App\Domain\Draw\Entities;

use App\Domain\Draw\Collections\Participants;
use App\Domain\Draw\Contracts\DrawStrategy;
use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\DrawResult;

/**
 * Entité du Domaine (Aggregate Root).
 * Responsable de la cohérence globale d'un tirage et du respect de ses invariants.
 */
final class Draw
{
    /**
     * Valide l'état de l'entité dès sa création (concept de "Always-Valid Entity").
     */
    public function __construct(
        private readonly Participants $participants,
    ) {
        $this->validate();
    }

    /**
     * Exécute le tirage en déléguant l'algorithme à la stratégie injectée (Double Dispatch).
     */
    public function execute(
        DrawStrategy $strategy,
    ): DrawResult {
        return $strategy->draw(
            $this->participants
        );
    }

    /**
     * Règle métier (Invariant) : Empêche la création d'un tirage incohérent (moins de 2 joueurs).
     */
    private function validate(): void
    {
        if ($this->participants->count() < 2) {
            throw new InvalidDrawException(
                'A draw requires at least two participants.'
            );
        }
    }

    /**
     * Accesseur (Getter) vers la collection immuable de participants.
     */
    public function participants(): Participants
    {
        return $this->participants;
    }
}
