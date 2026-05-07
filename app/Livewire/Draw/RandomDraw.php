<?php

namespace App\Livewire\Draw;

use Livewire\Component;
use App\Domain\Draw\Enums\DrawType;
use App\Application\Draw\DTOs\DrawData;
use App\Application\Draw\Actions\RunDrawAction;

class RandomDraw extends Component
{
    public array $participants = [];

    public string $participant = '';

    public ?string $result = null;

    public function addParticipant(): void
    {
        $name = trim($this->participant);

        if ($name === '') {
            return;
        }

        $this->participants[] = $name;

        $this->participant = '';
    }

    public function removeParticipant(int $index): void
    {
        unset($this->participants[$index]);

        $this->participants = array_values($this->participants);
    }

    public function draw(RunDrawAction $action): void
    {
        $this->result = $action->execute(
            new DrawData(
                participants: $this->participants,
                type: DrawType::RANDOM,
            )
        );
    }

 public function render()
{
    return view('livewire.draw.random-draw')
        ->layout('layouts.app');
}
}
