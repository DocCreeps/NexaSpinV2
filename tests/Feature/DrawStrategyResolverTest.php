<?php

use App\Application\Draw\Resolvers\DrawStrategyResolver;
use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Strategies\RandomDrawStrategy;
use App\Domain\Draw\Strategies\WeightedDrawStrategy;

it('resolves a RandomDrawStrategy for the random draw type', function () {
    $resolver = new DrawStrategyResolver(new RandomDrawStrategy, new WeightedDrawStrategy);

    expect($resolver->resolve(DrawType::RANDOM))->toBeInstanceOf(RandomDrawStrategy::class);
});

it('resolves a WeightedDrawStrategy for the weighted draw type', function () {
    $resolver = new DrawStrategyResolver(new RandomDrawStrategy, new WeightedDrawStrategy);

    expect($resolver->resolve(DrawType::WEIGHTED))->toBeInstanceOf(WeightedDrawStrategy::class);
});
