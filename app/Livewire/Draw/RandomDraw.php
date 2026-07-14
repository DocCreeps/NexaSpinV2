<?php

namespace App\Livewire\Draw;

use Livewire\Component;

use App\Application\Draw\Actions\RunDrawAction;

use App\Application\Draw\DTOs\DrawData;

use App\Domain\Draw\Enums\DrawType;
use App\Domain\Draw\Enums\DrawDisplay;

use App\Livewire\Draw\Concerns\ManagesParticipants;
use App\Livewire\Draw\Concerns\HandlesDraw;


class RandomDraw extends Component
{
    use ManagesParticipants;
    use HandlesDraw;


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



        $this->result =
            $result->winner->name;



        $this->dispatch(
            'winner-selected',
            winner: $this->result
        );
    }



    public function render()
    {
        return view(
            'livewire.draw.random-draw'
        )
            ->layout('layouts.app');
    }
}
