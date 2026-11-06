<?php

namespace App\Domain\Sticks\Exceptions;

use DomainException;

/**
 * Exception du Domaine levée lorsqu'un coup du jeu des bâtonnets est invalide
 * (nombre de bâtonnets hors 1-3, bâtonnet inconnu ou déjà retiré, partie terminée).
 */
final class InvalidSticksMoveException extends DomainException {}
