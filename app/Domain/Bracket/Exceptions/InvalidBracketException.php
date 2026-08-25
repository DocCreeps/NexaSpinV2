<?php

namespace App\Domain\Bracket\Exceptions;

use DomainException;

/**
 * Exception du Domaine levée lors de la violation d'un invariant du bracket
 * (ex : nombre de participants insuffisant, match introuvable).
 */
final class InvalidBracketException extends DomainException {}
