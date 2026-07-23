<?php

namespace App\Domain\Draw\Collections;

use App\Domain\Draw\Exceptions\InvalidDrawException;
use App\Domain\Draw\ValueObjects\Participant;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Collection typée du Domaine (Domain Collection).
 * Encapsule un tableau de participants pour lui appliquer des règles métier.
 */
final class Participants implements Countable, IteratorAggregate
{
    /**
     * @param  array<int, Participant>  $items
     */
    public function __construct(
        private array $items
    ) {
        // Approche "Fail-Fast" : validation immédiate dès l'instanciation.
        $this->validate();
    }

    /**
     * Assure la sécurité du typage (Type Safety) en l'absence de génériques natifs en PHP.
     */
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

    /**
     * Permet d'utiliser count($collection) directement (interface Countable).
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Rend l'objet itérable dans un foreach classique (interface IteratorAggregate).
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Récupère le premier participant. Sécurisé contre les collections vides.
     */
    public function first(): Participant
    {
        if ($this->count() === 0) {
            throw new InvalidDrawException(
                'Cannot get first participant from empty collection.'
            );
        }

        // reset() renvoie le premier élément quelles que soient les clés du tableau
        // (contrairement à $this->items[0], qui suppose à tort une ré-indexation à 0).
        return reset($this->items);
    }

    /**
     * Sélectionne un participant au hasard via random_int (CSPRNG).
     *
     * Note technique : on utilise volontairement random_int() plutôt qu'array_rand().
     * array_rand() s'appuie sur le générateur Mersenne Twister de PHP, qui n'est pas
     * cryptographiquement sûr et est prévisible si l'état interne est connu ou
     * partiellement déduit. random_int() garantit un tirage réellement imprévisible,
     * cohérent avec celui déjà utilisé par WeightedDrawStrategy.
     */
    public function random(): Participant
    {
        if ($this->count() === 0) {
            throw new InvalidDrawException(
                'Cannot select random participant from empty collection.'
            );
        }

        // On travaille sur les valeurs réindexées : $this->items peut contenir
        // des clés non contiguës après suppression/élimination de participants.
        $values = array_values($this->items);

        return $values[random_int(0, count($values) - 1)];
    }

    /**
     * @return array<int, Participant> Extraction du tableau sous-jacent.
     */
    public function all(): array
    {
        return $this->items;
    }
}
