<?php

namespace App\Domain\Bracket\Entities;

use App\Domain\Bracket\Exceptions\InvalidMatchResultException;
use App\Domain\Bracket\ValueObjects\Participant;

/**
 * Entité représentant un match unique du bracket (un nœud de l'arbre).
 * Un bye (exemption) n'est possible qu'au round 1 : au-delà, un match reçoit
 * toujours ses deux participants via propagation (voir Bracket::propagateResolvedMatches()).
 */
final class BracketMatch
{
    private ?Participant $winner = null;
    private readonly bool $bye;

    public function __construct(
        public readonly int $round,
        public readonly int $position,
        private ?Participant $participantA = null,
        private ?Participant $participantB = null,
    ) {
        $this->bye = $this->round === 1
            && ($this->participantA === null) !== ($this->participantB === null);

        if ($this->bye) {
            $this->winner = $this->participantA ?? $this->participantB;
        }
    }

    public function participantA(): ?Participant
    {
        return $this->participantA;
    }

    public function participantB(): ?Participant
    {
        return $this->participantB;
    }

    public function winner(): ?Participant
    {
        return $this->winner;
    }

    public function isBye(): bool
    {
        return $this->bye;
    }

    public function isPlayable(): bool
    {
        return ! $this->bye && $this->participantA !== null && $this->participantB !== null;
    }

    public function isResolved(): bool
    {
        return $this->winner !== null;
    }

    /**
     * Enregistre le vainqueur saisi manuellement. Doit être l'un des deux engagés.
     *
     * @throws InvalidMatchResultException Si le match n'est pas jouable ou si le
     *                                     vainqueur ne fait pas partie du match.
     */
    public function recordResult(Participant $winner): void
    {
        if (! $this->isPlayable()) {
            throw new InvalidMatchResultException(
                'Ce match ne peut pas être résolu manuellement (bye ou emplacement(s) encore vide(s)).'
            );
        }

        if (! $winner->equals($this->participantA) && ! $winner->equals($this->participantB)) {
            throw new InvalidMatchResultException('Le vainqueur doit être l’un des deux participants du match.');
        }

        $this->winner = $winner;
    }

    /**
     * Place un participant qualifié venant du tour précédent (0 = slot A, 1 = slot B).
     * Réservé à Bracket pour la propagation automatique, jamais appelé depuis l'UI.
     */
    public function fillSlot(int $slotIndex, Participant $participant): void
    {
        if ($slotIndex === 0) {
            $this->participantA = $participant;
        } else {
            $this->participantB = $participant;
        }
    }
}
