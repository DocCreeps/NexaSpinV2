<?php

namespace App\Domain\Tournament\Collections;

use App\Domain\Shared\Collections\ParticipantsCollection;
use App\Domain\Tournament\ValueObjects\Participant;
use InvalidArgumentException;
use Throwable;

/**
 * Collection typée de participants, partagée entre les sous-domaines Bracket
 * et Pool.
 *
 * @extends ParticipantsCollection<Participant>
 */
final class Participants extends ParticipantsCollection
{
    protected function itemClass(): string
    {
        return Participant::class;
    }

    protected function invalidItemException(string $message): Throwable
    {
        return new InvalidArgumentException($message);
    }

    /**
     * @return array<int, Participant>
     */
    public function all(): array
    {
        return array_values(parent::all());
    }
}
