<?php

namespace App\Application\Bracket\Actions;

use App\Application\Bracket\DTOs\BracketData;
use App\Domain\Bracket\Entities\Bracket;

/**
 * Action (Use Case) orchestrant la création initiale d'un bracket.
 * Classe fermée à l'extension (final) possédant une unique responsabilité.
 */
final class CreateBracketAction
{
    /**
     * Construit l'entité du Domaine, ce qui applique l'invariant "min. 4
     * participants" et génère l'arbre complet (byes inclus) dès la création.
     */
    public function execute(BracketData $data): Bracket
    {
        return new Bracket($data->participantsCollection());
    }
}
