<?php

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Exceptions\DrawTypeNotSupportedException;
use App\Domain\Draw\Exceptions\InvalidDrawException;

it('builds a DrawTypeNotSupportedException naming the unsupported type', function () {
    $exception = DrawTypeNotSupportedException::forType(DrawType::WEIGHTED);

    expect($exception)->toBeInstanceOf(DomainException::class)
        ->and($exception->getMessage())->toContain('weighted');
});

it('InvalidDrawException is a DomainException', function () {
    expect(new InvalidDrawException('test'))->toBeInstanceOf(DomainException::class);
});
