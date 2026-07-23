<?php

namespace App\Domain\Draw\Collections;

use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection typée de participants du Domaine.
 */
final class Participants implements Countable, IteratorAggregate
{
    /**
     * @param array<int, Participant> $items
     */
    public function __construct(
        private array $items
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->items as $participant) {
            if (! $participant instanceof Participant) {
                throw new InvalidDrawException('Invalid participant collection.');
            }
        }
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function first(): Participant
    {
        if ($this->count() === 0) {
            throw new InvalidDrawException('Cannot get first participant from empty collection.');
        }

        return reset($this->items);
    }

    /**
     * Sélectionne un participant au hasard via CSPRNG (random_int).
     */
    public function random(): Participant
    {
        if ($this->count() === 0) {
            throw new InvalidDrawException('Cannot select random participant from empty collection.');
        }

        $values = array_values($this->items);

        return $values[random_int(0, count($values) - 1)];
    }

    /**
     * @return array<int, Participant>
     */
    public function all(): array
    {
        return $this->items;
    }
}
