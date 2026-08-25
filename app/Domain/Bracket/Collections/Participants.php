<?php

namespace App\Domain\Bracket\Collections;

use App\Domain\Bracket\Exceptions\InvalidBracketException;
use App\Domain\Bracket\ValueObjects\Participant;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection typée de participants du bracket.
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
                throw new InvalidBracketException('Invalid participant collection.');
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
