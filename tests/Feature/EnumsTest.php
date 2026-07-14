<?php

use App\Domain\Draw\Enums\DrawDisplay;
use App\Domain\Draw\Enums\DrawType;

it('exposes exactly the two implemented DrawType cases', function () {
    expect(DrawType::cases())->toHaveCount(2)
        ->and(array_column(DrawType::cases(), 'value'))->toBe(['random', 'weighted']);
});

it('labels DrawType cases in French', function () {
    expect(DrawType::RANDOM->label())->toBe('Aléatoire')
        ->and(DrawType::WEIGHTED->label())->toBe('Pondéré');
});

it('resolves DrawType from its raw string value', function () {
    expect(DrawType::tryFrom('random'))->toBe(DrawType::RANDOM)
        ->and(DrawType::tryFrom('unknown'))->toBeNull();
});

it('exposes exactly the two DrawDisplay cases', function () {
    expect(DrawDisplay::cases())->toHaveCount(2)
        ->and(array_column(DrawDisplay::cases(), 'value'))->toBe(['simple', 'wheel']);
});

it('labels DrawDisplay cases in French', function () {
    expect(DrawDisplay::SIMPLE->label())->toBe('Simple')
        ->and(DrawDisplay::WHEEL->label())->toBe('Roue');
});
