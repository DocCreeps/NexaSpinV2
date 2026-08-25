<?php

namespace App\Domain\Tournament\Collections;

use App\Domain\Tournament\ValueObjects\Participant;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Collection typée de participants, partagée entre les sous-domaines Bracket
 * et Pool.
 */
final class Participants implements Countable, IteratorAggregate
{
    /**
     * @param array<int, Participant> $items
     */
    public function __construct(private array $items)
    {
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->items as $participant) {
            if (! $participant instanceof Participant) {
                throw new InvalidArgumentException('Invalid participant collection.');
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

    /**
     * @return array<int, Participant>
     */
    public function all(): array
    {
        return array_values($this->items);
    }
}
