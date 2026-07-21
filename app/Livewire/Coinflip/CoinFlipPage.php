<?php

namespace App\Livewire\CoinFlip;

use App\Application\CoinFlip\Actions\FlipCoinAction;
use App\Application\Draw\Enums\DrawModeType;
use Livewire\Component;

/**
 * Composant de la page de tirage "Pile ou Face".
 */
class CoinFlipPage extends Component
{
    /**
     * Faces autorisées pour valider les paris.
     *
     * @var array<int, string>
     */
    private const SIDES = ['Pile', 'Face'];

    /**
     * Limite de l'historique conservé en mémoire.
     */
    private const MAX_HISTORY = 1000;

    /**
     * Limites de tirages pour le mode automatique.
     */
    private const MIN_AUTO_FLIPS = 2;

    private const MAX_AUTO_FLIPS = 1000;

    /**
     * Dernier résultat tiré ("Pile" ou "Face").
     */
    public ?string $result = null;

    /**
     * Historique chronologique des tirages de la session.
     *
     * @var array<int, string>
     */
    public array $history = [];

    /**
     * Message d'erreur de validation pour l'interface utilisateur.
     */
    public ?string $error = null;

    /**
     * Nombre de tirages à exécuter en mode automatique.
     */
    public int $autoFlipCount = 10;

    /**
     * Pari actif de l'utilisateur ("Pile" ou "Face").
     */
    public ?string $guess = null;

    /**
     * Indique si le dernier tirage correspondait au pari (null si aucun pari actif).
     */
    public ?bool $lastGuessCorrect = null;

    /**
     * Nombre total de tirages effectués avec un pari actif.
     */
    public int $guessedFlips = 0;

    /**
     * Nombre de paris réussis.
     */
    public int $correctGuesses = 0;


    /**
     * Effectue un tirage simple et déclenche l'animation.
     */
    public function flip(FlipCoinAction $action): void
    {
        $this->error = null;

        $this->result = $this->performFlip($action);

        $this->dispatch('coin-flip', face: $this->result);
    }

    /**
     * Exécute une série de tirages automatiques et anime la pièce sur le dernier résultat.
     */
    public function flipMultiple(FlipCoinAction $action): void
    {
        $this->error = null;

        if ($this->autoFlipCount < self::MIN_AUTO_FLIPS || $this->autoFlipCount > self::MAX_AUTO_FLIPS) {
            $this->error = sprintf(
                'Le nombre de tirages automatiques doit être compris entre %d et %d.',
                self::MIN_AUTO_FLIPS,
                self::MAX_AUTO_FLIPS
            );

            return;
        }

        for ($i = 0; $i < $this->autoFlipCount; $i++) {
            $this->result = $this->performFlip($action);
        }

        $this->dispatch('coin-flip', face: $this->result);
    }

    /**
     * Réinitialise l'historique et les statistiques de la session.
     */
    public function resetHistory(): void
    {
        $this->result = null;
        $this->history = [];
        $this->error = null;
        $this->guess = null;
        $this->lastGuessCorrect = null;
        $this->guessedFlips = 0;
        $this->correctGuesses = 0;

        $this->dispatch('coin-flip-reset');
    }

    /**
     * Retourne le nombre total de tirages effectués.
     */
    public function totalFlips(): int
    {
        return count($this->history);
    }

    /**
     * Retourne le nombre de tirages "Pile".
     */
    public function pileCount(): int
    {
        return count(array_filter(
            $this->history,
            fn(string $face) => $face === 'Pile'
        ));
    }

    /**
     * Retourne le nombre de tirages "Face".
     */
    public function faceCount(): int
    {
        return count(array_filter(
            $this->history,
            fn(string $face) => $face === 'Face'
        ));
    }

    /**
     * Exécute la logique métier d'un tirage (historique, paris, limites).
     */
    private function performFlip(FlipCoinAction $action): string
    {
        $result = $action->execute();
        $face = $result->side->label();

        $this->history[] = $face;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        if ($this->guess !== null) {
            $this->guessedFlips++;
            $this->lastGuessCorrect = $this->guess === $face;

            if ($this->lastGuessCorrect) {
                $this->correctGuesses++;
            }
        } else {
            $this->lastGuessCorrect = null;
        }

        return $face;
    }

    /**
     * Rendu de la vue Livewire avec son layout principal.
     */
    public function render()
    {
        $mode = DrawModeType::COIN_FLIP->toDto();

        return view('livewire.coin-flip.coin-flip-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
