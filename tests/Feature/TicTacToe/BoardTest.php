<?php

use App\Application\TicTacToe\Actions\ReplayMovesAction;
use App\Domain\TicTacToe\Entities\Board;
use App\Domain\TicTacToe\Enums\Mark;
use App\Domain\TicTacToe\Exceptions\InvalidMoveException;

it('starts with an empty board and X to play by default', function () {
    $board = new Board();

    expect($board->cells())->toBe(array_fill(0, 9, null))
        ->and($board->currentTurn())->toBe(Mark::X)
        ->and($board->isOver())->toBeFalse();
});

it('alternates turns after each move', function () {
    $board = new Board();

    $board->play(0);
    expect($board->currentTurn())->toBe(Mark::O);

    $board->play(1);
    expect($board->currentTurn())->toBe(Mark::X);
});

it('detects a horizontal line win', function () {
    $board = new Board();

    $board->play(0); // X
    $board->play(3); // O
    $board->play(1); // X
    $board->play(4); // O
    $board->play(2); // X -> ligne 0,1,2

    expect($board->winner())->toBe(Mark::X)
        ->and($board->isOver())->toBeTrue();
});

it('detects a vertical line win', function () {
    $board = new Board();

    $board->play(0); // X
    $board->play(1); // O
    $board->play(3); // X
    $board->play(2); // O
    $board->play(6); // X -> colonne 0,3,6

    expect($board->winner())->toBe(Mark::X);
});

it('detects a diagonal win', function () {
    $board = new Board();

    $board->play(0); // X
    $board->play(1); // O
    $board->play(4); // X
    $board->play(2); // O
    $board->play(8); // X -> diagonale 0,4,8

    expect($board->winner())->toBe(Mark::X);
});

it('declares a draw when the board is full without any winning line', function () {
    $board = new Board();

    // Grille classique de match nul :
    // X O X
    // X O O
    // O X X
    foreach ([0, 1, 2, 4, 3, 5, 7, 6, 8] as $position) {
        $board->play($position);
    }

    expect($board->isDraw())->toBeTrue()
        ->and($board->winner())->toBeNull()
        ->and($board->isOver())->toBeTrue();
});

it('refuses to play on an already occupied cell', function () {
    $board = new Board();

    $board->play(0);
    $board->play(0);
})->throws(InvalidMoveException::class);

it('refuses a position outside the 0-8 range', function (int $position) {
    (new Board())->play($position);
})->with([-1, 9, 100])->throws(InvalidMoveException::class);

it('refuses to play once the game is already over', function () {
    $board = new Board();

    $board->play(0); // X
    $board->play(3); // O
    $board->play(1); // X
    $board->play(4); // O
    $board->play(2); // X gagne

    $board->play(5);
})->throws(InvalidMoveException::class);

it('replays a sequence of moves to reconstruct the same board state', function () {
    $moves = [0, 3, 1, 4, 2]; // X gagne sur la ligne du haut

    $board = (new ReplayMovesAction())->execute($moves);

    expect($board->winner())->toBe(Mark::X)
        ->and($board->isOver())->toBeTrue();
});

it('replays an empty move list into a fresh board', function () {
    $board = (new ReplayMovesAction())->execute([]);

    expect($board->cells())->toBe(array_fill(0, 9, null))
        ->and($board->isOver())->toBeFalse();
});
