<?php

namespace App\Domain\Draw\Collections;

use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;

use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;


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

                throw new InvalidDrawException(
                    'Invalid participant collection.'
                );
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

            throw new InvalidDrawException(
                'Cannot get first participant from empty collection.'
            );
        }

        return $this->items[0];
    }


    public function random(): Participant
    {
        if ($this->count() === 0) {

            throw new InvalidDrawException(
                'Cannot select random participant from empty collection.'
            );
        }


        return $this->items[array_rand($this->items)];
    }


    /**
     * @return array<int, Participant>
     */
    public function all(): array
    {
        return $this->items;
    }
}
