<?php

namespace App\Domain\Draw\Collections;

use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;
use App\Domain\Shared\Collections\ParticipantsCollection;
use Throwable;

/**
 * Collection typée de participants du Domaine.
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
        return new InvalidDrawException($message);
    }

    public function first(): Participant
    {
        $items = $this->all();

        if ($items === []) {
            throw new InvalidDrawException('Cannot get first participant from empty collection.');
        }

        return reset($items);
    }

    /**
     * Sélectionne un participant au hasard via CSPRNG (random_int).
     */
    public function random(): Participant
    {
        $items = array_values($this->all());

        if ($items === []) {
            throw new InvalidDrawException('Cannot select random participant from empty collection.');
        }

        return $items[random_int(0, count($items) - 1)];
    }
}
