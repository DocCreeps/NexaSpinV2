<?php

use App\Application\Draw\Factories\DrawFactory;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Strategies\RandomDrawStrategy;

it('builds a RandomDrawStrategy for the random draw type', function () {
    expect(DrawFactory::make(DrawType::RANDOM))->toBeInstanceOf(RandomDrawStrategy::class);
});

it('has no strategy registered yet for the weighted draw type (known bug)', function () {
    // DrawTypeNotSupportedException existe dans le domaine précisément pour
    // ce cas, mais DrawFactory::make() n'a ni bras WEIGHTED ni bras
    // "default" : le match natif de PHP lève donc son propre
    // UnhandledMatchError au lieu de l'exception métier prévue.
    // Voir README > Limites connues / dette technique.
    DrawFactory::make(DrawType::WEIGHTED);
})->throws(UnhandledMatchError::class);
