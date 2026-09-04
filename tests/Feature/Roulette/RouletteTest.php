<?php

use App\Application\Roulette\RouletteBetEvaluator;
use App\Domain\Roulette\Enums\RouletteBetType;
use App\Domain\Roulette\RoulettePocket;

it('always draws one of the 38 American roulette pockets', function () {
    $all = RoulettePocket::all();

    expect($all)->toHaveCount(38)
        ->and($all)->toContain('0')
        ->and($all)->toContain('00');

    for ($i = 0; $i < 50; $i++) {
        expect($all)->toContain(RoulettePocket::random());
    }
});

it('treats 0 and 00 as green, not red or black', function () {
    expect(RoulettePocket::color('0'))->toBe('green')
        ->and(RoulettePocket::color('00'))->toBe('green');
});

it('colors known red and black numbers correctly', function () {
    expect(RoulettePocket::color('1'))->toBe('red')
        ->and(RoulettePocket::color('2'))->toBe('black');
});

it('does not consider zero or double-zero as even', function () {
    expect(RoulettePocket::isEven('0'))->toBeFalse()
        ->and(RoulettePocket::isEven('00'))->toBeFalse();
});

it('correctly classifies even and odd numbers', function () {
    expect(RoulettePocket::isEven('2'))->toBeTrue()
        ->and(RoulettePocket::isEven('3'))->toBeFalse();
});

it('has no dozen or column for zero and double-zero', function () {
    expect(RoulettePocket::dozen('0'))->toBeNull()
        ->and(RoulettePocket::dozen('00'))->toBeNull()
        ->and(RoulettePocket::column('0'))->toBeNull()
        ->and(RoulettePocket::column('00'))->toBeNull();
});

it('assigns the correct dozen to a number', function () {
    expect(RoulettePocket::dozen('5'))->toBe(1)
        ->and(RoulettePocket::dozen('15'))->toBe(2)
        ->and(RoulettePocket::dozen('30'))->toBe(3);
});

it('assigns the correct column to a number', function () {
    expect(RoulettePocket::column('1'))->toBe(1)
        ->and(RoulettePocket::column('2'))->toBe(2)
        ->and(RoulettePocket::column('3'))->toBe(3)
        ->and(RoulettePocket::column('4'))->toBe(1);
});

it('evaluates a straight bet as winning only on the exact number', function () {
    $evaluator = new RouletteBetEvaluator();

    expect($evaluator->isWinning(RouletteBetType::STRAIGHT, '17', '17'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::STRAIGHT, '17', '18'))->toBeFalse()
        ->and($evaluator->isWinning(RouletteBetType::STRAIGHT, null, '17'))->toBeFalse();
});

it('evaluates simple chances bets against a black even number', function () {
    $evaluator = new RouletteBetEvaluator();

    // 2 is black and even, and within the "low" (manque) range.
    expect($evaluator->isWinning(RouletteBetType::BLACK, null, '2'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::RED, null, '2'))->toBeFalse()
        ->and($evaluator->isWinning(RouletteBetType::EVEN, null, '2'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::ODD, null, '2'))->toBeFalse()
        ->and($evaluator->isWinning(RouletteBetType::LOW, null, '2'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::HIGH, null, '2'))->toBeFalse();
});

it('never pays out any simple chance bet on zero or double-zero', function ($betType) {
    $evaluator = new RouletteBetEvaluator();

    expect($evaluator->isWinning($betType, null, '0'))->toBeFalse()
        ->and($evaluator->isWinning($betType, null, '00'))->toBeFalse();
})->with([
    RouletteBetType::RED,
    RouletteBetType::BLACK,
    RouletteBetType::EVEN,
    RouletteBetType::ODD,
    RouletteBetType::LOW,
    RouletteBetType::HIGH,
]);

it('evaluates dozen and column bets', function () {
    $evaluator = new RouletteBetEvaluator();

    expect($evaluator->isWinning(RouletteBetType::DOZEN_1, null, '5'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::DOZEN_2, null, '5'))->toBeFalse()
        ->and($evaluator->isWinning(RouletteBetType::COLUMN_1, null, '4'))->toBeTrue()
        ->and($evaluator->isWinning(RouletteBetType::COLUMN_2, null, '4'))->toBeFalse();
});

it('evaluates a top line bet as winning only on 0, 00, 1, 2 or 3', function () {
    $evaluator = new RouletteBetEvaluator();

    foreach (['0', '00', '1', '2', '3'] as $winningNumber) {
        expect($evaluator->isWinning(RouletteBetType::TOP_LINE, null, $winningNumber))->toBeTrue();
    }

    expect($evaluator->isWinning(RouletteBetType::TOP_LINE, null, '4'))->toBeFalse();
});

it('pays a straight bet at 35 to 1 and a simple chance at 1 to 1', function () {
    expect(RouletteBetType::STRAIGHT->payoutMultiplier())->toBe(35)
        ->and(RouletteBetType::RED->payoutMultiplier())->toBe(1)
        ->and(RouletteBetType::DOZEN_1->payoutMultiplier())->toBe(2)
        ->and(RouletteBetType::TOP_LINE->payoutMultiplier())->toBe(6);
});

it('only requires picking a number for a straight bet', function () {
    expect(RouletteBetType::STRAIGHT->requiresNumber())->toBeTrue()
        ->and(RouletteBetType::RED->requiresNumber())->toBeFalse();
});
