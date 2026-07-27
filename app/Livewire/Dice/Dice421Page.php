<?php

namespace App\Livewire\Dice;

use App\Application\Dice\Actions\RollDiceAction;
use App\Application\Home\Enums\GameModeType;
use App\Domain\Dice\Contracts\DiceGameStrategy;
use App\Domain\Dice\Strategies\FourTwoOneStrategy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Composant du 421 : gère l'état d'une partie (dés, dés gardés, nombre de
 * lancers) et délègue toute la logique de jeu à RollDiceAction / la
 * DiceGameStrategy liée (FourTwoOneStrategy).
 *
 * IMPORTANT : la stratégie n'est PAS injectée en paramètre de roll(), ni via
 * un binding contextuel who(Dice421Page::class) dans AppServiceProvider.
 * Ces deux approches échouent silencieusement avec Livewire : le conteneur
 * résout les paramètres de méthode (roll(), boot()...) via Container::call(),
 * qui ne passe jamais par build() pour Dice421Page — le contextual binding
 * indexé sur Dice421Page::class ne matche donc jamais, et Laravel tente
 * d'instancier l'interface DiceGameStrategy directement (échec :
 * "Target [...DiceGameStrategy] is not instantiable").
 *
 * On résout donc explicitement la stratégie concrète dans boot() (rappelé
 * par Livewire à chaque requête, y compris les appels d'action ultérieurs),
 * et on construit RollDiceAction à la main dans roll().
 */
class Dice421Page extends Component
{
    private const DICE_COUNT = 3;
    private const MAX_HISTORY = 5000;

    private DiceGameStrategy $strategy;

    public function boot(): void
    {
        $this->strategy = app(FourTwoOneStrategy::class);
    }

    /**
     * #[Locked] : $dice ne doit jamais être modifiable directement depuis un
     * payload Livewire forgé côté client. Sans ce verrou, un attaquant
     * pourrait poser $dice = [4, 2, 1] puis appeler roll() avec les 3 dés
     * gardés, ce qui ferait évaluer une victoire sur des valeurs jamais
     * réellement tirées côté serveur. Seule roll() (serveur) peut la faire
     * évoluer, avec les valeurs renvoyées par RollDiceAction.
     */
    #[Locked]
    public array $dice = [1, 1, 1];

    /** @var array<bool> Dés à conserver avant la prochaine relance */
    public array $kept = [false, false, false];

    /**
     * #[Locked] pour la même raison que $bet sur CoinFlipPage : $throwCount
     * pilote la limite de lancers appliquée par la stratégie. Un payload
     * forgé qui le remettrait à 0 permettrait de relancer indéfiniment.
     */
    #[Locked]
    public int $throwCount = 0;

    public bool $isOver = false;
    public bool $isWon = false;
    public ?string $combinationLabel = null;
    public ?string $error = null;

    /** @var array<int, array{dice: array<int>, throws: int, won: bool, combination: ?string}> */
    public array $history = [];

    public function mount(): void
    {
        $this->resetGame();
    }

    public function roll(): void
    {
        $this->error = null;

        if ($this->isOver) {
            // Garde-fou défensif : si le client appelle roll() malgré tout
            // (double clic, état pas encore synchronisé, payload forgé...),
            // on ne relance rien côté serveur, mais on renvoie quand même
            // l'état complet via l'event. Sans ce dispatch, 'dice-rolled'
            // n'arrive jamais côté Alpine et isRolling reste bloqué à true
            // indéfiniment : c'est ce qui causait le lancer "interminable".
            $this->error = 'La partie est déjà terminée.';

            $this->dispatch(
                'dice-rolled',
                dice: $this->dice,
                throwCount: $this->throwCount,
                isOver: $this->isOver,
                isWon: $this->isWon,
                combinationLabel: $this->combinationLabel,
            );

            return;
        }

        $action = new RollDiceAction($this->strategy);
        $result = $action->execute($this->dice, $this->kept, $this->throwCount);

        $this->dice = $result->roll->values;
        $this->throwCount = $result->throwCount;
        $this->isWon = $result->isWon;
        $this->isOver = $result->isOver;
        $this->combinationLabel = $result->combination->label();

        if ($this->isOver) {
            $this->recordHistory();
        }

        // Fix : on transmet tout l'état nécessaire à finalizeRoll() côté
        // Alpine (throwCount, isOver, isWon, combinationLabel), pas
        // seulement `dice` comme avant.
        $this->dispatch(
            'dice-rolled',
            dice: $this->dice,
            throwCount: $this->throwCount,
            isOver: $this->isOver,
            isWon: $this->isWon,
            combinationLabel: $this->combinationLabel,
        );
    }

    public function toggleKeep(int $index): void
    {
        if ($this->isOver || ! array_key_exists($index, $this->kept)) {
            return;
        }

        $this->kept[$index] = ! $this->kept[$index];
    }

    public function resetGame(): void
    {
        $this->dice = array_fill(0, self::DICE_COUNT, 1);
        $this->kept = array_fill(0, self::DICE_COUNT, false);
        $this->throwCount = 0;
        $this->isOver = false;
        $this->isWon = false;
        $this->combinationLabel = null;
        $this->error = null;

        $this->dispatch('dice-reset');
    }

    private function recordHistory(): void
    {
        $this->history[] = [
            'dice' => $this->dice,
            'throws' => $this->throwCount,
            'won' => $this->isWon,
            // Ajout : on garde le libellé de la combinaison finale pour
            // pouvoir l'afficher dans la colonne dédiée de l'historique.
            'combination' => $this->combinationLabel,
        ];

        if (count($this->history) > self::MAX_HISTORY) {
            $this->history = array_slice($this->history, -self::MAX_HISTORY);
        }
    }

    public function maxThrows(): int
    {
        return $this->strategy->maxThrows();
    }

    public function winCount(): int
    {
        return count(array_filter($this->history, fn(array $entry) => $entry['won']));
    }

    public function render()
    {
        $mode = GameModeType::DICE_421->toDto();

        return view('livewire.dice.dice421-page')
            ->layout('layouts.app', [
                'title' => $mode->metaTitle,
                'metaDescription' => $mode->metaDescription,
            ]);
    }
}
