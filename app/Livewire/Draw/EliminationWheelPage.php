<?php

namespace App\Livewire\Draw;

use Livewire\Attributes\Computed;
use Livewire\Component;

use App\Application\Draw\Actions\RunDrawAction;
use App\Application\Draw\Support\WheelSegmentBuilder;

use App\Livewire\Draw\Concerns\ManagesParticipants;
use App\Livewire\Draw\Concerns\HandlesDraw;


class EliminationWheelPage extends Component
{
    use ManagesParticipants;
    use HandlesDraw;


    private const MAX_LABELS_ON_WHEEL = 10;


    public ?string $error = null;

    public array $initialParticipants = [];

    public array $eliminated = [];

    public array $colors = [];

    public ?string $pendingElimination = null;

    public ?string $lastEliminated = null;

    public ?string $winner = null;

    public bool $processing = false;

    public int $wheelRotation = 0;

    public bool $autoMode = false;



    /**
     * Bloque les modifications dès que le jeu commence.
     */
    protected function participantsAreLocked(): bool
    {
        return $this->started();
    }



    /**
     * Mode automatique.
     */
    public function updatedAutoMode($value): void
    {
        if (
            $value &&
            !$this->processing &&
            !$this->winner
        ) {
            $this->handleAction();
        }
    }



    /**
     * Action principale.
     */
    public function handleAction(
        ?RunDrawAction $action = null
    ): void {
        $action = $action ?? app(RunDrawAction::class);

        if (!$this->started()) {

            $this->start();

            if ($this->error) {
                return;
            }
        }


        $this->eliminateNext($action);
    }



    /**
     * Initialise le jeu.
     */
    public function start(): void
    {
        $this->error = null;


        if (count($this->participants) < 2) {

            $this->error =
                "Ajoutez au moins deux participants avant de lancer l'élimination.";

            return;
        }



        $this->initialParticipants =
            $this->participants;


        $this->colors =
            WheelSegmentBuilder::assignColors(
                $this->participants
            );


        $this->eliminated = [];

        $this->winner = null;

        $this->pendingElimination = null;

        $this->lastEliminated = null;

        $this->processing = false;

        $this->wheelRotation = 0;



        unset($this->segments);



        $this->dispatch(
            'start-ready'
        );
    }





    /**
     * Sélectionne le participant à éliminer.
     */
    public function eliminateNext(
        ?RunDrawAction $action = null
    ): void {
        $action = $action ?? app(RunDrawAction::class);


        if (
            $this->processing ||
            $this->winner ||
            count($this->participants) <= 1
        ) {
            return;
        }



        $this->processing = true;



        $result =
            $this->executeDraw($action);



        if (!$result) {

            $this->processing = false;

            return;
        }



        $this->pendingElimination =
            $result->winner->name;



        $targetIndex =
            array_search(
                $this->pendingElimination,
                $this->participants,
                true
            );



        if ($targetIndex === false) {

            $this->processing = false;

            return;
        }



        $totalParticipants =
            count($this->participants);



        $slice =
            360 / $totalParticipants;



        /**
         * Position du segment gagnant
         */
        $targetAngle =
            360 -
            (
                ($targetIndex * $slice)
                +
                ($slice / 2)
            );



        $targetAngle =
            fmod(
                $targetAngle,
                360
            );



        if ($targetAngle < 0) {
            $targetAngle += 360;
        }



        /**
         * Minimum 5 tours.
         */
        $minRotation =
            $this->wheelRotation + 1800;



        $current =
            fmod(
                $minRotation,
                360
            );



        $nextRotation =
            $targetAngle >= $current

            ? $minRotation + ($targetAngle - $current)

            : $minRotation
            + (360 - $current)
            + $targetAngle;



        $delta =
            $nextRotation
            - $this->wheelRotation;



        $this->wheelRotation =
            (int) $nextRotation;



        $this->dispatch(
            'wheel-spin',
            rotation: $delta
        );
    }





    /**
     * Confirmation après animation.
     */
    public function confirmElimination(): void
    {

        if (
            !$this->processing ||
            !$this->pendingElimination
        ) {

            $this->processing = false;

            return;
        }



        $player =
            $this->pendingElimination;



        if (
            !in_array(
                $player,
                $this->eliminated,
                true
            )
        ) {


            $this->eliminated[] =
                $player;


            $this->lastEliminated =
                $player;



            $this->participants =
                array_values(
                    array_diff(
                        $this->participants,
                        [$player]
                    )
                );


            $this->afterParticipantsChanged();
        }



        $this->pendingElimination = null;



        if (
            count($this->participants) === 1
        ) {

            $this->winner =
                $this->participants[0];


            $this->autoMode = false;
        }



        $this->processing = false;



        $this->dispatch(
            'elimination-confirmed'
        );
    }





    /**
     * Recommencer.
     */
    public function restart(): void
    {
        $this->participants =
            $this->initialParticipants;


        $this->initialParticipants = [];

        $this->eliminated = [];

        $this->colors = [];

        $this->pendingElimination = null;

        $this->lastEliminated = null;

        $this->winner = null;

        $this->processing = false;

        $this->error = null;

        $this->wheelRotation = 0;

        $this->autoMode = false;



        unset($this->segments);



        $this->dispatch(
            'game-restarted'
        );
    }





    public function started(): bool
    {
        return $this->colors !== [];
    }





    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build(
            $this->participants,
            $this->colors
        );
    }





    public function showLabelsOnWheel(): bool
    {
        return count($this->participants)
            <= self::MAX_LABELS_ON_WHEEL;
    }





    public function render()
    {
        return view(
            'livewire.draw.elimination-wheel-page'
        )
            ->layout('layouts.app');
    }
}
