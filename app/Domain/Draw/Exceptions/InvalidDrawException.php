<?php

namespace App\Domain\Draw\Exceptions;

use DomainException;

/**
 * Exception du Domaine levée lors de la violation d'un invariant ou d'une règle métier.
 *
 * Note technique : Étend DomainException pour distinguer les erreurs fonctionnelles (liées aux règles)
 * des erreurs d'infrastructure. Le mot-clé "final" empêche toute dérive par héritage.
 */
final class InvalidDrawException extends DomainException {}
