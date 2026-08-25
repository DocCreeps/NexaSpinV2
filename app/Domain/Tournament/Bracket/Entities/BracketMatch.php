<?php

namespace App\Domain\Tournament\Bracket\Entities;

use App\Domain\Tournament\Bracket\Exceptions\InvalidMatchResultException;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Entité représentant un match unique du bracket (un nœud de l'arbre).
 * Un bye (exemption) n'est possible qu'au round 1 : au-delà, un match reçoit
 * toujours ses deux participants via propagation (voir Bracket::propagateResolvedMatches()).
 */
final class BracketMatch
{
    private ?Participant $winner = null;
    private readonly bool $bye;

    /**
     * Utilisé par les brackets à structure dynamique (ex. double élimination) pour
     * les matchs dont on sait, avant même que les deux slots soient remplis, qu'ils
     * ne recevront jamais qu'un seul participant réel (l'autre branche amont étant
     * elle-même un bye ou un match fantôme). Voir markAsPotentialBye().
     */
    private bool $potentialBye = false;

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
        if ($this->bye) {
            return true;
        }

        // Bye "différé" : marqué potentiel, résolu dès qu'un seul des deux slots
        // a fini par se remplir (l'autre étant structurellement condamné à rester vide).
        return $this->potentialBye && $this->winner !== null;
    }

    /**
     * Marque ce match comme ne pouvant structurellement recevoir qu'un seul
     * participant réel à terme (l'autre branche amont du bracket étant elle-même
     * un bye ou un match fantôme sans issue). Dès que ce sera le cas, le
     * participant présent est automatiquement déclaré vainqueur — exactement
     * comme un bye classique — pour éviter tout match mort en attente éternelle.
     * Sans effet si les deux slots finissent par se remplir normalement.
     */
    public function markAsPotentialBye(): void
    {
        $this->potentialBye = true;
        $this->tryResolvePotentialBye();
    }

    private function tryResolvePotentialBye(): void
    {
        if ($this->potentialBye
            && $this->winner === null
            && ($this->participantA === null) !== ($this->participantB === null)) {
            $this->winner = $this->participantA ?? $this->participantB;
        }
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

        $this->tryResolvePotentialBye();
    }
}
