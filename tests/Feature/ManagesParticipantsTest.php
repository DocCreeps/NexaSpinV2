<?php

use App\Livewire\Draw\EliminationWheelPage;
use App\Livewire\Draw\WheelPage;
use Livewire\Livewire;

it('adds a trimmed participant', function () {
    Livewire::test(WheelPage::class)
        ->set('participant', '  John  ')
        ->call('addParticipant')
        ->assertSet('participants', ['John'])
        ->assertSet('participant', '');
});

it('rejects a duplicate participant, case-insensitively', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['John'])
        ->set('participant', 'JOHN')
        ->call('addParticipant')
        ->assertSet('participants', ['John'])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('rejects a name longer than 50 characters', function () {
    Livewire::test(WheelPage::class)
        ->set('participant', str_repeat('a', 51))
        ->call('addParticipant')
        ->assertSet('participants', [])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('rejects more than 100 participants', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', array_map(fn (int $i) => "Participant {$i}", range(1, 100)))
        ->set('participant', 'One too many')
        ->call('addParticipant')
        ->assertSet('participants', fn (array $participants) => count($participants) === 100)
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('removes a participant by index', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->call('removeParticipant', 0)
        ->assertSet('participants', ['Jane']);
});

it('updates a participant name', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->call('updateParticipant', 0, 'Johnny')
        ->assertSet('participants', ['Johnny', 'Jane']);
});

it('rejects an empty name when updating a participant', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->call('updateParticipant', 0, '   ')
        ->assertSet('participants', ['John', 'Jane'])
        ->assertSet('error', fn(?string $error) => $error !== null);
});

it('rejects a duplicate name when updating a participant, case-insensitively', function () {
    Livewire::test(WheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->call('updateParticipant', 0, 'JANE')
        ->assertSet('participants', ['John', 'Jane'])
        ->assertSet('error', fn(?string $error) => $error !== null);
});

it('locks participant editing once the elimination wheel has started', function () {
    $component = Livewire::test(EliminationWheelPage::class)
        ->set('participants', ['John', 'Jane', 'Bob'])
        ->call('start');

    $component
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('removeParticipant', 0);

    expect($component->get('participants'))->toHaveCount(3); // rien n'a changé : verrouillé
});
