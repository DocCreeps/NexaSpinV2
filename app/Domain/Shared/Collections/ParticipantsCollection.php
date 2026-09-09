<?php

namespace App\Domain\Shared\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Throwable;
use Traversable;

/**
 * Base commune aux collections typées de participants de chaque domaine
 * (Draw, Tournament...) : factorise l'itération, le comptage et la
 * validation de type, identiques partout, sans coupler les domaines entre
 * eux — chaque sous-classe reste responsable de son propre Participant
 * (Value Object) et de sa propre exception métier.
 *
 * @template T of object
 */
abstract class ParticipantsCollection implements Countable, IteratorAggregate
{
    /**
     * @param  array<int, T>  $items
     */
    public function __construct(private array $items)
    {
        $this->validate();
    }

    /**
     * FQCN du Participant attendu par ce domaine.
     *
     * @return class-string<T>
     */
    abstract protected function itemClass(): string;

    /**
     * Construit l'exception métier à lever quand un élément ne correspond
     * pas au type attendu — c'est ce qui permet à chaque domaine de garder
     * sa propre exception (ex. `InvalidDrawException` pour Draw) plutôt que
     * de partager un type d'exception générique entre domaines.
     */
    abstract protected function invalidItemException(string $message): Throwable;

    private function validate(): void
    {
        $expected = $this->itemClass();

        foreach ($this->items as $item) {
            if (! $item instanceof $expected) {
                throw $this->invalidItemException('Invalid participant collection.');
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
     * @return array<int, T>
     */
    public function all(): array
    {
        return $this->items;
    }
}
