<?php

namespace App\Domain\Tournament\Pool\Entities;

use App\Domain\Tournament\Pool\Exceptions\InvalidPoolMatchResultException;
use App\Domain\Tournament\ValueObjects\Participant;

/**
 * Entité représentant un match de round-robin au sein d'une poule.
 * Contrairement au BracketMatch, il n'y a jamais de bye ici : un match de poule
 * a toujours ses deux participants dès sa création (c'est précisément ce qui
 * garantit l'absence de match vide dans une phase de poules).
 */
final class PoolMatch
{
    private ?Participant $winner = null;

    public function __construct(
        public readonly int $index,
        private readonly Participant $participantA,
        private readonly Participant $participantB,
    ) {}

    public function participantA(): Participant
    {
        return $this->participantA;
    }

    public function participantB(): Participant
    {
        return $this->participantB;
    }

    public function winner(): ?Participant
    {
        return $this->winner;
    }

    public function isResolved(): bool
    {
        return $this->winner !== null;
    }

    /**
     * @throws InvalidPoolMatchResultException Si le vainqueur ne fait pas partie du match.
     */
    public function recordResult(Participant $winner): void
    {
        if (! $winner->equals($this->participantA) && ! $winner->equals($this->participantB)) {
            throw new InvalidPoolMatchResultException('Le vainqueur doit être l’un des deux participants du match.');
        }

        $this->winner = $winner;
    }
}
