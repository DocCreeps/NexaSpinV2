<?php

use App\Livewire\Draw\WeightedWheelPage;
use Livewire\Livewire;

it('adds a participant with a default weight of one', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->assertSet('participants', ['Alice'])
        ->assertSet('participantWeights', [1])
        ->assertSet('participant', '') // le champ de saisie est vidé après ajout
        ->assertSet('error', null);
});

it('silently ignores adding a blank participant name', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', '   ')
        ->call('addParticipant')
        ->assertSet('participants', [])
        ->assertSet('error', null);
});

it('refuses a participant name longer than 50 characters', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', str_repeat('a', 51))
        ->call('addParticipant')
        ->assertSet('participants', [])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('refuses to add the same participant name twice, case-insensitively', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->set('participant', 'alice')
        ->call('addParticipant')
        ->assertSet('participants', ['Alice'])
        ->assertSet('error', 'Ce participant existe déjà.');
});

it('refuses to add more than 100 participants', function () {
    $component = Livewire::test(WeightedWheelPage::class)
        ->set('participants', array_map(fn (int $i) => "P{$i}", range(1, 100)))
        ->set('participantWeights', array_fill(0, 100, 1));

    $component
        ->set('participant', 'OneTooMany')
        ->call('addParticipant')
        ->assertSet('participants', fn (array $participants) => count($participants) === 100)
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('renames a participant in place', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipant', 0, 'Alicia')
        ->assertSet('participants', ['Alicia'])
        ->assertSet('error', null);
});

it('refuses to rename a participant to a blank name', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipant', 0, '   ')
        ->assertSet('participants', ['Alice'])
        ->assertSet('error', fn (?string $error) => $error !== null);
});

it('refuses to rename a participant to a name already used by another participant', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->set('participant', 'Bob')
        ->call('addParticipant')
        ->call('updateParticipant', 1, 'alice')
        ->assertSet('participants', ['Alice', 'Bob'])
        ->assertSet('error', 'Ce participant existe déjà.');
});

it('allows renaming a participant to the same name with a different case', function () {
    // Cas limite explicitement géré : renommer "Alice" en "ALICE" ne doit pas
    // être vu comme un doublon avec elle-même.
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipant', 0, 'ALICE')
        ->assertSet('participants', ['ALICE'])
        ->assertSet('error', null);
});

it('updates a participant weight within the allowed range', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipantWeight', 0, 42)
        ->assertSet('participantWeights', [42])
        ->assertSet('error', null);
});

it('refuses a weight outside the allowed 1-100 range', function (int $weight) {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipantWeight', 0, $weight)
        ->assertSet('participantWeights', [1]) // poids par défaut inchangé
        ->assertSet('error', fn (?string $error) => $error !== null);
})->with([0, -5, 101, 1000]);

it('accepts the boundary weights of 1 and 100', function (int $weight) {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->call('updateParticipantWeight', 0, $weight)
        ->assertSet('participantWeights', [$weight])
        ->assertSet('error', null);
})->with([1, 100]);

it('removes a participant and reindexes the remaining ones', function () {
    Livewire::test(WeightedWheelPage::class)
        ->set('participant', 'Alice')
        ->call('addParticipant')
        ->set('participant', 'Bob')
        ->call('addParticipant')
        ->set('participant', 'Charlie')
        ->call('addParticipant')
        ->call('removeParticipant', 1) // retire Bob
        ->assertSet('participants', [0 => 'Alice', 1 => 'Charlie'])
        ->assertSet('participantWeights', [0 => 1, 1 => 1]);
});
