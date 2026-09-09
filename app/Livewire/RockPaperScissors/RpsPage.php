<?php

namespace App\Livewire\RockPaperScissors;

use App\Application\History\HistoryStore;
use App\Application\Home\Enums\GameModeType;
use App\Application\RockPaperScissors\Actions\PlayRpsRoundAction;
use App\Domain\RockPaperScissors\Enums\RpsChoice;
use App\Domain\RockPaperScissors\Enums\RpsOpponentType;
use App\Domain\RockPaperScissors\ValueObjects\RpsResult;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant de pierre-feuille-ciseaux, contre l'IA ou en local (2 joueurs sur
 * le même appareil : chacun joue son coup à son tour, le second choix n'est
 * dévoilé qu'une fois les deux joués).
 *
 * Regroupement par session : toutes les manches jouées depuis l'ouverture de
 * la page (ou depuis le dernier "Vider l'historique") partagent le même
 * $sessionId et sont fusionnées en une seule entrée dans l'historique global
 * (/historique) via HistoryStore::pushSession(), mise à jour manche après
 * manche plutôt qu'empilée une par une.
 */
class RpsPage extends Component
{
    private const MAX_HISTORY = 200;

    /** Identifiant de la session en cours, régénéré à l'ouverture de la page et à chaque "Vider l'historique". */
    #[Locked]
    public string $sessionId;

    /** 'ai' ou 'local'. Verrouillé dès que la session contient au moins une manche. */
    public string $opponentType = RpsOpponentType::AI->value;

    public ?string $playerChoice = null;

    public ?string $opponentChoice = null;

    public ?string $outcome = null;

    /** Choix du premier joueur en mode local, caché jusqu'au choix du second. */
    #[Locked]
    public ?string $localFirstChoice = null;

    /** @var array<int, array<string, mixed>> Manches de la session en cours. */
    public array $history = [];

    /**
     * Manche en attente de confirmation : le résultat est déterminé
     * immédiatement, mais n'atterrit dans $history/le cache qu'une fois
     * l'animation de révélation terminée côté client (voir confirmRound()),
     * pour rester cohérent avec les autres modes (CoinFlipPage, WheelPage...).
     *
     * @var array<string, mixed>|null
     */
    #[Locked]
    public ?array $pendingRound = null;

    public function mount(): void
    {
        $this->sessionId = (string) Str::uuid();
    }

    /**
     * Change l'adversaire (IA / local). Refusé une fois la session commencée
     * (au moins une manche jouée, ou une manche locale à moitié jouée) pour
     * ne pas mélanger deux types d'adversaire dans une même session groupée.
     */
    public function setOpponentType(string $type): void
    {
        if ($this->history !== [] || $this->localFirstChoice !== null) {
            return;
        }

        $opponent = RpsOpponentType::tryFrom($type);

        if ($opponent === null) {
            return;
        }

        $this->opponentType = $opponent->value;
    }

    public function play(string $choice, PlayRpsRoundAction $action): void
    {
        $rpsChoice = RpsChoice::tryFrom($choice);

        if ($rpsChoice === null) {
            return;
        }

        if ($this->opponentType === RpsOpponentType::LOCAL->value) {
            $this->playLocal($rpsChoice);

            return;
        }

        $this->applyResult($action->execute($rpsChoice));
    }

    /**
     * Mode local : le premier appel mémorise le choix du joueur 1 (cache,
     * rien n'est révélé côté écran) et déclenche l'écran "passez l'appareil".
     * Le second appel consomme ce choix et calcule le résultat.
     */
    private function playLocal(RpsChoice $choice): void
    {
        if ($this->localFirstChoice === null) {
            $this->localFirstChoice = $choice->value;

            $this->dispatch('rps-local-first-played');

            return;
        }

        $firstChoice = RpsChoice::from($this->localFirstChoice);
        $this->localFirstChoice = null;

        $this->applyResult(new RpsResult($firstChoice, $choice));
    }

    private function applyResult(RpsResult $result): void
    {
        $opponent = RpsOpponentType::from($this->opponentType);

        $this->playerChoice = $result->player->value;
        $this->opponentChoice = $result->opponent->value;
        $this->outcome = $result->outcome->value;

        $this->pendingRound = [
            'a' => $result->player->value,
            'b' => $result->opponent->value,
            'outcome' => $result->outcome->value,
        ];

        $this->dispatch('rps-played');
    }

    /**
     * Confirme la manche en attente : appelé côté client une fois l'animation
     * de révélation terminée, pour que l'historique n'apparaisse pas avant.
     */
    public function confirmRound(): void
    {
        if ($this->pendingRound === null) {
            return;
        }

        $this->history[] = $this->pendingRound;

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }

        $this->persistSession();

        $this->pendingRound = null;
    }

    private function persistSession(): void
    {
        $opponent = RpsOpponentType::from($this->opponentType);

        app(HistoryStore::class)->pushSession(GameModeType::ROCK_PAPER_SCISSORS, $this->sessionId, [
            'opponent' => $opponent->value,
            'a_label' => $opponent->playerALabel(),
            'b_label' => $opponent->playerBLabel(),
            'score' => [
                'a' => $this->countOutcome('win'),
                'b' => $this->countOutcome('lose'),
                'draw' => $this->countOutcome('draw'),
            ],
            'rounds' => $this->history,
            'rounds_count' => count($this->history),
        ]);
    }

    public function resetHistory(): void
    {
        $this->history = [];
        $this->playerChoice = null;
        $this->opponentChoice = null;
        $this->outcome = null;
        $this->pendingRound = null;
        $this->localFirstChoice = null;
        $this->sessionId = (string) Str::uuid();

        app(HistoryStore::class)->clear(GameModeType::ROCK_PAPER_SCISSORS);
    }

    private function countOutcome(string $outcome): int
    {
        return count(array_filter($this->history, fn (array $e) => $e['outcome'] === $outcome));
    }

    public function winCount(): int
    {
        return $this->countOutcome('win');
    }

    public function loseCount(): int
    {
        return $this->countOutcome('lose');
    }

    public function drawCount(): int
    {
        return $this->countOutcome('draw');
    }

    public function opponent(): RpsOpponentType
    {
        return RpsOpponentType::from($this->opponentType);
    }
    public function restart(): void
    {
        // Réinitialise l'état de la manche / partie
        $this->reset(['playerChoice', 'opponentChoice', 'localFirstChoice', 'outcome']);
    }
    
    public function render()
    {
        $mode = GameModeType::ROCK_PAPER_SCISSORS->toDto();

        return view('livewire.rock-paper-scissors.rps-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
