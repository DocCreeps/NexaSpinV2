<?php

use App\Livewire\Draw\WeightedWheelPage;
use Livewire\Livewire;

it('requires at least two participants to draw', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participants', ['Solo'])
        ->call('draw')
        ->assertSet('result', null)
        ->assertSet('error', fn(?string $error) => $error !== null);
});

it('draws a winner among the participants and dispatches a wheel-spin event', function () {
    $names = ['John', 'Jane', 'Bob'];

    Livewire::test(WeightedWheelPage::class)
        ->set('participants', $names)
        ->call('draw')
        ->assertSet('result', fn(?string $result) => in_array($result, $names, true))
        ->assertSet('error', null)
        ->assertDispatched('wheel-spin');
});

it('defaults new participants to a weight of one', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'John')
        ->call('addParticipant')
        ->assertSet('participantWeights', [1]);
});

it('updates a participant weight', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->set('participantWeights', [1, 1])
        ->call('updateParticipantWeight', 1, 10)
        ->assertSet('participantWeights', [1, 10]);
});

it('rejects a weight outside the allowed range', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participants', ['John', 'Jane'])
        ->set('participantWeights', [1, 1])
        ->call('updateParticipantWeight', 0, 999)
        ->assertSet('participantWeights', [1, 1])
        ->assertSet('error', fn(?string $error) => $error !== null);
});

it('keeps weights in sync when a participant is removed', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participants', ['John', 'Jane', 'Bob'])
        ->set('participantWeights', [1, 5, 10])
        ->call('removeParticipant', 1)
        ->assertSet('participants', ['John', 'Bob'])
        ->assertSet('participantWeights', [1, 10]);
});
