<?php

namespace App\Domain\Sticks\Entities;

use App\Domain\Sticks\Enums\SticksVariant;
use App\Domain\Sticks\Exceptions\InvalidSticksMoveException;
use InvalidArgumentException;

/**
 * Entité du Domaine : une partie du jeu des bâtonnets.
 *
 * N bâtonnets sont alignés. À tour de rôle, chaque joueur en retire un, deux ou
 * trois, où il le souhaite. Deux variantes (SticksVariant) :
 * - « le dernier perd » (classique) : celui qui se retrouve avec le dernier bâtonnet
 *   perd ; la partie s'arrête dès qu'il n'en reste qu'un (le joueur dont c'est le tour
 *   le récupère), ou si un joueur vide la table en retirant les derniers (il perd alors) ;
 * - « le dernier gagne » : la partie s'arrête quand la table est vide, et celui qui a
 *   retiré les derniers bâtonnets gagne.
 *
 * Always-Valid Entity : les bornes sont validées à la construction, chaque coup
 * l'est avant toute modification d'état.
 */
final class SticksGame
{
    public const MIN_STICKS = 16;

    public const MAX_STICKS = 50;

    public const DEFAULT_STICKS = 21;

    public const MIN_TAKE = 1;

    public const MAX_TAKE = 3;

    /** @var array<int, true> Bâtonnets déjà retirés, indexés par position. */
    private array $removed = [];

    private int $movesCount = 0;

    private ?int $lastMover = null;

    /**
     * @param  int  $firstPlayer  Joueur (1 ou 2) qui retire en premier.
     */
    public function __construct(
        public readonly int $stickCount = self::DEFAULT_STICKS,
        public readonly int $firstPlayer = 1,
        public readonly SticksVariant $variant = SticksVariant::LAST_LOSES,
    ) {
        if ($stickCount < self::MIN_STICKS || $stickCount > self::MAX_STICKS) {
            throw new InvalidArgumentException(
                sprintf('Stick count must be between %d and %d.', self::MIN_STICKS, self::MAX_STICKS)
            );
        }

        if ($firstPlayer !== 1 && $firstPlayer !== 2) {
            throw new InvalidArgumentException('First player must be 1 or 2.');
        }
    }

    /**
     * Retire les bâtonnets donnés (positions 0-based) pour le joueur courant.
     *
     * @param  array<int, int>  $indices
     *
     * @throws InvalidSticksMoveException
     */
    public function remove(array $indices): void
    {
        if ($this->isOver()) {
            throw new InvalidSticksMoveException('La partie est terminée.');
        }

        $indices = array_values($indices);
        $count = count($indices);

        if ($count < self::MIN_TAKE || $count > self::MAX_TAKE) {
            throw new InvalidSticksMoveException(
                sprintf('Retirez entre %d et %d bâtonnets.', self::MIN_TAKE, self::MAX_TAKE)
            );
        }

        if (count(array_unique($indices)) !== $count) {
            throw new InvalidSticksMoveException('Un même bâtonnet ne peut pas être retiré deux fois.');
        }

        foreach ($indices as $index) {
            if ($index < 0 || $index >= $this->stickCount) {
                throw new InvalidSticksMoveException('Ce bâtonnet n’existe pas.');
            }

            if (isset($this->removed[$index])) {
                throw new InvalidSticksMoveException('Ce bâtonnet a déjà été retiré.');
            }
        }

        $player = $this->currentPlayer();

        foreach ($indices as $index) {
            $this->removed[$index] = true;
        }

        $this->movesCount++;
        $this->lastMover = $player;
    }

    public function remaining(): int
    {
        return $this->stickCount - count($this->removed);
    }

    /**
     * Positions des bâtonnets encore en jeu, dans l'ordre.
     *
     * @return array<int, int>
     */
    public function remainingSticks(): array
    {
        return array_values(array_filter(
            range(0, $this->stickCount - 1),
            fn (int $index) => ! isset($this->removed[$index])
        ));
    }

    public function isRemoved(int $index): bool
    {
        return isset($this->removed[$index]);
    }

    public function currentPlayer(): int
    {
        return $this->movesCount % 2 === 0
            ? $this->firstPlayer
            : 3 - $this->firstPlayer;
    }

    public function movesCount(): int
    {
        return $this->movesCount;
    }

    public function isOver(): bool
    {
        return $this->variant === SticksVariant::LAST_WINS
            ? $this->remaining() === 0
            : $this->remaining() <= 1;
    }

    /** Joueur qui perd, null tant que la partie est en cours. */
    public function loser(): ?int
    {
        if (! $this->isOver()) {
            return null;
        }

        // Variante inversée : le dernier à avoir joué a vidé la table et gagne.
        if ($this->variant === SticksVariant::LAST_WINS) {
            return 3 - (int) $this->lastMover;
        }

        // Table vidée : le dernier à avoir joué a pris le dernier bâtonnet.
        // Sinon, il reste un bâtonnet : le joueur dont c'est le tour le récupère.
        return $this->remaining() === 0 ? $this->lastMover : $this->currentPlayer();
    }

    public function winner(): ?int
    {
        $loser = $this->loser();

        return $loser === null ? null : 3 - $loser;
    }
}
