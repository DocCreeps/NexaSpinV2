<?php

namespace App\Domain\Draw\Exceptions;

use App\Domain\Draw\Enums\DrawType;
use DomainException;

class DrawTypeNotSupportedException extends DomainException
{
    public static function forType(DrawType $type): self
    {
        return new self(
            "Le type de tirage [{$type->value}] n'est pas encore supporté."
        );
    }
}
