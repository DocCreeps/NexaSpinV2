<?php

use App\Application\Sticks\Actions\ChooseAiMoveAction;
use App\Application\Sticks\Actions\ReplaySticksMovesAction;
use App\Domain\Sticks\Entities\SticksGame;
use App\Domain\Sticks\Enums\SticksVariant;
use App\Domain\Sticks\Exceptions\InvalidSticksMoveException;
use App\Domain\Sticks\Strategies\OptimalSticksStrategy;

function takeFirstSticks(SticksGame $game, int $count): void
{
    $game->remove(array_slice($game->remainingSticks(), 0, $count));
}

/** Construit une partie de 50 bâtonnets où il n'en reste plus que $remaining. */
function sticksGameWithRemaining(int $remaining): SticksGame
{
    $game = new SticksGame(50);

    while ($game->remaining() > $remaining) {
        takeFirstSticks($game, min(3, $game->remaining() - $remaining));
    }

    return $game;
}

it('starts with 21 sticks by default and player 1 to move', function () {
    $game = new SticksGame;

    expect($game->stickCount)->toBe(21)
        ->and($game->remaining())->toBe(21)
        ->and($game->currentPlayer())->toBe(1)
        ->and($game->isOver())->toBeFalse()
        ->and($game->loser())->toBeNull();
});

it('refuses a stick count outside 16-50', function (int $count) {
    new SticksGame($count);
})->with([0, 15, 51, 100])->throws(InvalidArgumentException::class);

it('accepts the boundary stick counts', function (int $count) {
    expect((new SticksGame($count))->remaining())->toBe($count);
})->with([16, 21, 50]);

it('refuses an invalid first player', function () {
    new SticksGame(21, 3);
})->throws(InvalidArgumentException::class);

it('removes one to three sticks and alternates players', function () {
    $game = new SticksGame;

    $game->remove([0]);
    expect($game->remaining())->toBe(20)->and($game->currentPlayer())->toBe(2);

    $game->remove([5, 6]);
    expect($game->remaining())->toBe(18)->and($game->currentPlayer())->toBe(1);

    $game->remove([10, 11, 12]);
    expect($game->remaining())->toBe(15)->and($game->currentPlayer())->toBe(2)
        ->and($game->movesCount())->toBe(3);
});

it('lets the second player open when asked', function () {
    $game = new SticksGame(21, 2);

    expect($game->currentPlayer())->toBe(2);

    $game->remove([0]);

    expect($game->currentPlayer())->toBe(1);
});

it('refuses to remove zero sticks or more than three', function (array $indices) {
    (new SticksGame)->remove($indices);
})->with([
    'none' => [[]],
    'four' => [[0, 1, 2, 3]],
])->throws(InvalidSticksMoveException::class);

it('refuses unknown, duplicated and already removed sticks without changing the state', function () {
    $game = new SticksGame;
    $game->remove([3]);

    foreach ([[-1], [21], [3], [4, 4]] as $bad) {
        expect(fn () => $game->remove($bad))->toThrow(InvalidSticksMoveException::class);
    }

    expect($game->remaining())->toBe(20)->and($game->currentPlayer())->toBe(2);
});

it('lets sticks be removed anywhere, adjacent or not', function () {
    $game = new SticksGame;
    $game->remove([0, 10, 20]);

    expect($game->remaining())->toBe(18)
        ->and($game->isRemoved(10))->toBeTrue()
        ->and($game->isRemoved(11))->toBeFalse()
        ->and($game->remainingSticks())->not->toContain(0, 10, 20);
});

it('ends as soon as one stick remains, and the player to move gets it and loses', function () {
    $game = new SticksGame(16);

    foreach (range(1, 5) as $unused) {
        takeFirstSticks($game, 3); // 16 -> 13 -> 10 -> 7 -> 4 -> 1
    }

    // 5 coups joués : c'est au joueur 2 de jouer, il récupère le dernier bâtonnet.
    expect($game->remaining())->toBe(1)
        ->and($game->isOver())->toBeTrue()
        ->and($game->loser())->toBe(2)
        ->and($game->winner())->toBe(1);
});

it('makes the player who empties the table lose', function () {
    $game = new SticksGame(16);

    foreach ([3, 3, 3, 3, 2] as $count) {
        takeFirstSticks($game, $count); // il reste 2 bâtonnets, c'est au joueur 2
    }

    expect($game->remaining())->toBe(2)->and($game->isOver())->toBeFalse();

    takeFirstSticks($game, 2);

    expect($game->remaining())->toBe(0)
        ->and($game->isOver())->toBeTrue()
        ->and($game->loser())->toBe(2)
        ->and($game->winner())->toBe(1);
});

it('refuses any move once the game is over', function () {
    $game = sticksGameWithRemaining(1);

    expect($game->isOver())->toBeTrue();

    $game->remove([$game->remainingSticks()[0]]);
})->throws(InvalidSticksMoveException::class);

it('replays a list of moves into the same game', function () {
    $moves = [[0, 1, 2], [3], [4, 5]];

    $game = (new ReplaySticksMovesAction)->execute(21, $moves);

    expect($game->remaining())->toBe(15)
        ->and($game->currentPlayer())->toBe(2)
        ->and((new ReplaySticksMovesAction)->execute(21, $moves, 2)->currentPlayer())->toBe(1);
});

