<?php

namespace App\Domain\Draw\Exceptions;

use DomainException;

/**
 * Exception levée lorsqu'un tirage ne respecte
 * pas les règles métier.
 */
final class InvalidDrawException extends DomainException {}
