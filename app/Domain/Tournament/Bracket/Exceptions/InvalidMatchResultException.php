<?php

namespace App\Domain\Tournament\Bracket\Exceptions;

use DomainException;

/**
 * Exception du Domaine levée lorsqu'un résultat de match saisi est invalide
 * (match non jouable, ou vainqueur ne faisant pas partie du match).
 */
final class InvalidMatchResultException extends DomainException {}
