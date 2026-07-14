<?php

namespace App\Livewire\Draw;


use Livewire\Attributes\Computed;
use Livewire\Component;


use App\Application\Draw\Actions\RunDrawAction;

use App\Application\Draw\Support\WheelSegmentBuilder;


use App\Livewire\Draw\Concerns\ManagesParticipants;
use App\Livewire\Draw\Concerns\HandlesDraw;



class WheelPage extends Component
{

    use ManagesParticipants;
    use HandlesDraw;



    private const MAX_LABELS_ON_WHEEL = 10;



    public ?string $result = null;



    protected function afterParticipantsChanged(): void
    {
        $this->result = null;
    }




    public function draw(
        RunDrawAction $action
    ): void {


        $this->error = null;



        if (count($this->participants) < 2) {

            $this->error =
                'Ajoutez au moins deux participants.';

            return;
        }




        $result =
            $this->executeDraw($action);




        $winner =
            $result->winner;




        $index =
            array_search(
                $winner->name,
                $this->participants,
                true
            );



        if ($index === false) {

            $this->error =
                'Gagnant introuvable.';

            return;
        }




        $this->result =
            $winner->name;




        $this->dispatch(
            'wheel-spin',
            rotation: WheelSegmentBuilder::rotationFor(
                $index,
                count($this->participants)
            )
        );
    }





    #[Computed]
    public function segments(): array
    {
        return WheelSegmentBuilder::build(
            $this->participants
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
            'livewire.draw.wheel-page'
        )
            ->layout('layouts.app');
    }
}