it('leaves a multiple of four plus one stick whenever it can', function () {
    $strategy = new OptimalSticksStrategy;

    foreach (range(2, 50) as $remaining) {
        $game = sticksGameWithRemaining($remaining);
        $take = $strategy->choose($game);

        expect($take)->toBeBetween(1, min(3, $remaining));

        if (($remaining - 1) % 4 !== 0) {
            expect(($remaining - $take) % 4)->toBe(1);
        }
    }
});

it('only ever returns a legal number of sticks, even when it always makes mistakes', function () {
    $strategy = new OptimalSticksStrategy(errorRatePercent: 100);

    foreach (range(2, 12) as $remaining) {
        for ($i = 0; $i < 30; $i++) {
            expect($strategy->choose(sticksGameWithRemaining($remaining)))->toBeBetween(1, min(3, $remaining));
        }
    }
});

it('always wins from a winning position, whatever its opponent plays', function (int $count) {
    $optimal = new ChooseAiMoveAction(new OptimalSticksStrategy);
    $random = new ChooseAiMoveAction(new OptimalSticksStrategy(errorRatePercent: 100));

    // Sans coup d'avance (reste congru à 1 modulo 4), c'est le second joueur qui est en position gagnante.
    $aiPlayer = ($count - 1) % 4 === 0 ? 2 : 1;

    for ($i = 0; $i < 20; $i++) {
        $game = new SticksGame($count);

        while (! $game->isOver()) {
            $game->remove(($game->currentPlayer() === $aiPlayer ? $optimal : $random)->execute($game));
        }

        expect($game->winner())->toBe($aiPlayer);
    }
})->with([16, 17, 18, 19, 20, 21, 22, 25, 33, 49, 50]);

it('picks a block of neighbouring remaining sticks, even around gaps', function () {
    $action = new ChooseAiMoveAction(new OptimalSticksStrategy(errorRatePercent: 100));

    for ($i = 0; $i < 100; $i++) {
        $game = new SticksGame;
        $game->remove([2]);
        $game->remove([10, 11]);

        $indices = $action->execute($game);
        $remaining = $game->remainingSticks();
        $start = array_search($indices[0], $remaining, true);

        expect($indices)->toHaveCount(count(array_unique($indices)))
            ->and(count($indices))->toBeBetween(1, 3)
            ->and(array_slice($remaining, $start, count($indices)))->toBe($indices);
    }
});

it('only ends on an empty table in the last-wins variant, and the player who empties it wins', function () {
    $game = new SticksGame(16, 1, SticksVariant::LAST_WINS);

    foreach ([3, 3, 3, 3, 3] as $count) {
        takeFirstSticks($game, $count); // il reste 1 bâtonnet, c'est au joueur 2
    }

    // Contrairement à la variante classique, un bâtonnet restant ne termine pas la partie.
    expect($game->remaining())->toBe(1)
        ->and($game->isOver())->toBeFalse()
        ->and($game->winner())->toBeNull();

    takeFirstSticks($game, 1);

    expect($game->isOver())->toBeTrue()
        ->and($game->winner())->toBe(2)
        ->and($game->loser())->toBe(1);
});

it('replays a last-wins game with the same variant', function () {
    $moves = [[0, 1, 2], [3, 4, 5]];

    $game = (new ReplaySticksMovesAction)->execute(16, $moves, 1, SticksVariant::LAST_WINS);

    expect($game->variant)->toBe(SticksVariant::LAST_WINS)
        ->and($game->remaining())->toBe(10);
});

it('leaves a multiple of four sticks in the last-wins variant', function () {
    $strategy = new OptimalSticksStrategy;

    foreach (range(1, 50) as $remaining) {
        $game = new SticksGame(50, 1, SticksVariant::LAST_WINS);

        while ($game->remaining() > $remaining) {
            takeFirstSticks($game, min(3, $game->remaining() - $remaining));
        }

        $take = $strategy->choose($game);

        expect($take)->toBeBetween(1, min(3, $remaining));

        if ($remaining % 4 !== 0) {
            expect(($remaining - $take) % 4)->toBe(0);
        }
    }
});

it('always wins the last-wins variant from a winning position', function (int $count) {
    $optimal = new ChooseAiMoveAction(new OptimalSticksStrategy);
    $random = new ChooseAiMoveAction(new OptimalSticksStrategy(errorRatePercent: 100));

    // Reste multiple de 4 : le second joueur est en position gagnante, sinon le premier.
    $aiPlayer = $count % 4 === 0 ? 2 : 1;

    for ($i = 0; $i < 20; $i++) {
        $game = new SticksGame($count, 1, SticksVariant::LAST_WINS);

        while (! $game->isOver()) {
            $game->remove(($game->currentPlayer() === $aiPlayer ? $optimal : $random)->execute($game));
        }

        expect($game->winner())->toBe($aiPlayer);
    }
})->with([16, 17, 18, 19, 20, 21, 24, 33, 49, 50]);
