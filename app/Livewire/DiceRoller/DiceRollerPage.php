<?php

namespace App\Livewire\DiceRoller;

use App\Application\DiceRoller\Actions\RollDiceSetAction;
use App\Application\Home\Enums\GameModeType;
use App\Domain\DiceRoller\Enums\DiceFaceCount;
use App\Domain\DiceRoller\Exceptions\InvalidDiceRollException;
use Livewire\Component;

/**
 * Lanceur de dés générique façon JDR : choix du type de dé (uniquement
 * parmi les types réellement existants, voir DiceFaceCount) et du nombre de
 * dés à lancer d'un coup.
 *
 * AUCUN historique persisté ici (choix produit assumé) : $rolls ne vit que
 * pour la session Livewire en cours (le temps que la page reste ouverte),
 * jamais poussé dans HistoryStore/le cache — contrairement à tous les
 * autres modes du site. Il se vide donc au moindre rechargement de page,
 * et n'apparaîtra jamais sur /historique.
 */
class DiceRollerPage extends Component
{
    private const MIN_DICE = 1;

    private const MAX_DICE = 20;

    /** Borne défensive sur la taille de $rolls : ce tableau est renvoyé au
     * client à chaque requête Livewire (pas de pagination côté cache comme
     * HistoryStore), il ne doit donc pas grossir indéfiniment sur une
     * session très active. */
    private const MAX_SESSION_ROLLS = 50;

    public int $facesValue = 6;

    public int $diceCount = 2;

    public ?string $error = null;

    /** @var array{faces: int, values: array<int, int>, sum: int}|null */
    public ?array $lastResult = null;

    /** @var array<int, array{faces: int, values: array<int, int>, sum: int}> */
    public array $rolls = [];

    /** @return array<int, DiceFaceCount> */
    public function availableFaces(): array
    {
        return DiceFaceCount::cases();
    }

    public function selectFaces(int $faces): void
    {
        if (DiceFaceCount::tryFrom($faces) === null) {
            return;
        }

        $this->facesValue = $faces;
        $this->error = null;
    }

    public function incrementDiceCount(): void
    {
        $this->diceCount = min($this->diceCount + 1, self::MAX_DICE);
    }

    public function decrementDiceCount(): void
    {
        $this->diceCount = max($this->diceCount - 1, self::MIN_DICE);
    }

    public function roll(RollDiceSetAction $action): void
    {
        $this->error = null;

        $faces = DiceFaceCount::from($this->facesValue);

        try {
            $result = $action->execute($faces, $this->diceCount);
        } catch (InvalidDiceRollException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $entry = [
            'faces' => $faces->value,
            'values' => $result->values,
            'sum' => $result->sum(),
        ];

        $this->lastResult = $entry;
        $this->rolls[] = $entry;

        if (count($this->rolls) > self::MAX_SESSION_ROLLS) {
            $this->rolls = array_slice($this->rolls, -self::MAX_SESSION_ROLLS);
        }
    }

    public function clearRolls(): void
    {
        $this->rolls = [];
        $this->lastResult = null;
    }

    public function render()
    {
        $mode = GameModeType::DICE_ROLLER->toDto();

        return view('livewire.dice-roller.dice-roller-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
