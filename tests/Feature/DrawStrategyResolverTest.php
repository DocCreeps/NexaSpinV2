<?php

use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\Strategies\RandomDrawStrategy;

it('resolves a RandomDrawStrategy for the random draw type', function () {
    $resolver = new DrawStrategyResolver(new RandomDrawStrategy);

    expect($resolver->resolve(DrawType::RANDOM))->toBeInstanceOf(RandomDrawStrategy::class);
});

it('throws a domain exception for the weighted draw type (not yet implemented)', function () {
    $resolver = new DrawStrategyResolver(new RandomDrawStrategy);

    $resolver->resolve(DrawType::WEIGHTED);
})->throws(DrawTypeNotSupportedException::class);
