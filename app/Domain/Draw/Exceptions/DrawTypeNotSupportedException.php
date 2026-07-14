<?php

namespace App\Domain\Draw\Exceptions;

use App\Domain\Draw\Enums\DrawType;
use DomainException;

/**
 * Exception du Domaine levée lorsqu'un type de tirage n'est pas pris en charge.
 * Étend DomainException pour matérialiser une violation des règles métier.
 */
class DrawTypeNotSupportedException extends DomainException
{
    /**
     * Constructeur nommé (Named Constructor) pour encapsuler la création de l'exception.
     * Centralise le formatage du message d'erreur en s'appuyant sur la valeur de l'Enum.
     */
    public static function forType(DrawType $type): self
    {
        return new self(
            "Le type de tirage [{$type->value}] n'est pas encore supporté."
        );
    }
}
